<?php

namespace App\Models;

use App\Config\Database;

class EstoqueModel
{
    private $db;
    private ?array $estoqueColumns = null;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function getImageDirectories()
    {
        $baseDir = dirname(__DIR__, 2);
        $publicUploads = $baseDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';
        $rootUploads = $baseDir . DIRECTORY_SEPARATOR . 'uploads';

        return [
            [
                'disk' => $publicUploads,
                'url' => app_url('uploads') . '/',
                'mirror' => false,
            ],
            [
                'disk' => $publicUploads . DIRECTORY_SEPARATOR . 'estoque',
                'url' => app_url('uploads/estoque') . '/',
                'mirror' => false,
            ],
            [
                'disk' => $rootUploads,
                'url' => app_url('uploads') . '/',
                'mirror' => true,
            ],
            [
                'disk' => $rootUploads . DIRECTORY_SEPARATOR . 'estoque',
                'url' => app_url('uploads/estoque') . '/',
                'mirror' => false,
            ],
        ];
    }

    private function mirrorToPublicUploads(string $sourcePath, string $fileName): void
    {
        $target = public_path('uploads' . DIRECTORY_SEPARATOR . $fileName);
        $targetDir = dirname($target);

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (!is_file($target)) {
            copy($sourcePath, $target);
        }
    }

    private function extractImageName($imageName): ?string
    {
        if (!$imageName) {
            return null;
        }

        $imageName = trim((string) $imageName);
        $path = parse_url($imageName, PHP_URL_PATH);
        if (is_string($path) && $path !== '') {
            $imageName = $path;
        }

        $imageName = str_replace('\\', '/', rawurldecode($imageName));
        $imageName = basename($imageName);

        return $imageName !== '' && $imageName !== '.' ? $imageName : null;
    }

    private function findImageAsset($imageName)
    {
        $imageName = $this->extractImageName($imageName);
        if (!$imageName) {
            return null;
        }

        foreach ($this->getImageDirectories() as $directory) {
            $fullPath = $directory['disk'] . DIRECTORY_SEPARATOR . $imageName;
            if (!is_file($fullPath)) {
                continue;
            }

            if (!empty($directory['mirror'])) {
                $this->mirrorToPublicUploads($fullPath, $imageName);
            }

            return [
                'name' => $imageName,
                'disk' => $fullPath,
                'url' => route_url('media/estoque', ['file' => $imageName]),
            ];
        }

        return null;
    }

    private function isSystemDemoImage($imageName): bool
    {
        $imageName = $this->extractImageName($imageName);
        if (!$imageName) {
            return false;
        }

        $imageName = strtolower($imageName);
        if (str_starts_with($imageName, 'demo-')) {
            return true;
        }

        return in_array($imageName, [
            'android.png',
            'smartwhat.jpg',
            'smartwhat02.jpg',
            'fone01.png',
            'fone02.png',
            'capa.png',
            'iphone.jpg',
            'demo-blue-tech.png',
            'demo-capinhas.jpeg',
            'demo-dark-tech.png',
            '9cc7e5ddc31a657c5245ddb2.png',
        ], true);
    }

