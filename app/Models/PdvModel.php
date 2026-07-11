<?php
namespace App\Models;
use App\Config\Database;

class PdvModel {
    private \PDO $db;
    public function __construct(?\PDO $db = null)
    {
        $this->db = $db ?: Database::getInstance();
        \App\Core\RequiredSchema::assert($this->db, 'PDV', [
            'pdv_caixas' => ['id', 'data_caixa', 'status'],
            'pdv_vendas' => ['id', 'caixa_id', 'status'],
            'pdv_itens' => ['id', 'venda_id'],
        ], [
            'pdv_caixas' => ['uk_pdv_caixas_data'],
            'pdv_vendas' => ['idx_pdv_caixa'],
        ]);
    }

    private function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo'));
    }

    public function horarioCaixa(?\DateTimeImmutable $now = null): array
    {
        $now = $now ?: $this->now();
        $day = (int) $now->format('N');
        if ($day >= 1 && $day <= 5) {
            return ['abre' => '09:00', 'fecha' => '18:00', 'dia_util' => true];
        }
        if ($day === 6) {
            return ['abre' => '09:00', 'fecha' => '14:00', 'dia_util' => true];
        }
        return ['abre' => null, 'fecha' => null, 'dia_util' => false];
    }

    public function estaNoHorario(?\DateTimeImmutable $now = null): bool
    {
        $now = $now ?: $this->now();
        $schedule = $this->horarioCaixa($now);
        if (!$schedule['dia_util']) {
            return false;
        }

        $time = $now->format('H:i');
        return $time >= $schedule['abre'] && $time < $schedule['fecha'];
    }

    public function sincronizarCaixaAutomatico(?int $usuarioId = null): ?array
    {
        $now = $this->now();
        $today = $now->format('Y-m-d');
        $caixa = $this->caixaDoDia($today);

        if ($this->estaNoHorario($now)) {
            if (!$caixa) {
                return $this->abrirCaixa($usuarioId, 'automatica');
            }
            if (($caixa['status'] ?? '') === 'fechado' && ($caixa['fechamento_tipo'] ?? '') !== 'manual') {
                return $this->reabrirCaixa((int) $caixa['id'], $usuarioId, 'automatica');
            }
            return $caixa;
        }

        if ($caixa && ($caixa['status'] ?? '') === 'aberto' && ($caixa['abertura_tipo'] ?? '') !== 'manual') {
            return $this->fecharCaixa((int) $caixa['id'], $usuarioId, 'automatico');
        }

        return $caixa ?: null;
    }

    public function caixaDoDia(?string $date = null): ?array
    {
        $date = $date ?: $this->now()->format('Y-m-d');
        $stmt = $this->db->prepare("SELECT c.*, ua.nome AS aberto_por_nome, uf.nome AS fechado_por_nome
            FROM pdv_caixas c
            LEFT JOIN usuarios ua ON ua.id = c.aberto_por
            LEFT JOIN usuarios uf ON uf.id = c.fechado_por
            WHERE c.data_caixa = :data
            LIMIT 1");
        $stmt->execute([':data' => $date]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function caixaAberto(): ?array
    {
        $caixa = $this->caixaDoDia();
        return $caixa && ($caixa['status'] ?? '') === 'aberto' ? $caixa : null;
    }

    public function abrirCaixa(?int $usuarioId, string $tipo = 'manual', float $valorInicial = 0.0, string $observacoes = ''): array
    {
        $now = $this->now();
        $today = $now->format('Y-m-d');
        $caixa = $this->caixaDoDia($today);
        if ($caixa) {
            return $this->reabrirCaixa((int) $caixa['id'], $usuarioId, $tipo, $valorInicial, $observacoes);
        }

        $stmt = $this->db->prepare("INSERT INTO pdv_caixas
            (data_caixa, status, abertura_tipo, aberto_por, aberto_em, valor_inicial, observacoes)
            VALUES (:data_caixa, 'aberto', :tipo, :usuario_id, :aberto_em, :valor_inicial, :observacoes)");
        $stmt->execute([
            ':data_caixa' => $today,
            ':tipo' => $tipo === 'manual' ? 'manual' : 'automatica',
            ':usuario_id' => $usuarioId,
            ':aberto_em' => $now->format('Y-m-d H:i:s'),
            ':valor_inicial' => $valorInicial,
            ':observacoes' => $observacoes,
        ]);
        return $this->caixaDoDia($today) ?: [];
    }

    public function reabrirCaixa(int $id, ?int $usuarioId, string $tipo = 'manual', ?float $valorInicial = null, string $observacoes = ''): array
    {
        $now = $this->now();
        $sets = "status='aberto', abertura_tipo=:tipo, aberto_por=:usuario_id, aberto_em=:aberto_em, fechamento_tipo=NULL, fechado_por=NULL, fechado_em=NULL";
        $params = [
            ':tipo' => $tipo === 'manual' ? 'manual' : 'automatica',
            ':usuario_id' => $usuarioId,
            ':aberto_em' => $now->format('Y-m-d H:i:s'),
            ':id' => $id,
        ];
        if ($valorInicial !== null) {
            $sets .= ", valor_inicial=:valor_inicial";
            $params[':valor_inicial'] = $valorInicial;
        }
        if ($observacoes !== '') {
            $sets .= ", observacoes=:observacoes";
            $params[':observacoes'] = $observacoes;
        }
        $stmt = $this->db->prepare("UPDATE pdv_caixas SET {$sets} WHERE id=:id");
        $stmt->execute($params);
        return $this->caixaDoDia() ?: [];
    }

    public function fecharCaixa(int $id, ?int $usuarioId, string $tipo = 'manual', ?float $valorInformado = null, string $observacoes = ''): array
    {
        $now = $this->now();
        $sets = "status='fechado', fechamento_tipo=:tipo, fechado_por=:usuario_id, fechado_em=:fechado_em";
        $params = [
            ':tipo' => $tipo === 'manual' ? 'manual' : 'automatico',
            ':usuario_id' => $usuarioId,
            ':fechado_em' => $now->format('Y-m-d H:i:s'),
            ':id' => $id,
        ];
        if ($valorInformado !== null) {
            $sets .= ", valor_informado=:valor_informado";
            $params[':valor_informado'] = $valorInformado;
        }
        if ($observacoes !== '') {
            $sets .= ", observacoes=:observacoes";
            $params[':observacoes'] = $observacoes;
        }
        $this->db->prepare("UPDATE pdv_caixas SET {$sets} WHERE id=:id")->execute($params);

        try {
            $this->gerarESalvarRelatorioCaixa($id);
        } catch (\Throwable $e) {
            app_log('Erro ao gerar relatorio automatico de fechamento de caixa', ['erro' => $e->getMessage()]);
        }

        return $this->caixaDoDia() ?: [];
    }

    public function resumoCaixa(?int $caixaId): array
    {
        if (!$caixaId) {
            return ['total' => 0.0, 'qtd' => 0, 'formas' => []];
        }
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(total),0) AS total, COUNT(*) AS qtd
            FROM pdv_vendas
            WHERE caixa_id = :caixa_id AND status = 'finalizada'");
        $stmt->execute([':caixa_id' => $caixaId]);
        $summary = $stmt->fetch() ?: ['total' => 0, 'qtd' => 0];

        $stmt = $this->db->prepare("SELECT COALESCE(NULLIF(forma_pagamento, ''), 'Nao informado') AS forma, COALESCE(SUM(total),0) AS total, COUNT(*) AS qtd
            FROM pdv_vendas
            WHERE caixa_id = :caixa_id AND status = 'finalizada'
            GROUP BY COALESCE(NULLIF(forma_pagamento, ''), 'Nao informado')
            ORDER BY total DESC");
        $stmt->execute([':caixa_id' => $caixaId]);

        return [
            'total' => (float) ($summary['total'] ?? 0),
            'qtd' => (int) ($summary['qtd'] ?? 0),
            'formas' => $stmt->fetchAll(),
        ];
    }

    public function getVendas(int $limit = 50) {
        $sql = "SELECT v.*, c.nome as cliente_nome, u.nome as operador_nome FROM pdv_vendas v LEFT JOIN clientes c ON v.cliente_id=c.id LEFT JOIN usuarios u ON v.usuario_id=u.id ORDER BY v.created_at DESC LIMIT $limit";
        return $this->db->query($sql)->fetchAll();
    }

    public function findVenda(int $id) {
        $stmt = $this->db->prepare("
            SELECT
                v.*,
                c.nome as cliente_nome,
                u.nome as operador_nome,
                os.numero_os as numero_os_vinculada
            FROM pdv_vendas v
            LEFT JOIN clientes c ON v.cliente_id=c.id
            LEFT JOIN usuarios u ON v.usuario_id=u.id
            LEFT JOIN ordens_servico os ON v.os_id=os.id
            WHERE v.id=:id
        ");
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch();
    }

    public function getItensVenda(int $venda_id) {
        $stmt = $this->db->prepare("SELECT pi.*, e.nome as produto_nome FROM pdv_itens pi LEFT JOIN estoque e ON pi.produto_id=e.id WHERE pi.venda_id=:id");
        $stmt->execute([':id'=>$venda_id]);
        return $stmt->fetchAll();
    }

    public function criarVenda(array $data) {
        $numero = 'PDV-' . date('Ymd') . '-' . str_pad(rand(1,999), 3, '0', STR_PAD_LEFT);
        $sql = "INSERT INTO pdv_vendas (numero_venda, caixa_id, cliente_id, os_id, usuario_id, forma_pagamento, desconto, observacoes) VALUES (:num, :caixa_id, :cliente_id, :os_id, :usuario_id, :forma_pagamento, :desconto, :obs)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':num'=>$numero, ':caixa_id'=>$data['caixa_id']??null, ':cliente_id'=>$data['cliente_id']??null, ':os_id'=>$data['os_id']??null, ':usuario_id'=>$data['usuario_id'], ':forma_pagamento'=>$data['forma_pagamento'], ':desconto'=>$data['desconto']??0, ':obs'=>$data['observacoes']??'']);
        return $this->db->lastInsertId();
    }

    public function addItem(int $venda_id, ?int $produto_id, string $descricao, int $qtd, float $preco) {
        $stmt = $this->db->prepare("INSERT INTO pdv_itens (venda_id, produto_id, descricao, quantidade, preco_unitario) VALUES (:vid, :pid, :desc, :qtd, :preco)");
        $stmt->execute([':vid'=>$venda_id, ':pid'=>$produto_id, ':desc'=>$descricao, ':qtd'=>$qtd, ':preco'=>$preco]);
    }

    public function finalizarVenda(int $venda_id, float $taxaPercentual = 0.0) {
        $itens = $this->getItensVenda($venda_id);
        $subtotal = array_sum(array_column($itens, 'total'));
        $venda = $this->findVenda($venda_id);
        $total = $subtotal - ($venda['desconto'] ?? 0);
        $taxaPercentual = max(0, $taxaPercentual);
        $taxaValor = round($total * ($taxaPercentual / 100), 2);
        $totalLiquido = max(0, $total - $taxaValor);
        $this->db->prepare("UPDATE pdv_vendas
                SET subtotal=:sub,
                    total=:tot,
                    taxa_cartao_percentual=:taxa_percentual,
                    taxa_cartao_valor=:taxa_valor,
                    total_liquido=:total_liquido,
                    status='finalizada'
                WHERE id=:id")
            ->execute([
                ':sub'=>$subtotal,
                ':tot'=>$total,
                ':taxa_percentual'=>$taxaPercentual,
                ':taxa_valor'=>$taxaValor,
                ':total_liquido'=>$totalLiquido,
                ':id'=>$venda_id
            ]);
    }

    public function prepararVendaPoint(int $venda_id, float $taxaPercentual = 0.0): void {
        $itens = $this->getItensVenda($venda_id);
        $subtotal = array_sum(array_column($itens, 'total'));
        $venda = $this->findVenda($venda_id);
        $total = max(0, $subtotal - ($venda['desconto'] ?? 0));
        $taxaPercentual = max(0, $taxaPercentual);
        $taxaValor = round($total * ($taxaPercentual / 100), 2);
        $totalLiquido = max(0, $total - $taxaValor);
        $this->db->prepare("UPDATE pdv_vendas
                SET subtotal=:sub,
                    total=:tot,
                    taxa_cartao_percentual=:taxa_percentual,
                    taxa_cartao_valor=:taxa_valor,
                    total_liquido=:total_liquido,
                    status='aguardando_point'
                WHERE id=:id")
            ->execute([
                ':sub'=>$subtotal,
                ':tot'=>$total,
                ':taxa_percentual'=>$taxaPercentual,
                ':taxa_valor'=>$taxaValor,
                ':total_liquido'=>$totalLiquido,
                ':id'=>$venda_id
            ]);
    }

    public function marcarFinalizada(int $venda_id, float $taxaPercentual = 0.0): void {
        $this->finalizarVenda($venda_id, $taxaPercentual);
    }

    public function totalHoje() {
        return $this->db->query("SELECT COALESCE(SUM(total),0) FROM pdv_vendas WHERE DATE(created_at)=CURDATE() AND status='finalizada'")->fetchColumn();
    }

    public function getVendasPorCaixa(int $caixaId): array
    {
        $stmt = $this->db->prepare("
            SELECT v.*, c.nome as cliente_nome, u.nome as operador_nome 
            FROM pdv_vendas v 
            LEFT JOIN clientes c ON v.cliente_id=c.id 
            LEFT JOIN usuarios u ON v.usuario_id=u.id 
            WHERE v.caixa_id = :caixa_id AND v.status = 'finalizada'
            ORDER BY v.created_at ASC
        ");
        $stmt->execute([':caixa_id' => $caixaId]);
        return $stmt->fetchAll() ?: [];
    }

    public function gerarESalvarRelatorioCaixa(int $caixaId): void
    {
        $stmt = $this->db->prepare("SELECT c.*, ua.nome AS aberto_por_nome, uf.nome AS fechado_por_nome
            FROM pdv_caixas c
            LEFT JOIN usuarios ua ON ua.id = c.aberto_por
            LEFT JOIN usuarios uf ON uf.id = c.fechado_por
            WHERE c.id = :id");
        $stmt->execute([':id' => $caixaId]);
        $caixa = $stmt->fetch();
        if (!$caixa) {
            return;
        }

        $caixaResumo = $this->resumoCaixa($caixaId);
        $vendas = $this->getVendasPorCaixa($caixaId);

        $dataCaixa = $caixa['data_caixa'];
        $stmt = $this->db->prepare("
            SELECT f.*, u.nome as usuario_nome 
            FROM financeiro f 
            LEFT JOIN usuarios u ON f.usuario_id = u.id 
            WHERE COALESCE(f.data_pagamento, DATE(f.created_at)) = :data
            ORDER BY f.created_at ASC
        ");
        $stmt->execute([':data' => $dataCaixa]);
        $movimentacoes = $stmt->fetchAll() ?: [];

        $totalReceitas = 0.0;
        $totalDespesas = 0.0;
        foreach ($movimentacoes as $m) {
            if (($m['tipo'] ?? '') === 'Receita') {
                $totalReceitas += (float) ($m['valor'] ?? 0);
            } else {
                $totalDespesas += (float) ($m['valor'] ?? 0);
            }
        }
        $saldoFinanceiro = $totalReceitas - $totalDespesas;

        $settingsModel = new \App\Models\ConfigModel();
        $settings = $settingsModel->getAll();
        $nomeEmpresa = $settings['nome_empresa'] ?? 'Conectados';

        $valorInicial = (float) ($caixa['valor_inicial'] ?? 0);
        $valorInformado = ($caixa['valor_informado'] !== null) ? (float) $caixa['valor_informado'] : null;
        $totalPdv = (float) ($caixaResumo['total'] ?? 0);

        $entradasDinheiro = 0.0;
        $saidasDinheiro = 0.0;
        foreach ($movimentacoes as $m) {
            $forma = strtolower((string) ($m['forma_pagamento'] ?? ''));
            if (str_contains($forma, 'dinheiro')) {
                if (($m['tipo'] ?? '') === 'Receita') {
                    $entradasDinheiro += (float) ($m['valor'] ?? 0);
                } else {
                    $saidasDinheiro += (float) ($m['valor'] ?? 0);
                }
            }
        }
        $dinheiroEsperado = $valorInicial + $entradasDinheiro - $saidasDinheiro;
        $diferencaCaixa = ($valorInformado !== null) ? ($valorInformado - $dinheiroEsperado) : 0.0;

        $dataCaixaFmt = date('d/m/Y', strtotime($caixa['data_caixa']));
        $abertoEmFmt = $caixa['aberto_em'] ? date('d/m/Y H:i:s', strtotime($caixa['aberto_em'])) : '-';
        $fechadoEmFmt = $caixa['fechado_em'] ? date('d/m/Y H:i:s', strtotime($caixa['fechado_em'])) : '-';

        $pdf = new \App\Support\PdvReportPdf($nomeEmpresa, $dataCaixaFmt);
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $sectionTitle = function($title) use ($pdf) {
            $pdf->Ln(4);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor(0, 52, 154); // Brand Blue
            $pdf->Cell(0, 7, $pdf->toLatin1(mb_strtoupper($title, 'UTF-8')), 0, 1, 'L');
            $pdf->SetDrawColor(215, 222, 232);
            $pdf->SetLineWidth(0.4);
            $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
            $pdf->Ln(3);
        };

        // 1. Tabela de Metadados (Cabecalho do relatorio)
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(17, 24, 39);
        $pdf->SetDrawColor(215, 222, 232);
        
        $pdf->Cell(30, 6, $pdf->toLatin1('EMPRESA'), 1, 0, 'L');
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(160, 6, $pdf->toLatin1($nomeEmpresa), 1, 1, 'L');
        
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(30, 6, $pdf->toLatin1('PERIODO'), 1, 0, 'L');
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(160, 6, $pdf->toLatin1('Dia ' . $dataCaixaFmt), 1, 1, 'L');
        
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(30, 6, $pdf->toLatin1('EMISSAO'), 1, 0, 'L');
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(160, 6, $pdf->toLatin1(date('d/m/Y H:i')), 1, 1, 'L');
        $pdf->Ln(4);

        // 2. Resumo Executivo
        $sectionTitle('Resumo Executivo');
        
        $pdf->SetFillColor(251, 252, 254);
        $pdf->SetDrawColor(215, 222, 232);
        $pdf->SetTextColor(17, 24, 39);
        $pdf->SetFont('Arial', '', 9);
        
        $execText = "Leitura rapida: No periodo do dia " . $dataCaixaFmt . ", foram registradas entradas de R$ " . number_format($totalReceitas, 2, ',', '.') . " e saidas de R$ " . number_format($totalDespesas, 2, ',', '.') . ", resultando em saldo " . ($saldoFinanceiro >= 0 ? "positivo" : "negativo") . " de R$ " . number_format(abs($saldoFinanceiro), 2, ',', '.') . ".";
        if ($valorInformado !== null) {
            $execText .= " O caixa foi aberto com R$ " . number_format($valorInicial, 2, ',', '.') . " em dinheiro e fechado com contagem manual de R$ " . number_format($valorInformado, 2, ',', '.') . " (diferenca de " . ($diferencaCaixa >= 0 ? "+" : "") . "R$ " . number_format($diferencaCaixa, 2, ',', '.') . ").";
        }
        $pdf->MultiCell(190, 6, $pdf->toLatin1($execText), 1, 'L', true);
        $pdf->Ln(2);

        // 3. Totais do Periodo
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(38, 50, 72);
        $pdf->SetFillColor(245, 247, 251);
        
        $pdf->Cell(47, 6, $pdf->toLatin1('ENTRADAS RECEBIDAS'), 1, 0, 'C', true);
        $pdf->Cell(47, 6, $pdf->toLatin1('SAÍDAS / DESPESAS'), 1, 0, 'C', true);
        $pdf->Cell(47, 6, $pdf->toLatin1('SALDO DO PERÍODO'), 1, 0, 'C', true);
        $pdf->Cell(49, 6, $pdf->toLatin1('LANÇAMENTOS ANALISADOS'), 1, 1, 'C', true);
        
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetTextColor(4, 120, 87); // Verde
        $pdf->Cell(47, 10, $pdf->toLatin1('R$ ' . number_format($totalReceitas, 2, ',', '.')), 1, 0, 'C');
        
        $pdf->SetTextColor(185, 28, 28); // Vermelho
        $pdf->Cell(47, 10, $pdf->toLatin1('R$ ' . number_format($totalDespesas, 2, ',', '.')), 1, 0, 'C');
        
        if ($saldoFinanceiro >= 0) {
            $pdf->SetTextColor(4, 120, 87);
        } else {
            $pdf->SetTextColor(185, 28, 28);
        }
        $pdf->Cell(47, 10, $pdf->toLatin1('R$ ' . number_format($saldoFinanceiro, 2, ',', '.')), 1, 0, 'C');
        
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(17, 24, 39);
        $qtdReceitas = count(array_filter($movimentacoes, fn($m) => $m['tipo'] === 'Receita'));
        $qtdDespesas = count(array_filter($movimentacoes, fn($m) => $m['tipo'] === 'Despesa'));
        $totalLancamentos = count($movimentacoes);
        $pdf->Cell(49, 10, $pdf->toLatin1("$totalLancamentos ($qtdReceitas rec, $qtdDespesas desp)"), 1, 1, 'C');
        $pdf->Ln(4);

        // 4. Identificação e Conciliação do Caixa
        $sectionTitle('Identificação e Conciliação do Caixa');
        
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(38, 50, 72);
        $pdf->SetFillColor(245, 247, 251);
        $pdf->SetDrawColor(215, 222, 232);
        
        // Linha 1: ID e STATUS
        $pdf->Cell(35, 7, $pdf->toLatin1('CAIXA ID'), 1, 0, 'L', true);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(17, 24, 39);
        $pdf->Cell(60, 7, $pdf->toLatin1('#' . $caixa['id']), 1, 0, 'L');
        
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(38, 50, 72);
        $pdf->Cell(35, 7, $pdf->toLatin1('STATUS'), 1, 0, 'L', true);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(185, 28, 28);
        $pdf->Cell(60, 7, $pdf->toLatin1('FECHADO (' . ($caixa['fechamento_tipo'] ?? 'manual') . ')'), 1, 1, 'L');
        
        // Linha 2: ABERTURA e FECHAMENTO
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(38, 50, 72);
        $pdf->Cell(35, 7, $pdf->toLatin1('ABERTURA'), 1, 0, 'L', true);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(17, 24, 39);
        $pdf->Cell(60, 7, $pdf->toLatin1($abertoEmFmt . ' - ' . ($caixa['aberto_por_nome'] ?? 'Sistema')), 1, 0, 'L');
        
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(38, 50, 72);
        $pdf->Cell(35, 7, $pdf->toLatin1('FECHAMENTO'), 1, 0, 'L', true);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(17, 24, 39);
        $pdf->Cell(60, 7, $pdf->toLatin1($fechadoEmFmt . ' - ' . ($caixa['fechado_por_nome'] ?? 'Sistema')), 1, 1, 'L');
        
        // Linha 3: SALDO INICIAL e DINHEIRO ESPERADO
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(38, 50, 72);
        $pdf->Cell(35, 7, $pdf->toLatin1('SALDO INICIAL'), 1, 0, 'L', true);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(17, 24, 39);
        $pdf->Cell(60, 7, $pdf->toLatin1('R$ ' . number_format($valorInicial, 2, ',', '.')), 1, 0, 'L');
        
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(38, 50, 72);
        $pdf->Cell(35, 7, $pdf->toLatin1('DINHEIRO ESPERADO'), 1, 0, 'L', true);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(17, 24, 39);
        $pdf->Cell(60, 7, $pdf->toLatin1('R$ ' . number_format($dinheiroEsperado, 2, ',', '.')), 1, 1, 'L');
        
        // Linha 4: VALOR CONTADO e DIFERENÇA
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(38, 50, 72);
        $pdf->Cell(35, 7, $pdf->toLatin1('VALOR CONTADO'), 1, 0, 'L', true);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(17, 24, 39);
        $pdf->Cell(60, 7, $pdf->toLatin1(($valorInformado !== null) ? 'R$ ' . number_format($valorInformado, 2, ',', '.') : 'Não informado'), 1, 0, 'L');
        
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(38, 50, 72);
        $pdf->Cell(35, 7, $pdf->toLatin1('DIFERENÇA'), 1, 0, 'L', true);
        
        if ($valorInformado !== null) {
            $pdf->SetFont('Arial', 'B', 8);
            if ($diferencaCaixa >= 0) {
                $pdf->SetTextColor(4, 120, 87);
                $difTxt = '+' . number_format($diferencaCaixa, 2, ',', '.');
            } else {
                $pdf->SetTextColor(185, 28, 28);
                $difTxt = '-' . number_format(abs($diferencaCaixa), 2, ',', '.');
            }
            $pdf->Cell(60, 7, $pdf->toLatin1('R$ ' . $difTxt), 1, 1, 'L');
            $pdf->SetTextColor(17, 24, 39);
        } else {
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(17, 24, 39);
            $pdf->Cell(60, 7, $pdf->toLatin1('-'), 1, 1, 'L');
        }
        $pdf->Ln(4);

        // 5. Lançamentos Detalhados Table
        $sectionTitle('Lançamentos Detalhados');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(38, 50, 72);
        $pdf->SetFillColor(245, 247, 251);
        
        $pdf->Cell(20, 6, $pdf->toLatin1('DATA'), 1, 0, 'C', true);
        $pdf->Cell(20, 6, $pdf->toLatin1('NATUREZA'), 1, 0, 'C', true);
        $pdf->Cell(30, 6, $pdf->toLatin1('CATEGORIA'), 1, 0, 'L', true);
        $pdf->Cell(57, 6, $pdf->toLatin1('DESCRIÇÃO'), 1, 0, 'L', true);
        $pdf->Cell(35, 6, $pdf->toLatin1('FORMA'), 1, 0, 'L', true);
        $pdf->Cell(28, 6, $pdf->toLatin1('IMPACTO'), 1, 1, 'R', true);
        
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->SetTextColor(17, 24, 39);
        
        if (empty($movimentacoes)) {
            $pdf->Cell(190, 6, $pdf->toLatin1('Nenhum lançamento encontrado para a data do caixa.'), 1, 1, 'C');
        } else {
            foreach ($movimentacoes as $m) {
                $dataMov = date('d/m/Y', strtotime($m['data_pagamento'] ?: $m['created_at']));
                $pdf->Cell(20, 6, $pdf->toLatin1($dataMov), 1, 0, 'C');
                
                if ($m['tipo'] === 'Receita') {
                    $pdf->SetTextColor(4, 120, 87);
                    $nat = 'Receita';
                } else {
                    $pdf->SetTextColor(185, 28, 28);
                    $nat = 'Despesa';
                }
                $pdf->Cell(20, 6, $pdf->toLatin1($nat), 1, 0, 'C');
                $pdf->SetTextColor(17, 24, 39);
                
                $pdf->Cell(30, 6, $pdf->toLatin1($m['categoria'] ?: 'Sem categoria'), 1, 0, 'L');
                
                // Shorten description to fit nicely in 57mm Portrait
                $desc = $m['descricao'];
                if (strlen($desc) > 32) {
                    $desc = substr($desc, 0, 29) . '...';
                }
                $pdf->Cell(57, 6, $pdf->toLatin1($desc), 1, 0, 'L');
                
                $pdf->Cell(35, 6, $pdf->toLatin1($m['forma_pagamento'] ?: '-'), 1, 0, 'L');
                
                if ($m['tipo'] === 'Receita') {
                    $pdf->SetTextColor(4, 120, 87);
                    $valStr = '+ R$ ' . number_format((float)($m['valor'] ?? 0), 2, ',', '.');
                } else {
                    $pdf->SetTextColor(185, 28, 28);
                    $valStr = '- R$ ' . number_format((float)($m['valor'] ?? 0), 2, ',', '.');
                }
                $pdf->Cell(28, 6, $pdf->toLatin1($valStr), 1, 1, 'R');
                $pdf->SetTextColor(17, 24, 39);
            }
        }
        $pdf->Ln(4);

        // 6. Resumo por Forma de Pagamento (PDV)
        if (!empty($caixaResumo['formas'])) {
            $sectionTitle('Resumo de Vendas por Forma de Pagamento (PDV)');
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetTextColor(38, 50, 72);
            $pdf->SetFillColor(245, 247, 251);
            
            $pdf->Cell(80, 6, $pdf->toLatin1('FORMA DE PAGAMENTO'), 1, 0, 'L', true);
            $pdf->Cell(40, 6, $pdf->toLatin1('QTD VENDAS'), 1, 0, 'C', true);
            $pdf->Cell(70, 6, $pdf->toLatin1('TOTAL RECEBIDO'), 1, 1, 'R', true);
            
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(17, 24, 39);
            foreach ($caixaResumo['formas'] as $f) {
                $pdf->Cell(80, 6, $pdf->toLatin1($f['forma']), 1, 0, 'L');
                $pdf->Cell(40, 6, $pdf->toLatin1((string)$f['qtd']), 1, 0, 'C');
                $pdf->Cell(70, 6, $pdf->toLatin1('R$ ' . number_format((float)($f['total'] ?? 0), 2, ',', '.')), 1, 1, 'R');
            }
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(80, 7, $pdf->toLatin1('TOTAL VENDAS PDV'), 1, 0, 'L', true);
            $pdf->Cell(40, 7, $pdf->toLatin1((string)($caixaResumo['qtd'] ?? 0)), 1, 0, 'C', true);
            $pdf->Cell(70, 7, $pdf->toLatin1('R$ ' . number_format($totalPdv, 2, ',', '.')), 1, 1, 'R', true);
            $pdf->Ln(4);
        }

        if (!empty($caixa['observacoes'])) {
            $sectionTitle('Observações do Fechamento');
            $pdf->SetFont('Arial', '', 9);
            $pdf->MultiCell(190, 5, $pdf->toLatin1($caixa['observacoes']), 1, 'L');
        }

        // Caminho de salvamento
        $defaultDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'relatorios_financeiro';
        $saveDir = $settings['fechamento_caixa_pasta'] ?? $defaultDir;
        $saveDir = trim($saveDir);
        if ($saveDir === '') {
            $saveDir = $defaultDir;
        }

        // Criar pasta caso não exista
        if (!is_dir($saveDir)) {
            @mkdir($saveDir, 0777, true);
        }

        $fileName = 'fechamento-caixa-' . $dataCaixa . '-id' . $caixaId . '.pdf';
        $filePath = rtrim($saveDir, '/\\') . DIRECTORY_SEPARATOR . $fileName;

        // Gravar PDF no arquivo
        $pdf->Output('F', $filePath);
    }
}
