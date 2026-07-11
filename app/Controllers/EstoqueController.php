<?php

namespace App\Controllers;

use App\Core\Controller;

class EstoqueController extends Controller
{
    private $model;
    private $catModel;
    private $uploadError = null;
    private $uploadedImageMime = null;
    private $uploadedImageBlob = null;

    public function __construct()
    {
        $this->model = new \App\Models\EstoqueModel();
        $this->catModel = new \App\Models\CategoriasModel();
    }

    private function getCategoriasEstoque(?array $item = null): array
    {
        $categorias = array_merge(
            array_column($this->catModel->getAll('peca'), 'nome'),
            array_column($this->catModel->getAll('produto'), 'nome')
        );

        if (empty($categorias)) {
            $categorias = ['Telas', 'Baterias', 'Conectores', 'Câmeras', 'Alto-falantes', 'Microfones', 'Placas', 'Acessórios', 'Outros'];
        }

        if (!empty($item['categoria'])) {
            $categorias[] = $item['categoria'];
        }

        return array_values(array_unique(array_filter($categorias)));
    }

    private function normalizePayload(array $current = []): array
    {
        $nome = trim((string) ($_POST['nome'] ?? ($current['nome'] ?? '')));
        $codigoInterno = trim((string) ($_POST['codigo_interno'] ?? ($current['codigo_interno'] ?? '')));
        $categoria = trim((string) ($_POST['categoria'] ?? ($current['categoria'] ?? 'Outros')));
        $marcaCompativel = trim((string) ($_POST['marca_compativel'] ?? ($current['marca_compativel'] ?? '')));
        $modeloCompativel = trim((string) ($_POST['modelo_compativel'] ?? ($current['modelo_compativel'] ?? '')));
        $fornecedor = trim((string) ($_POST['fornecedor'] ?? ($current['fornecedor'] ?? '')));
        $localizacao = trim((string) ($_POST['localizacao'] ?? ($current['localizacao'] ?? '')));
        $tipo = (string) ($_POST['tipo_item'] ?? ($current['tipo'] ?? 'produto'));
        if (!in_array($tipo, ['produto', 'peca'], true)) {
            $tipo = 'produto';
        }

        return [
            ':codigo_interno' => $codigoInterno,
            ':nome' => $nome,
            ':tipo' => $tipo,
            ':categoria' => $categoria !== '' ? $categoria : 'Outros',
            ':marca_compativel' => $marcaCompativel,
            ':modelo_compativel' => $modeloCompativel,
            ':quantidade' => max(0, (int) ($_POST['quantidade'] ?? ($current['quantidade'] ?? 0))),
            ':estoque_minimo' => max(0, (int) ($_POST['estoque_minimo'] ?? ($current['estoque_minimo'] ?? 5))),
            ':custo' => (float) str_replace(',', '.', (string) ($_POST['custo'] ?? ($current['custo'] ?? 0))),
            ':preco_venda' => (float) str_replace(',', '.', (string) ($_POST['preco_venda'] ?? ($current['preco_venda'] ?? 0))),
            ':fornecedor' => $fornecedor,
            ':localizacao' => $localizacao,
        ];
    }

    private function abortInvalidPayload(array $payload): void
    {
        if ($payload[':nome'] === '') {
            http_response_code(422);
            exit('Erro ao salvar o item do estoque: o nome e obrigatorio.');
        }

        if ($this->uploadError !== null) {
            http_response_code(422);
            exit('Erro ao salvar o item do estoque: ' . $this->uploadError);
        }
    }

    private function storeUploadedImage(array $file): ?string
    {
        $this->uploadedImageMime = null;
        $this->uploadedImageBlob = null;
        $this->uploadError = null;

        $result = validate_and_store_image_upload($file, 'estoque');
        if (!$result['ok']) {
            $this->uploadError = (string) $result['error'];
            return null;
        }

        $this->uploadedImageMime = $result['mime'];
        $this->uploadedImageBlob = $result['blob'];
        if (!empty($result['warning'])) {
            app_log('Aviso no upload de estoque', ['warning' => $result['warning']]);
        }

        return $result['name'];
    }

    public function index()
    {
        $search = $_GET['search'] ?? '';
        $categoria = $_GET['categoria'] ?? '';
        $pager = pagination_request(20, 100);
        $pagination = pagination_meta($this->model->countFiltered($search, $categoria, null), $pager['page'], $pager['per_page']);
        $items = $this->model->getAll($search, $categoria, null, $pagination['per_page'], $pagination['offset']);
        $baixo = $this->model->getEstoqueBaixo(null);
        $categorias = array_values(array_unique(array_merge(
            array_column($this->catModel->getAll('peca'), 'nome'),
            array_column($this->catModel->getAll('produto'), 'nome'),
            $this->model->getCategorias(null)
        )));
        $this->view('estoque/index', [
            'title' => 'Controle de Estoque - Conectados',
            'page_title' => 'Controle de Estoque',
            'items' => $items,
            'baixo' => $baixo,
            'categorias' => $categorias,
            'search' => $search,
            'categoria' => $categoria,
            'pagination' => $pagination,
            'totalValor' => $this->model->totalValor(null),
            'total' => $this->model->count(null)
        ]);
    }

