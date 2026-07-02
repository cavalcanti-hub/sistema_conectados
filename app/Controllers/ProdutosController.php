<?php

namespace App\Controllers;

use App\Core\Controller;

class ProdutosController extends Controller
{
    private $model;
    private $catModel;
    private $uploadError = null;
    private $uploadedImageMime = null;
    private $uploadedImageBlob = null;
    private const FORM_TOKEN_KEY = 'produtos_form_tokens';
    private const RECENT_SUBMISSIONS_KEY = 'produtos_recent_submissions';

    public function __construct()
    {
        $this->model = new \App\Models\EstoqueModel();
        $this->catModel = new \App\Models\CategoriasModel();
    }

    private function getCategoriasProduto(?array $item = null): array
    {
        $categorias = array_column($this->catModel->getAll('produto'), 'nome');

        if (empty($categorias)) {
            $categorias = ['Smartphones', 'Tablets', 'Notebooks', 'Acessórios', 'Áudio', 'Carregadores', 'Capas / Películas', 'Gamer', 'Outros'];
        }

        if (!empty($item['categoria'])) {
            $categorias[] = $item['categoria'];
        }

        return array_values(array_unique(array_filter($categorias)));
    }

    private function storeUploadedImage(array $file): ?string
    {
        $this->uploadError = null;
        $this->uploadedImageMime = null;
        $this->uploadedImageBlob = null;

        $result = validate_and_store_image_upload($file, 'estoque');
        if (!$result['ok']) {
            $this->uploadError = (string) $result['error'];
            return null;
        }

        $this->uploadedImageMime = $result['mime'];
        $this->uploadedImageBlob = $result['blob'];
        if (!empty($result['warning'])) {
            app_log('Aviso no upload de produto', ['warning' => $result['warning']]);
        }

        return $result['name'];
    }

    private function uploadErrorMessage(int $code): string
    {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'a imagem excede o limite configurado no servidor.',
            UPLOAD_ERR_FORM_SIZE => 'a imagem excede o limite permitido pelo formulario.',
            UPLOAD_ERR_PARTIAL => 'o envio da imagem foi interrompido.',
            UPLOAD_ERR_NO_TMP_DIR => 'a pasta temporaria de uploads nao esta disponivel.',
            UPLOAD_ERR_CANT_WRITE => 'o servidor nao conseguiu gravar a imagem.',
            UPLOAD_ERR_EXTENSION => 'uma extensao do PHP bloqueou o upload.',
        ];

