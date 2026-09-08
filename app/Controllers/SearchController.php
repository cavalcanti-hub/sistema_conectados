<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Config\Database;

class SearchController extends Controller
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function globalSearch(): void
    {
        $query = trim((string) ($_GET['q'] ?? ''));
        if (mb_strlen($query) < 2) {
            $this->json([
                'query' => $query,
                'results' => [
                    'acoes' => $this->getQuickActions(),
                    'os' => [],
                    'clientes' => [],
                    'produtos' => []
                ]
            ]);
            return;
        }

        $param = '%' . $query . '%';

        // 1. Buscar Ordens de Servico
        $stmtOs = $this->db->prepare("
            SELECT os.id, os.numero_os, os.status, c.nome as cliente_nome, a.modelo as aparelho_modelo
            FROM ordens_servico os
            JOIN clientes c ON os.cliente_id = c.id
            JOIN aparelhos a ON os.aparelho_id = a.id
            WHERE os.numero_os LIKE :q
               OR c.nome LIKE :q2
               OR a.modelo LIKE :q3
               OR a.imei LIKE :q4
            ORDER BY os.id DESC
            LIMIT 6
        ");
        $stmtOs->execute([':q' => $param, ':q2' => $param, ':q3' => $param, ':q4' => $param]);
        $osRows = $stmtOs->fetchAll(\PDO::FETCH_ASSOC);

        $osResults = array_map(static function ($row) {
            return [
                'id' => $row['id'],
                'title' => 'OS #' . $row['numero_os'] . ' - ' . $row['aparelho_modelo'],
                'subtitle' => $row['cliente_nome'] . ' • ' . $row['status'],
                'url' => route_url('os/viewDetail', ['id' => $row['id']]),
                'badge' => $row['status'],
                'icon' => 'file-text'
            ];
        }, $osRows);

        // 2. Buscar Clientes
        $stmtCli = $this->db->prepare("
            SELECT id, nome, whatsapp, cpf_cnpj
            FROM clientes
            WHERE nome LIKE :q
               OR whatsapp LIKE :q2
               OR cpf_cnpj LIKE :q3
            ORDER BY id DESC
            LIMIT 5
        ");
        $stmtCli->execute([':q' => $param, ':q2' => $param, ':q3' => $param]);
        $cliRows = $stmtCli->fetchAll(\PDO::FETCH_ASSOC);

        $cliResults = array_map(static function ($row) {
            return [
                'id' => $row['id'],
                'title' => $row['nome'],
                'subtitle' => ($row['whatsapp'] ? $row['whatsapp'] . ' ' : '') . ($row['cpf_cnpj'] ? '• ' . $row['cpf_cnpj'] : ''),
                'url' => route_url('clientes/edit', ['id' => $row['id']]),
                'badge' => 'Cliente',
                'icon' => 'user'
            ];
        }, $cliRows);

        // 3. Buscar Produtos
        $stmtProd = $this->db->prepare("
            SELECT id, nome, preco_venda, quantidade, codigo_barras
            FROM produtos
            WHERE nome LIKE :q
               OR codigo_barras LIKE :q2
            ORDER BY id DESC
            LIMIT 5
        ");
        $stmtProd->execute([':q' => $param, ':q2' => $param]);
        $prodRows = $stmtProd->fetchAll(\PDO::FETCH_ASSOC);

        $prodResults = array_map(static function ($row) {
            return [
                'id' => $row['id'],
                'title' => $row['nome'],
                'subtitle' => 'Estoque: ' . $row['quantidade'] . ' un. • R$ ' . number_format((float)$row['preco_venda'], 2, ',', '.'),
                'url' => route_url('produtos'),
                'badge' => 'R$ ' . number_format((float)$row['preco_venda'], 2, ',', '.'),
                'icon' => 'package'
            ];
        }, $prodRows);

        // 4. Filtrar Ações Rápidas por texto
        $quickActions = array_values(array_filter($this->getQuickActions(), static function ($act) use ($query) {
            return str_contains(mb_strtolower($act['title']), mb_strtolower($query))
                || str_contains(mb_strtolower($act['subtitle']), mb_strtolower($query));
        }));

        $this->json([
            'query' => $query,
            'results' => [
                'acoes' => $quickActions,
                'os' => $osResults,
                'clientes' => $cliResults,
                'produtos' => $prodResults
            ]
        ]);
    }

    private function getQuickActions(): array
    {
        return [
            [
                'id' => 'nova_os',
                'title' => 'Nova Ordem de Serviço',
                'subtitle' => 'Cadastrar entrada de novo aparelho',
                'url' => route_url('os/create'),
                'badge' => 'Atalho',
                'icon' => 'plus-circle'
            ],
            [
                'id' => 'novo_cliente',
                'title' => 'Novo Cliente',
                'subtitle' => 'Cadastrar novo cliente no sistema',
                'url' => route_url('clientes/create'),
                'badge' => 'Atalho',
                'icon' => 'user-plus'
            ],
            [
                'id' => 'pdv_balcao',
                'title' => 'Frente de Caixa (PDV)',
                'subtitle' => 'Venda rápida de balcão e produtos',
                'url' => route_url('pdv'),
                'badge' => 'Atalho',
                'icon' => 'shopping-cart'
            ],
            [
                'id' => 'financeiro',
                'title' => 'Módulo Financeiro',
                'subtitle' => 'Controle de receitas, despesas e fluxo',
                'url' => route_url('financeiro'),
                'badge' => 'Módulo',
                'icon' => 'dollar-sign'
            ],
            [
                'id' => 'produtos',
                'title' => 'Gerenciar Produtos e Peças',
                'subtitle' => 'Estoque e catálogo de componentes',
                'url' => route_url('produtos'),
                'badge' => 'Módulo',
                'icon' => 'boxes'
            ]
        ];
    }
}