    public function create()
    {
        $this->view('estoque/create', [
            'title' => 'Novo Item de Estoque - Conectados',
            'page_title' => 'Cadastrar Item de Estoque',
            'categorias' => $this->getCategoriasEstoque(),
        ]);
    }

    public function store()
    {
        $imagemNome = $this->storeUploadedImage($_FILES['imagem'] ?? []);
        $payload = $this->normalizePayload();
        $payload[':imagem'] = $imagemNome;
        $payload[':imagem_mime'] = $this->uploadedImageMime;
        $payload[':imagem_blob'] = $this->uploadedImageBlob;
        $this->abortInvalidPayload($payload);
        $id = (int) $this->model->create($payload);
        (new \App\Models\AuditModel())->record('criar', 'estoque', $id, 'Item de estoque criado: ' . $payload[':nome'], [
            'quantidade' => $payload[':quantidade'],
            'tipo' => $payload[':tipo'],
        ]);
        $this->redirect(absolute_route_url('estoque', ['success' => 1]));
    }

    public function edit()
    {
        $id = $_GET['id'] ?? 0;
        $item = $this->model->find($id);
        $this->view('estoque/create', [
            'title' => 'Editar Item de Estoque',
            'page_title' => 'Editar Item de Estoque',
            'item' => $item,
            'isEdit' => true,
            'categorias' => $this->getCategoriasEstoque($item ?: null),
        ]);
    }

    public function update()
    {
        $id = $_POST['id'];
        $itemAtual = $this->model->find($id);

        $imagemNome = $itemAtual['imagem'] ?? null;
        if (!empty($_FILES['imagem']['name'])) {
            $imagemNome = $this->storeUploadedImage($_FILES['imagem']);
        }

        $payload = $this->normalizePayload($itemAtual ?: []);
        $payload[':imagem'] = $imagemNome;
        $payload[':imagem_mime'] = $itemAtual['imagem_mime'] ?? null;
        $payload[':imagem_blob'] = $itemAtual['imagem_blob'] ?? null;
        if ($this->uploadedImageBlob !== null) {
            $payload[':imagem_mime'] = $this->uploadedImageMime;
            $payload[':imagem_blob'] = $this->uploadedImageBlob;
        }
        $this->abortInvalidPayload($payload);
        $this->model->update($id, $payload);
        (new \App\Models\AuditModel())->record('editar', 'estoque', (int) $id, 'Item de estoque editado: ' . $payload[':nome'], [
            'quantidade' => $payload[':quantidade'],
            'preco_venda' => $payload[':preco_venda'],
        ]);
        $this->redirect(absolute_route_url('estoque', ['success' => 1]));
    }

    public function delete()
    {
        $id = $_POST['id'] ?? 0;
        $item = $this->model->find($id);
        $this->model->delete($id);
        (new \App\Models\AuditModel())->record('excluir', 'estoque', (int) $id, 'Item de estoque excluido: ' . ($item['nome'] ?? '#' . $id));
        $this->redirect(absolute_route_url('estoque'));
    }

    public function movimentar()
    {
        $id = $_POST['produto_id'];
        $tipo = $_POST['tipo'];
        $qtd = (int) $_POST['quantidade'];
        $motivo = $_POST['motivo'] ?? '';
        try {
            if ($tipo === 'entrada') {
                $this->model->entradaEstoque($id, $qtd, $motivo, current_user_id());
            } else {
                $this->model->baixarEstoque($id, $qtd, null, current_user_id(), $motivo !== '' ? $motivo : 'Baixa manual');
            }
            $item = $this->model->find($id);
            (new \App\Models\AuditModel())->record($tipo === 'entrada' ? 'entrada' : 'saida', 'estoque', (int) $id, 'Movimentacao de estoque: ' . ($item['nome'] ?? '#' . $id), [
                'tipo' => $tipo,
                'quantidade' => $qtd,
                'motivo' => $motivo,
            ]);
        } catch (\Throwable $e) {
            $this->redirect(absolute_route_url('estoque', ['erro_msg' => substr($e->getMessage(), 0, 160)]));
        }
        $this->redirect(absolute_route_url('estoque', ['success' => 1]));
    }
}
