<?php
namespace App\Models;
use App\Config\Database;

class PdvModel {
    private $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function getVendas($limit = 50) {
        $sql = "SELECT v.*, c.nome as cliente_nome, u.nome as operador_nome FROM pdv_vendas v LEFT JOIN clientes c ON v.cliente_id=c.id LEFT JOIN usuarios u ON v.usuario_id=u.id ORDER BY v.created_at DESC LIMIT $limit";
        return $this->db->query($sql)->fetchAll();
    }

    public function findVenda($id) {
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

    public function getItensVenda($venda_id) {
        $stmt = $this->db->prepare("SELECT pi.*, e.nome as produto_nome FROM pdv_itens pi LEFT JOIN estoque e ON pi.produto_id=e.id WHERE pi.venda_id=:id");
        $stmt->execute([':id'=>$venda_id]);
        return $stmt->fetchAll();
    }

    public function criarVenda($data) {
        $numero = 'PDV-' . date('Ymd') . '-' . str_pad(rand(1,999), 3, '0', STR_PAD_LEFT);
        $sql = "INSERT INTO pdv_vendas (numero_venda, cliente_id, os_id, usuario_id, forma_pagamento, desconto, observacoes) VALUES (:num, :cliente_id, :os_id, :usuario_id, :forma_pagamento, :desconto, :obs)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':num'=>$numero, ':cliente_id'=>$data['cliente_id']??null, ':os_id'=>$data['os_id']??null, ':usuario_id'=>$data['usuario_id'], ':forma_pagamento'=>$data['forma_pagamento'], ':desconto'=>$data['desconto']??0, ':obs'=>$data['observacoes']??'']);
        return $this->db->lastInsertId();
    }

    public function addItem($venda_id, $produto_id, $descricao, $qtd, $preco) {
        $stmt = $this->db->prepare("INSERT INTO pdv_itens (venda_id, produto_id, descricao, quantidade, preco_unitario) VALUES (:vid, :pid, :desc, :qtd, :preco)");
        $stmt->execute([':vid'=>$venda_id, ':pid'=>$produto_id, ':desc'=>$descricao, ':qtd'=>$qtd, ':preco'=>$preco]);
    }

    public function finalizarVenda($venda_id, float $taxaPercentual = 0.0) {
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

    public function totalHoje() {
        return $this->db->query("SELECT COALESCE(SUM(total),0) FROM pdv_vendas WHERE DATE(created_at)=CURDATE() AND status='finalizada'")->fetchColumn();
    }
}