        return $messages[$code] ?? 'nao foi possivel enviar a imagem.';
    }

    private function galleryUploadFiles(): array
    {
        $input = $_FILES['galeria_imagens'] ?? null;
        if (empty($input) || empty($input['name']) || !is_array($input['name'])) {
            return [];
        }

        $files = [];
        foreach ($input['name'] as $index => $name) {
            if (trim((string) $name) === '') {
                continue;
            }

            $files[] = [
                'name' => $name,
                'type' => $input['type'][$index] ?? '',
                'tmp_name' => $input['tmp_name'][$index] ?? '',
                'error' => $input['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                'size' => $input['size'][$index] ?? 0,
            ];
        }

        return array_slice($files, 0, 8);
    }

    private function saveGalleryUploads(int $productId): void
    {
        foreach ($this->galleryUploadFiles() as $file) {
            $result = validate_and_store_image_upload($file, 'estoque');
            if (!$result['ok']) {
                throw new \RuntimeException('Imagem extra: ' . $result['error']);
            }

            if (!empty($result['name'])) {
                $this->model->addProductImage($productId, (string) $result['name'], $result['mime'] ?? null);
            }

            if (!empty($result['warning'])) {
                app_log('Aviso no upload de imagem extra do produto', [
                    'produto_id' => $productId,
                    'warning' => $result['warning'],
                ]);
            }
        }
    }

    private function decimalInput($value): float
    {
        $value = trim((string) $value);
        if ($value === '') {
            return 0.0;
        }

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
        }

        return (float) str_replace(',', '.', $value);
    }

    private function normalizeTextInput($value): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', (string) $value) ?? (string) $value);
        if ($text === '') {
            return '';
        }

        $length = \function_exists('mb_strlen') ? \mb_strlen($text, 'UTF-8') : strlen($text);
        if ($length < 8 || $length % 2 !== 0) {
            return $text;
        }

        $half = (int) ($length / 2);
        $first = trim(\function_exists('mb_substr') ? \mb_substr($text, 0, $half, 'UTF-8') : substr($text, 0, $half));
        $second = trim(\function_exists('mb_substr') ? \mb_substr($text, $half, null, 'UTF-8') : substr($text, $half));

        $firstCompare = \function_exists('mb_strtolower') ? \mb_strtolower($first, 'UTF-8') : strtolower($first);
        $secondCompare = \function_exists('mb_strtolower') ? \mb_strtolower($second, 'UTF-8') : strtolower($second);

        return $first !== '' && $firstCompare === $secondCompare
            ? $first
            : $text;
    }

    private function createFormToken(): string
    {
        try {
            $token = bin2hex(random_bytes(16));
        } catch (\Throwable $e) {
            $token = sha1(uniqid('', true));
        }

        $_SESSION[self::FORM_TOKEN_KEY] = $_SESSION[self::FORM_TOKEN_KEY] ?? [];
        $_SESSION[self::FORM_TOKEN_KEY][$token] = time();

        foreach ($_SESSION[self::FORM_TOKEN_KEY] as $key => $createdAt) {
            if ((int) $createdAt < time() - 7200) {
                unset($_SESSION[self::FORM_TOKEN_KEY][$key]);
            }
        }

        return $token;
    }

    private function consumeFormToken(): bool
    {
        $token = (string) ($_POST['_form_token'] ?? '');
        if ($token === '' || empty($_SESSION[self::FORM_TOKEN_KEY][$token])) {
            return false;
        }

        unset($_SESSION[self::FORM_TOKEN_KEY][$token]);
        return true;
    }

    private function hasSubmittedToken(): bool
    {
        return (string) ($_POST['_form_token'] ?? '') !== '';
    }

    private function submissionSignature(array $payload, ?int $id = null): string
    {
        $file = $_FILES['imagem'] ?? [];

        return sha1(json_encode([
            'id' => $id,
            'nome' => $payload[':nome'] ?? '',
            'codigo' => $payload[':codigo_interno'] ?? '',
            'categoria' => $payload[':categoria'] ?? '',
            'preco' => $payload[':preco_venda'] ?? '',
            'quantidade' => $payload[':quantidade'] ?? '',
            'imagem_nome' => $file['name'] ?? '',
            'imagem_tamanho' => $file['size'] ?? '',
        ]));
    }

    private function wasRecentlySubmitted(string $signature): bool
    {
        $_SESSION[self::RECENT_SUBMISSIONS_KEY] = $_SESSION[self::RECENT_SUBMISSIONS_KEY] ?? [];
        $now = time();

        foreach ($_SESSION[self::RECENT_SUBMISSIONS_KEY] as $key => $createdAt) {
            if ((int) $createdAt < $now - 30) {
                unset($_SESSION[self::RECENT_SUBMISSIONS_KEY][$key]);
            }
        }

        return isset($_SESSION[self::RECENT_SUBMISSIONS_KEY][$signature]);
    }

    private function markSubmitted(string $signature): void
    {
        $_SESSION[self::RECENT_SUBMISSIONS_KEY] = $_SESSION[self::RECENT_SUBMISSIONS_KEY] ?? [];
        $_SESSION[self::RECENT_SUBMISSIONS_KEY][$signature] = time();
    }

    private function normalizePayload(array $current = []): array
    {
        $nome = $this->normalizeTextInput($_POST['nome'] ?? ($current['nome'] ?? ''));
        $codigoInterno = trim((string) ($_POST['codigo_interno'] ?? ($current['codigo_interno'] ?? '')));
        $categoria = trim((string) ($_POST['categoria'] ?? ($current['categoria'] ?? 'Outros')));
        $marcaCompativel = trim((string) ($_POST['marca_compativel'] ?? ($current['marca_compativel'] ?? '')));
        $modeloCompativel = trim((string) ($_POST['modelo_compativel'] ?? ($current['modelo_compativel'] ?? '')));
        $fornecedor = trim((string) ($_POST['fornecedor'] ?? ($current['fornecedor'] ?? '')));
        $localizacao = trim((string) ($_POST['localizacao'] ?? ($current['localizacao'] ?? '')));

        return [
            ':codigo_interno' => $codigoInterno,
            ':nome' => $nome,
            ':tipo' => 'produto',
            ':categoria' => $categoria !== '' ? $categoria : 'Outros',
            ':marca_compativel' => $marcaCompativel,
            ':modelo_compativel' => $modeloCompativel,
            ':quantidade' => max(0, (int) ($_POST['quantidade'] ?? ($current['quantidade'] ?? 0))),
            ':estoque_minimo' => max(0, (int) ($_POST['estoque_minimo'] ?? ($current['estoque_minimo'] ?? 0))),
            ':custo' => $this->decimalInput($_POST['custo'] ?? ($current['custo'] ?? 0)),
            ':preco_venda' => $this->decimalInput($_POST['preco_venda'] ?? ($current['preco_venda'] ?? 0)),
            ':fornecedor' => $fornecedor,
            ':localizacao' => $localizacao,
        ];
    }

    private function abortInvalidPayload(array $payload): void
    {
        if ($payload[':nome'] === '') {
            http_response_code(422);
            exit('Erro ao salvar o produto: o nome e obrigatorio.');
        }

        if ($this->uploadError !== null) {
            http_response_code(422);
            exit('Erro ao salvar o produto: ' . $this->uploadError);
        }
    }

    public function index()
    {
        $search = $_GET['search'] ?? '';
        $categoria = $_GET['categoria'] ?? '';
        $pager = pagination_request(20, 100);
        $pagination = pagination_meta($this->model->countFiltered($search, $categoria, 'produto'), $pager['page'], $pager['per_page']);
        $items = $this->model->getAll($search, $categoria, 'produto', $pagination['per_page'], $pagination['offset']);
        $categorias = array_values(array_unique(array_merge(
            array_column($this->catModel->getAll('produto'), 'nome'),
            $this->model->getCategorias('produto')
        )));

        $this->view('produtos/index', [
            'title' => 'Vitrine / Produtos - Conectados',
            'page_title' => 'Gestao de Produtos da Vitrine',
            'items' => $items,
            'categorias' => $categorias,
            'search' => $search,
            'categoria' => $categoria,
            'pagination' => $pagination,
            'totalValor' => $this->model->totalValorVenda('produto'),
            'total' => $this->model->count('produto')
        ]);
    }

    public function print()
    {
        $search = trim((string) ($_GET['search'] ?? ''));
        $categoria = trim((string) ($_GET['categoria'] ?? ''));
        $items = $this->model->getAll($search, $categoria, 'produto');

        $grouped = [];
        foreach ($items as $item) {
            $category = trim((string) ($item['categoria'] ?? ''));
            $category = $category !== '' ? $category : 'Sem categoria';
            $grouped[$category][] = $item;
        }
        ksort($grouped, SORT_NATURAL | SORT_FLAG_CASE);

        $this->view('produtos/print', [
            'title' => 'Impressao do Catalogo de Produtos',
            'items' => $items,
            'grouped' => $grouped,
            'search' => $search,
            'categoria' => $categoria,
            'totalValor' => array_reduce($items, static function (float $sum, array $item): float {
                return $sum + ((float) ($item['preco_venda'] ?? 0) * max(0, (int) ($item['quantidade'] ?? 0)));
            }, 0.0),
        ]);
    }

    public function create()
    {
        $this->view('produtos/create', [
            'title' => 'Novo Produto - Conectados',
            'page_title' => 'Cadastrar Produto para Vitrine',
            'categorias' => $this->getCategoriasProduto(),
            'formToken' => $this->createFormToken(),
        ]);
    }

    public function store()
    {
        if ($this->hasSubmittedToken() && !$this->consumeFormToken()) {
            $this->redirect(route_url('produtos'));
        }

        $payload = $this->normalizePayload();
        $signature = $this->submissionSignature($payload);
        if ($this->wasRecentlySubmitted($signature)) {
            $this->redirect(route_url('produtos', ['success' => 1]));
        }

        $payload[':imagem'] = null;
        $payload[':imagem_mime'] = null;
        $payload[':imagem_blob'] = null;
        $this->abortInvalidPayload($payload);

        $imagemNome = $this->storeUploadedImage($_FILES['imagem'] ?? []);
        $payload[':imagem'] = $imagemNome;
        $payload[':imagem_mime'] = $this->uploadedImageMime;
        $payload[':imagem_blob'] = $this->uploadedImageBlob;

        $this->abortInvalidPayload($payload);
        try {
            $id = (int) $this->model->create($payload);
            $this->saveMercadoLivrePayload($id);
            $this->saveGalleryUploads($id);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                http_response_code(422);
                exit('Erro ao salvar o produto: ja existe produto com este codigo SKU. Volte e informe outro codigo ou deixe em branco.');
            }

            throw $e;
        } catch (\Throwable $e) {
            app_log('Falha ao salvar produto', ['erro' => $e->getMessage()]);
            http_response_code(500);
            exit('Erro ao salvar o produto: ' . $e->getMessage());
        }

        $this->markSubmitted($signature);
        $this->redirect(route_url('produtos', ['success' => 1]));
    }

    public function edit($id = null)
    {
        $id = $id ?? ($_GET['id'] ?? 0);
        $item = $this->model->find($id);

        if (!$item) {
            http_response_code(404);
            exit('Produto nao encontrado para edicao.');
        }

        $this->view('produtos/create', [
            'title' => 'Editar Produto',
            'page_title' => 'Editar Produto',
            'item' => $item,
            'isEdit' => true,
            'categorias' => $this->getCategoriasProduto($item ?: null),
            'formToken' => $this->createFormToken(),
            'mercadoLivreImageUrl' => (new \App\Models\MercadoLivreModel())->publicImageUrlForProduct($item),
            'galleryImages' => $this->model->getImagesForProduct((int) $item['id']),
        ]);
    }

    public function update()
    {
        if ($this->hasSubmittedToken() && !$this->consumeFormToken()) {
            $this->redirect(route_url('produtos'));
        }

        $id = $_POST['id'];
        $itemAtual = $this->model->find($id);

        if (!$itemAtual) {
            http_response_code(404);
            exit('Produto nao encontrado.');
        }

        $imagemNome = $itemAtual['imagem'] ?? null;
        $payload = $this->normalizePayload($itemAtual);
        $signature = $this->submissionSignature($payload, (int) $id);
        if ($this->wasRecentlySubmitted($signature)) {
            $this->redirect(route_url('produtos', ['success' => 1]));
        }

        $payload[':imagem'] = $imagemNome;
        $payload[':imagem_mime'] = $itemAtual['imagem_mime'] ?? null;
        $payload[':imagem_blob'] = $itemAtual['imagem_blob'] ?? null;
        $this->abortInvalidPayload($payload);

        if (!empty($_FILES['imagem']['name'])) {
            $imagemNome = $this->storeUploadedImage($_FILES['imagem']);
        }

        $payload[':imagem'] = $imagemNome;
        if ($this->uploadedImageBlob !== null) {
            $payload[':imagem_mime'] = $this->uploadedImageMime;
            $payload[':imagem_blob'] = $this->uploadedImageBlob;
        }

        $this->abortInvalidPayload($payload);
        try {
            $this->model->update($id, $payload);
            $removeGallery = $_POST['remover_galeria'] ?? [];
            $this->model->deleteProductImages((int) $id, is_array($removeGallery) ? $removeGallery : []);
            $this->saveMercadoLivrePayload((int) $id);
            $this->saveGalleryUploads((int) $id);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                http_response_code(422);
                exit('Erro ao salvar o produto: ja existe produto com este codigo SKU. Volte e informe outro codigo ou deixe em branco.');
            }

            throw $e;
        } catch (\Throwable $e) {
            app_log('Falha ao atualizar produto', ['produto_id' => $id, 'erro' => $e->getMessage()]);
            http_response_code(500);
            exit('Erro ao salvar o produto: ' . $e->getMessage());
        }

        $this->markSubmitted($signature);
        $this->redirect(route_url('produtos', ['success' => 1]));
    }

    private function saveMercadoLivrePayload(int $id): void
    {
        try {
            $this->model->updateMercadoLivreData($id, $this->mercadoLivrePayload());
        } catch (\Throwable $e) {
            app_log('Produto salvo sem atualizar dados do Mercado Livre', [
                'produto_id' => $id,
                'erro' => $e->getMessage(),
            ]);
        }
    }

    private function mercadoLivrePayload(): array
    {
        $condition = trim((string) ($_POST['mercado_livre_condition'] ?? ''));
        if (!in_array($condition, ['new', 'used', 'not_specified'], true)) {
            $condition = null;
        }

        return [
            'mercado_livre_category_id' => trim((string) ($_POST['mercado_livre_category_id'] ?? '')) ?: null,
            'mercado_livre_listing_type_id' => trim((string) ($_POST['mercado_livre_listing_type_id'] ?? '')) ?: null,
            'mercado_livre_condition' => $condition,
            'mercado_livre_attributes' => $this->mercadoLivreAttributesInput(),
        ];
    }

    private function mercadoLivreAttributesInput(): ?string
    {
        $lines = [];
        $barcode = preg_replace('/\D+/', '', (string) ($_POST['mercado_livre_gtin'] ?? '')) ?? '';
        $color = trim((string) ($_POST['mercado_livre_color'] ?? ''));
        $anatel = preg_replace('/\D+/', '', (string) ($_POST['mercado_livre_anatel'] ?? '')) ?? '';
        $partNumber = trim((string) ($_POST['mercado_livre_part_number'] ?? ''));
        $saleFormat = trim((string) ($_POST['mercado_livre_sale_format'] ?? ''));
        $availabilityDays = max(0, (int) ($_POST['mercado_livre_availability_days'] ?? 0));
        $warrantyType = trim((string) ($_POST['mercado_livre_warranty_type'] ?? ''));
        $advanced = trim((string) ($_POST['mercado_livre_attributes'] ?? ''));

        if ($barcode !== '') {
            $lines[] = 'GTIN=' . $barcode;
        }
        if ($color !== '') {
            $lines[] = 'COLOR=' . $color;
        }
        if ($anatel !== '') {
            $lines[] = 'ANATEL_HOMOLOGATION_NUMBER=' . $anatel;
        }
        if ($partNumber !== '') {
            $lines[] = 'PART_NUMBER=' . $partNumber;
        }
        if ($saleFormat !== '') {
            $lines[] = 'SALE_FORMAT=' . $saleFormat;
        }
        if ($warrantyType !== '') {
            $lines[] = 'WARRANTY_TYPE=' . $warrantyType;
        }
        $lines[] = 'MANUFACTURING_TIME=' . $availabilityDays . ' dias';
        if ($advanced !== '') {
            foreach (preg_split('/\r\n|\r|\n/u', $advanced) ?: [] as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $lines[] = $line;
                }
            }
        }

        $lines = array_values(array_unique($lines));
        return empty($lines) ? null : implode("\n", $lines);
    }

    public function delete()
    {
        $id = $_POST['id'] ?? 0;
        $this->model->delete($id);
        $this->redirect(route_url('produtos'));
    }
}