    private function withoutDemoProductsSql(): string
    {
        return " AND NOT (
            tipo = 'produto'
            AND imagem_blob IS NULL
            AND (
                codigo_interno LIKE 'DEMO-%'
                OR codigo_interno IN (
                    'TL-IP11', 'TL-IP12', 'BT-IP11', 'BT-SAM-A30', 'TL-SAM-A32', 'CC-TIPO-C', 'CC-LIGHTNING',
                    'PRD-PB10', 'PRD-C20W', 'PRD-C33W', 'PRD-CBLGT', 'PRD-CBUSC', 'PRD-FBT01', 'PRD-TGMR',
                    'PRD-MWLS', 'PRD-KTM01', 'PRD2-PR02', 'IMG-001', 'IMG-002', 'IMG-003', 'IMG-004',
                    'IMG-005', 'IMG-006', 'IMG-007', 'IMG-008', 'IMG-009', 'IMG-010', 'IMG-011', 'IMG-012',
                    'IMG-013', 'IMG-014', 'IMG-015', 'IMG-016', 'IMG-017', 'IMG-018', 'IMG-019', 'IMG-020'
                )
                OR fornecedor IN ('Cadastro demonstrativo', 'Importados Demo', 'Demo Supplier')
                OR LOWER(COALESCE(fornecedor, '')) LIKE '%demo%'
                OR (COALESCE(codigo_interno, '') = '' AND nome = 'iPhone 14' AND ROUND(COALESCE(preco_venda, 0), 2) = 5000.00)
            )
        )";
    }

    private function hydrateImage(array $item)
    {
        $currentImage = $item['imagem'] ?? null;
        $imageName = $this->extractImageName($currentImage);
        $v = time(); // Quebra-cache
        
        // 1. Prioridade total ao BLOB (imagem salva no banco)
        if ($imageName && !empty($item['imagem_blob'])) {
            $item['imagem'] = $imageName;
            $item['imagem_url'] = route_url('media/estoque', ['file' => $imageName]) . "&v=$v";
            return $item;
        }

        // 2. Tenta encontrar no disco físico
        $asset = $this->findImageAsset($imageName);
        if ($asset) {
            $item['imagem'] = $asset['name'];
            $item['imagem_url'] = $asset['url'] . (strpos($asset['url'], '?') !== false ? "&v=$v" : "?v=$v");
            return $item;
        }

        // 3. Sem imagem, sem fallback de demo
        $item['imagem_url'] = '';
        return $item;
    }

    private function normalizeCodigoInterno(array $data)
    {
        if (array_key_exists(':codigo_interno', $data)) {
            $codigo = trim((string) $data[':codigo_interno']);
            $data[':codigo_interno'] = $codigo !== '' ? $codigo : null;
        }
        return $data;
    }

    private function normalizeImagePayload(array $data): array
    {
        if (!array_key_exists(':imagem_mime', $data)) {
            $data[':imagem_mime'] = null;
        }

        if (!array_key_exists(':imagem_blob', $data)) {
            $data[':imagem_blob'] = null;
        }

        return $data;
    }

    private function listWhere($search = '', $categoria = '', $tipo = null): array
    {
        $sql = " FROM estoque WHERE 1=1";
        $params = [];
        if ($tipo) {
            $sql .= " AND tipo = :tipo";
            $params[':tipo'] = $tipo;
        }
        $sql .= $this->withoutDemoProductsSql();
        if ($search) {
            $sql .= " AND (nome LIKE :s OR codigo_interno LIKE :s2)";
            $params[':s'] = "%$search%";
            $params[':s2'] = "%$search%";
        }
        if ($categoria) {
            $sql .= " AND categoria = :cat";
            $params[':cat'] = $categoria;
        }

        return [$sql, $params];
    }

    public function getAll($search = '', $categoria = '', $tipo = null, ?int $limit = null, int $offset = 0)
    {
        [$where, $params] = $this->listWhere($search, $categoria, $tipo);
        $sql = "SELECT *" . $where;
        $sql .= " ORDER BY nome ASC";
        if ($limit !== null) {
            $limit = max(1, (int) $limit);
            $offset = max(0, (int) $offset);
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return array_map([$this, 'hydrateImage'], $stmt->fetchAll());
    }

    public function countFiltered($search = '', $categoria = '', $tipo = null): int
    {
        [$where, $params] = $this->listWhere($search, $categoria, $tipo);
        $stmt = $this->db->prepare("SELECT COUNT(*)" . $where);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM estoque WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $item = $stmt->fetch();
        return $item ? $this->hydrateImage($item) : $item;
    }

    public function getImagesForProduct(int $productId): array
    {
        if ($productId <= 0) {
            return [];
        }

        try {
            $stmt = $this->db->prepare("SELECT id, imagem, imagem_mime, ordem FROM estoque_imagens WHERE produto_id = :id ORDER BY ordem ASC, id ASC");
            $stmt->execute([':id' => $productId]);
        } catch (\Throwable $e) {
            app_log('Nao foi possivel carregar galeria do produto', ['produto_id' => $productId, 'erro' => $e->getMessage()]);
            return [];
        }

        $images = [];
        $v = time();
        foreach ($stmt->fetchAll() as $row) {
            $imageName = $this->extractImageName($row['imagem'] ?? '');
            if (!$imageName) {
                continue;
            }

            $asset = $this->findImageAsset($imageName);
            if (!$asset) {
                continue;
            }

            $images[] = [
                'id' => (int) ($row['id'] ?? 0),
                'imagem' => $asset['name'],
                'imagem_mime' => $row['imagem_mime'] ?? null,
                'ordem' => (int) ($row['ordem'] ?? 0),
                'imagem_url' => $asset['url'] . (strpos($asset['url'], '?') !== false ? "&v=$v" : "?v=$v"),
            ];
        }

        return $images;
    }

    public function addProductImage(int $productId, string $imageName, ?string $mime = null): bool
    {
        if ($productId <= 0 || trim($imageName) === '') {
            return false;
        }

        try {
            $stmt = $this->db->prepare("SELECT COALESCE(MAX(ordem), -1) + 1 FROM estoque_imagens WHERE produto_id = :id");
            $stmt->execute([':id' => $productId]);
            $order = (int) $stmt->fetchColumn();

            $stmt = $this->db->prepare("INSERT INTO estoque_imagens (produto_id, imagem, imagem_mime, ordem) VALUES (:produto_id, :imagem, :mime, :ordem)");
            return $stmt->execute([
                ':produto_id' => $productId,
                ':imagem' => basename($imageName),
                ':mime' => $mime,
                ':ordem' => $order,
            ]);
        } catch (\Throwable $e) {
            app_log('Produto salvo sem galeria extra', ['produto_id' => $productId, 'erro' => $e->getMessage()]);
            return false;
        }
    }

    public function deleteProductImages(int $productId, array $imageIds): void
    {
        $imageIds = array_values(array_filter(array_map('intval', $imageIds), static fn($id) => $id > 0));
        if ($productId <= 0 || empty($imageIds)) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($imageIds), '?'));
        $stmt = $this->db->prepare("DELETE FROM estoque_imagens WHERE produto_id = ? AND id IN ($placeholders)");
        $stmt->execute(array_merge([$productId], $imageIds));
    }

    public function create($data)
    {
        $data = $this->normalizeCodigoInterno($data);
        $data = $this->normalizeImagePayload($data);
        $fields = $this->filterWritableEstoqueFields([
            'codigo_interno',
            'nome',
            'tipo',
            'categoria',
            'marca_compativel',
            'modelo_compativel',
            'quantidade',
            'estoque_minimo',
            'custo',
            'preco_venda',
            'fornecedor',
            'localizacao',
            'imagem',
            'imagem_mime',
            'imagem_blob',
        ]);

        $columns = implode(', ', $fields);
        $placeholders = ':' . implode(', :', $fields);
        $sql = "INSERT INTO estoque ($columns) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        
        // Vincula imagem_blob como LOB para suportar arquivos maiores
        foreach ($this->payloadForFields($data, $fields) as $key => $val) {
            $type = \PDO::PARAM_STR;
            if ($key === ':imagem_blob' && $val !== null) {
                $type = \PDO::PARAM_LOB;
            }
            $stmt->bindValue($key, $val, $type);
        }
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $data = $this->normalizeCodigoInterno($data);
        $data = $this->normalizeImagePayload($data);
        $fields = $this->filterWritableEstoqueFields([
            'codigo_interno',
            'nome',
            'tipo',
            'categoria',
            'marca_compativel',
            'modelo_compativel',
            'quantidade',
            'estoque_minimo',
            'custo',
            'preco_venda',
            'fornecedor',
            'localizacao',
            'imagem',
            'imagem_mime',
            'imagem_blob',
        ]);

        $sets = array_map(static fn(string $field): string => "`$field`=:$field", $fields);
        $sql = "UPDATE estoque SET " . implode(', ', $sets) . " WHERE id=:id";
        $data = $this->payloadForFields($data, $fields);
        $data[':id'] = $id;
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $key => $val) {
            $type = \PDO::PARAM_STR;
            if ($key === ':imagem_blob' && $val !== null) {
                $type = \PDO::PARAM_LOB;
            }
            $stmt->bindValue($key, $val, $type);
        }
        
        return $stmt->execute();
    }

    private function filterWritableEstoqueFields(array $fields): array
    {
        $availableColumns = $this->estoqueColumns();
        $filtered = array_values(array_filter($fields, static fn(string $field): bool => isset($availableColumns[$field])));

        return !empty($filtered) ? $filtered : $fields;
    }

    private function payloadForFields(array $data, array $fields): array
    {
        $payload = [];
        foreach ($fields as $field) {
            $key = ':' . $field;
            if (array_key_exists($key, $data)) {
                $payload[$key] = $data[$key];
            }
        }

        return $payload;
    }

    public function updateMercadoLivreData(int $id, array $data): bool
    {
        $allowed = [
            'mercado_livre_item_id',
            'mercado_livre_permalink',
            'mercado_livre_status',
            'mercado_livre_category_id',
            'mercado_livre_listing_type_id',
            'mercado_livre_condition',
            'mercado_livre_attributes',
            'mercado_livre_last_sync_at',
            'mercado_livre_last_error',
        ];
        $availableColumns = $this->estoqueColumns();

        $sets = [];
        $params = [':id' => $id];
        foreach ($allowed as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }
            if (!isset($availableColumns[$field])) {
                app_log('Campo Mercado Livre ausente na tabela estoque ao salvar produto', ['campo' => $field]);
                continue;
            }

            $sets[] = "`$field` = :$field";
            $params[":$field"] = $data[$field];
        }

        if (empty($sets)) {
            return true;
        }

        $stmt = $this->db->prepare("UPDATE estoque SET " . implode(', ', $sets) . " WHERE id = :id");
        return $stmt->execute($params);
    }

    private function estoqueColumns(): array
    {
        if ($this->estoqueColumns !== null) {
            return $this->estoqueColumns;
        }

        try {
            $columns = [];
            $stmt = $this->db->query('SHOW COLUMNS FROM estoque');
            foreach ($stmt->fetchAll() as $column) {
                if (!empty($column['Field'])) {
                    $columns[(string) $column['Field']] = true;
                }
            }
            return $this->estoqueColumns = $columns;
        } catch (\Throwable $e) {
            app_log('Nao foi possivel listar colunas do estoque', ['erro' => $e->getMessage()]);
            return $this->estoqueColumns = [];
        }
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM estoque WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function baixarEstoque($id, $qtd, $os_id = null, $usuario_id = null, string $motivo = 'Uso em OS')
    {
        $qtd = (int) $qtd;
        $id = (int) $id;
        if ($id <= 0 || $qtd <= 0) {
            throw new \RuntimeException('Produto ou quantidade invalida para baixa de estoque.');
        }

        $stmt = $this->db->prepare("UPDATE estoque
            SET quantidade = quantidade - :qtd_decremento
            WHERE id = :id AND quantidade >= :qtd_minimo");
        $stmt->execute([
            ':qtd_decremento' => $qtd,
            ':qtd_minimo' => $qtd,
            ':id' => $id,
        ]);
        if ($stmt->rowCount() === 0) {
            throw new \RuntimeException('Estoque insuficiente para concluir a operacao.');
        }

        $this->db->prepare("INSERT INTO estoque_movimentacoes (produto_id, tipo, quantidade, motivo, os_id, usuario_id) VALUES (:pid, 'saida', :qtd, :motivo, :os_id, :uid)")
            ->execute([':pid' => $id, ':qtd' => $qtd, ':motivo' => $motivo, ':os_id' => $os_id, ':uid' => $usuario_id]);
    }

    public function entradaEstoque($id, $qtd, $motivo = 'Compra', $usuario_id = null)
    {
        $qtd = (int) $qtd;
        $id = (int) $id;
        if ($id <= 0 || $qtd <= 0) {
            throw new \RuntimeException('Produto ou quantidade invalida para entrada de estoque.');
        }

        $this->db->prepare("UPDATE estoque SET quantidade = quantidade + :qtd WHERE id = :id")->execute([':qtd' => $qtd, ':id' => $id]);
        $this->db->prepare("INSERT INTO estoque_movimentacoes (produto_id, tipo, quantidade, motivo, usuario_id) VALUES (:pid, 'entrada', :qtd, :motivo, :uid)")
            ->execute([':pid' => $id, ':qtd' => $qtd, ':motivo' => $motivo, ':uid' => $usuario_id]);
    }

    public function getEstoqueBaixo($tipo = 'peca')
    {
        $params = [];
        $whereTipo = '';
        if ($tipo !== null) {
            $whereTipo = ' AND tipo = :tipo';
            $params[':tipo'] = $tipo;
        }
        $stmt = $this->db->prepare("SELECT * FROM estoque WHERE quantidade <= estoque_minimo" . $whereTipo . $this->withoutDemoProductsSql() . " ORDER BY quantidade ASC");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function totalValor($tipo = 'peca')
    {
        $params = [];
        $whereTipo = '';
        if ($tipo !== null) {
            $whereTipo = ' AND tipo = :tipo';
            $params[':tipo'] = $tipo;
        }
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(quantidade * custo), 0) FROM estoque WHERE 1=1" . $whereTipo . $this->withoutDemoProductsSql());
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function totalValorVenda($tipo = 'produto')
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(quantidade * preco_venda), 0) FROM estoque WHERE tipo = :tipo" . $this->withoutDemoProductsSql());
        $stmt->execute([':tipo' => $tipo]);
        return $stmt->fetchColumn();
    }

    public function count($tipo = 'peca')
    {
        $params = [];
        $whereTipo = '';
        if ($tipo !== null) {
            $whereTipo = ' AND tipo = :tipo';
            $params[':tipo'] = $tipo;
        }
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM estoque WHERE 1=1" . $whereTipo . $this->withoutDemoProductsSql());
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function getCategorias($tipo = 'peca')
    {
        $params = [];
        $whereTipo = '';
        if ($tipo !== null) {
            $whereTipo = ' AND tipo = :tipo';
            $params[':tipo'] = $tipo;
        }
        $stmt = $this->db->prepare("SELECT DISTINCT categoria FROM estoque WHERE categoria IS NOT NULL" . $whereTipo . $this->withoutDemoProductsSql() . " ORDER BY categoria");
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getRecent($tipo = 'produto', $limit = 4)
    {
        $limit = max(1, (int) $limit);
        $stmt = $this->db->prepare("SELECT * FROM estoque WHERE tipo = :tipo" . $this->withoutDemoProductsSql() . " ORDER BY created_at DESC, id DESC LIMIT $limit");
        $stmt->execute([':tipo' => $tipo]);
        return array_map([$this, 'hydrateImage'], $stmt->fetchAll());
    }
}
