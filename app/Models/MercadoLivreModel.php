<?php

namespace App\Models;

use App\Config\Database;

class MercadoLivreModel
{
    private const API_BASE = 'https://api.mercadolibre.com';
    private const AUTH_BASE = 'https://auth.mercadolivre.com.br/authorization';

    private \PDO $db;
    private ConfigModel $config;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->config = new ConfigModel();
    }

    public function settings(): array
    {
        return $this->config->getAll();
    }

    public function isConfigured(): bool
    {
        $settings = $this->settings();
        return trim((string) ($settings['mercado_livre_client_id'] ?? '')) !== ''
            && trim((string) ($settings['mercado_livre_client_secret'] ?? '')) !== '';
    }

    public function isConnected(): bool
    {
        $settings = $this->settings();
        return trim((string) ($settings['mercado_livre_refresh_token'] ?? '')) !== ''
            && trim((string) ($settings['mercado_livre_user_id'] ?? '')) !== '';
    }

    public function redirectUri(): string
    {
        return absolute_route_url('mercadolivre/callback');
    }

    public function authorizationUrl(string $state): string
    {
        $settings = $this->settings();
        return self::AUTH_BASE . '?' . http_build_query([
            'response_type' => 'code',
            'client_id' => trim((string) ($settings['mercado_livre_client_id'] ?? '')),
            'redirect_uri' => $this->redirectUri(),
            'state' => $state,
        ]);
    }

    public function exchangeAuthorizationCode(string $code): array
    {
        $settings = $this->settings();
        $token = $this->postForm('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => trim((string) ($settings['mercado_livre_client_id'] ?? '')),
            'client_secret' => trim((string) ($settings['mercado_livre_client_secret'] ?? '')),
            'code' => $code,
            'redirect_uri' => $this->redirectUri(),
        ]);

        $this->storeToken($token);
        $this->syncAuthenticatedUser();

        return $token;
    }

    public function disconnect(): void
    {
        $this->config->update([
            'mercado_livre_access_token' => '',
            'mercado_livre_refresh_token' => '',
            'mercado_livre_token_expires_at' => '',
            'mercado_livre_user_id' => '',
            'mercado_livre_nickname' => '',
        ]);
    }

    public function publishOrSyncProduct(array $product): array
    {
        if (!empty($product['mercado_livre_item_id'])) {
            return $this->syncProduct($product);
        }

        return $this->publishProduct($product);
    }

    public function publishProduct(array $product): array
    {
        $this->validateProductForPublish($product);
        $payload = $this->buildItemPayload($product);

        try {
            $response = $this->request('POST', '/items', $payload);
            if (!empty($response['id'])) {
                $this->sendDescription((string) $response['id'], (string) ($product['localizacao'] ?? ''));
            }
            $this->storeProductSuccess((int) $product['id'], $response);
            $this->log((int) $product['id'], $response['id'] ?? null, 'publicar', 'sucesso', 'Anuncio publicado no Mercado Livre.', $payload);
            return $response;
        } catch (\Throwable $e) {
            $this->storeProductError((int) $product['id'], $e->getMessage());
            $this->log((int) $product['id'], $product['mercado_livre_item_id'] ?? null, 'publicar', 'erro', $e->getMessage(), $payload);
            throw $e;
        }
    }

    public function syncProduct(array $product): array
    {
        $this->validateConnection();
        $itemId = trim((string) ($product['mercado_livre_item_id'] ?? ''));
        if ($itemId === '') {
            throw new \RuntimeException('Produto ainda nao possui item_id do Mercado Livre.');
        }

        $payload = [
            'price' => max(0.01, (float) ($product['preco_venda'] ?? 0)),
            'available_quantity' => max(0, (int) ($product['quantidade'] ?? 0)),
        ];

        try {
            $response = $this->request('PUT', '/items/' . rawurlencode($itemId), $payload);
            $this->sendDescription($itemId, (string) ($product['localizacao'] ?? ''));
            $this->storeProductSuccess((int) $product['id'], $response);
            $this->log((int) $product['id'], $itemId, 'sincronizar', 'sucesso', 'Preco e estoque sincronizados.', $payload);
            return $response;
        } catch (\Throwable $e) {
            if ($this->isNonModifiableSyncError($e->getMessage())) {
                $response = $this->request('GET', '/items/' . rawurlencode($itemId));
                $this->sendDescription($itemId, (string) ($product['localizacao'] ?? ''));
                $this->storeProductSuccess((int) $product['id'], $response);
                $this->log(
                    (int) $product['id'],
                    $itemId,
                    'sincronizar',
                    'aviso',
                    'Mercado Livre nao permitiu alterar preco/estoque deste anuncio pela API. Status local atualizado.',
                    $payload
                );
                return $response + ['sync_warning' => 'preco_estoque_nao_editaveis'];
            }

            $this->storeProductError((int) $product['id'], $e->getMessage());
            $this->log((int) $product['id'], $itemId, 'sincronizar', 'erro', $e->getMessage(), $payload);
            throw $e;
        }
    }

    private function isNonModifiableSyncError(string $message): bool
    {
        return str_contains($message, 'item.price.not_modifiable')
            || str_contains($message, 'available_quantity is not modifiable')
            || str_contains($message, 'field_not_updatable');
    }

    private function buildItemPayload(array $product): array
    {
        $settings = $this->settings();
        $prediction = null;
        $categoryId = $this->categoryIdForProduct($product, $settings);
        if ($categoryId === '') {
            throw new \RuntimeException('Informe uma categoria final do Mercado Livre no produto antes de publicar. Exemplo para mouse: MLB1714.');
        }

        $listingType = trim((string) ($product['mercado_livre_listing_type_id'] ?? ''));
        $listingType = $listingType !== '' ? $listingType : trim((string) ($settings['mercado_livre_default_listing_type_id'] ?? 'gold_special'));

        $condition = trim((string) ($product['mercado_livre_condition'] ?? ''));
        $condition = $condition !== '' ? $condition : trim((string) ($settings['mercado_livre_default_condition'] ?? 'new'));

        $shippingMode = trim((string) ($settings['mercado_livre_default_shipping_mode'] ?? 'me2'));
        $shippingMode = in_array($shippingMode, ['me2', 'custom', 'not_specified'], true) ? $shippingMode : 'me2';

        $payload = [
            'title' => $this->limitText((string) ($product['nome'] ?? ''), 60),
            'category_id' => $categoryId,
            'price' => max(0.01, (float) ($product['preco_venda'] ?? 0)),
            'currency_id' => 'BRL',
            'available_quantity' => max(1, (int) ($product['quantidade'] ?? 1)),
            'buying_mode' => 'buy_it_now',
            'listing_type_id' => $listingType !== '' ? $listingType : 'gold_special',
            'condition' => in_array($condition, ['new', 'used', 'not_specified'], true) ? $condition : 'new',
            'shipping' => [
                'mode' => $shippingMode,
                'local_pick_up' => false,
            ],
        ];

        $saleTerms = $this->saleTermsForProduct($product);
        if (!empty($saleTerms)) {
            $payload['sale_terms'] = $saleTerms;
        }

        $pictures = $this->picturesForProduct($product);
        if (!empty($pictures)) {
            $payload['pictures'] = $pictures;
        }

        $attributes = $this->attributesForProduct($product, $prediction);
        if (!empty($attributes)) {
            $payload['attributes'] = $attributes;
        }

        return $payload;
    }

    private function categoryIdForProduct(array $product, array $settings, ?array $prediction = null): string
    {
        $categoryId = trim((string) ($product['mercado_livre_category_id'] ?? ''));
        if ($categoryId !== '') {
            return $categoryId;
        }

        return '';
    }

    private function predictCategoryForProduct(array $product): ?array
    {
        if (trim((string) ($product['mercado_livre_category_id'] ?? '')) !== '') {
            return null;
        }

        $settings = $this->settings();
        if (trim((string) ($settings['mercado_livre_default_category_id'] ?? '')) !== '') {
            return null;
        }

        $title = $this->categoryPredictionTitle($product);
        if ($title === '') {
            return null;
        }

        try {
            $predictions = $this->request('GET', '/sites/MLB/domain_discovery/search?' . http_build_query([
                'limit' => 1,
                'q' => $title,
            ]));
        } catch (\Throwable $e) {
            app_log('Falha ao predizer categoria Mercado Livre', [
                'produto_id' => $product['id'] ?? null,
                'erro' => $e->getMessage(),
            ]);
            return null;
        }

        $prediction = $predictions[0] ?? null;
        return is_array($prediction) ? $prediction : null;
    }

    private function categoryPredictionTitle(array $product): string
    {
        $parts = [
            $product['nome'] ?? '',
            $product['marca_compativel'] ?? '',
            $product['modelo_compativel'] ?? '',
            $product['categoria'] ?? '',
        ];

        return $this->limitText(implode(' ', array_filter(array_map('trim', array_map('strval', $parts)))), 150);
    }

    private function attributesForProduct(array $product, ?array $prediction = null): array
    {
        $attributes = [];
        foreach (($prediction['attributes'] ?? []) as $attribute) {
            if (!is_array($attribute) || empty($attribute['id'])) {
                continue;
            }

            $id = strtoupper(trim((string) $attribute['id']));
            $valueId = trim((string) ($attribute['value_id'] ?? ''));
            $valueName = trim((string) ($attribute['value_name'] ?? ''));
            if ($id === '' || ($valueId === '' && $valueName === '')) {
                continue;
            }

            $attributes[$id] = ['id' => $id];
            if ($valueId !== '') {
                $attributes[$id]['value_id'] = $valueId;
            }
            if ($valueName !== '') {
                $attributes[$id]['value_name'] = $valueName;
            }
        }

        if (!empty($product['marca_compativel'])) {
            $attributes['BRAND'] = ['id' => 'BRAND', 'value_name' => (string) $product['marca_compativel']];
        }
        if (!empty($product['modelo_compativel'])) {
            $attributes['MODEL'] = ['id' => 'MODEL', 'value_name' => (string) $product['modelo_compativel']];
        }

        $sku = trim((string) ($product['codigo_interno'] ?? ''));
        if ($sku !== '') {
            $attributes['SELLER_SKU'] = ['id' => 'SELLER_SKU', 'value_name' => $sku];
            if (preg_match('/^\d{8}$|^\d{12,14}$/', $sku)) {
                $attributes['GTIN'] = ['id' => 'GTIN', 'value_name' => $sku];
            }
        }

        $saleTermIds = ['WARRANTY_TYPE', 'WARRANTY_TIME', 'MANUFACTURING_TIME'];
        foreach ($this->parseExtraAttributes((string) ($product['mercado_livre_attributes'] ?? '')) as $attribute) {
            if (in_array(strtoupper((string) ($attribute['id'] ?? '')), $saleTermIds, true)) {
                continue;
            }
            $attributes[$attribute['id']] = $attribute;
        }

        return array_values($attributes);
    }

    private function saleTermsForProduct(array $product): array
    {
        $extras = $this->extraAttributeMap((string) ($product['mercado_livre_attributes'] ?? ''));
        $warrantyType = trim((string) ($extras['WARRANTY_TYPE'] ?? 'Sem garantia'));
        $manufacturingTime = trim((string) ($extras['MANUFACTURING_TIME'] ?? '0 dias'));

        $saleTerms = [];
        if ($warrantyType !== '') {
            $saleTerms[] = ['id' => 'WARRANTY_TYPE', 'value_name' => $warrantyType];
        }
        if ($manufacturingTime !== '') {
            $saleTerms[] = ['id' => 'MANUFACTURING_TIME', 'value_name' => $manufacturingTime];
        }

        return $saleTerms;
    }

    private function extraAttributeMap(string $raw): array
    {
        $map = [];
        foreach ($this->parseExtraAttributes($raw) as $attribute) {
            $id = strtoupper(trim((string) ($attribute['id'] ?? '')));
            $value = trim((string) ($attribute['value_name'] ?? ''));
            if ($id !== '' && $value !== '') {
                $map[$id] = $value;
            }
        }

        return $map;
    }

    private function parseExtraAttributes(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        $pairs = [];
        $json = json_decode($raw, true);
        if (is_array($json)) {
            foreach ($json as $id => $value) {
                if (is_array($value)) {
                    $id = (string) ($value['id'] ?? $id);
                    $value = $value['value_name'] ?? $value['value'] ?? '';
                }
                $pairs[(string) $id] = (string) $value;
            }
        } else {
            foreach (preg_split('/\r\n|\r|\n|;/u', $raw) ?: [] as $line) {
                $line = trim($line);
                if ($line === '' || !preg_match('/^([A-Z0-9_:-]+)\s*[=:]\s*(.+)$/i', $line, $match)) {
                    continue;
                }
                $pairs[(string) $match[1]] = (string) $match[2];
            }
        }

        $attributes = [];
        foreach ($pairs as $id => $value) {
            $id = strtoupper(trim($id));
            $value = trim((string) $value);
            if ($id === '' || $value === '') {
                continue;
            }
            $attributes[] = ['id' => $id, 'value_name' => $value];
        }

        return $attributes;
    }

    private function picturesForProduct(array $product): array
    {
        $urls = [];
        $primaryUrl = $this->publicImageUrlForProduct($product);
        if ($primaryUrl !== '') {
            $urls[] = $primaryUrl;
        } elseif (trim((string) ($product['imagem'] ?? '')) !== '') {
            throw new \RuntimeException('A imagem do produto nao esta em uma URL publica acessivel pelo Mercado Livre.');
        }

        $productId = (int) ($product['id'] ?? 0);
        if ($productId > 0) {
            foreach ((new EstoqueModel())->getImagesForProduct($productId) as $galleryImage) {
                $imageName = trim((string) ($galleryImage['imagem'] ?? ''));
                if ($imageName === '') {
                    continue;
                }

                $url = absolute_route_url('media/estoque', ['file' => basename($imageName)]);
                if ($this->isPublicUrl($url)) {
                    $urls[] = $url;
                }
            }
        }

        $urls = array_slice(array_values(array_unique($urls)), 0, 12);
        return array_map(static fn($url) => ['source' => $url], $urls);
    }

    public function publicImageUrlForProduct(array $product): string
    {
        $image = trim((string) ($product['imagem'] ?? ''));
        if ($image === '') {
            return '';
        }

        $url = absolute_route_url('media/estoque', ['file' => basename($image)]);
        return $this->isPublicUrl($url) ? $url : '';
    }

    private function isPublicUrl(string $url): bool
    {
        $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?: ''));
        return $host !== '' && !in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }

    private function validateConnection(): void
    {
        if (!function_exists('curl_init')) {
            throw new \RuntimeException('A extensao cURL do PHP precisa estar habilitada para usar o Mercado Livre.');
        }

        if (!$this->isConfigured()) {
            throw new \RuntimeException('Configure Client ID e Client Secret em Configuracoes > Marketplace.');
        }

        if (!$this->isConnected()) {
            throw new \RuntimeException('Conecte sua conta do Mercado Livre antes de publicar produtos.');
        }
    }

    private function validateProductForPublish(array $product): void
    {
        $this->validateConnection();

        if (empty($product['id']) || ($product['tipo'] ?? '') !== 'produto') {
            throw new \RuntimeException('Produto invalido para publicacao.');
        }

        $title = trim((string) ($product['nome'] ?? ''));
        if ($title === '') {
            throw new \RuntimeException('Produto sem titulo.');
        }

        if ((float) ($product['preco_venda'] ?? 0) <= 0) {
            throw new \RuntimeException('Produto sem preco valido.');
        }

        if ((int) ($product['quantidade'] ?? 0) <= 0) {
            throw new \RuntimeException('Produto sem estoque disponivel.');
        }

        $description = trim((string) ($product['localizacao'] ?? ''));
        if ($description === '') {
            throw new \RuntimeException('Informe uma descricao do produto antes de publicar no Mercado Livre.');
        }

        $settings = $this->settings();
        $prediction = null;
        $category = $this->categoryIdForProduct($product, $settings);
        if ($category === '') {
            throw new \RuntimeException('Informe uma categoria final do Mercado Livre no produto antes de publicar. Exemplo para mouse: MLB1714.');
        }

        if (!empty($product['imagem']) && $this->publicImageUrlForProduct($product) === '') {
            throw new \RuntimeException('A imagem do produto nao esta em uma URL publica. Ajuste APP_URL em producao.');
        }

        $this->validateRequiredAttributes($category, $product, $prediction);
    }

    private function validateRequiredAttributes(string $categoryId, array $product, ?array $prediction = null): void
    {
        $categoryId = trim($categoryId);
        if ($categoryId === '') {
            return;
        }

        try {
            $required = $this->requiredAttributeIdsForCategory($categoryId);
        } catch (\Throwable $e) {
            app_log('Falha ao consultar atributos obrigatorios Mercado Livre', [
                'category_id' => $categoryId,
                'erro' => $e->getMessage(),
            ]);
            return;
        }

        if (empty($required)) {
            return;
        }

        $provided = [];
        foreach ($this->attributesForProduct($product, $prediction) as $attribute) {
            $id = strtoupper(trim((string) ($attribute['id'] ?? '')));
            $value = trim((string) ($attribute['value_name'] ?? $attribute['value'] ?? ''));
            if ($id !== '' && $value !== '') {
                $provided[$id] = true;
            }
        }

        $missing = [];
        foreach ($required as $id => $name) {
            if (empty($provided[$id])) {
                $missing[] = $name !== '' && $name !== $id ? $id . ' (' . $name . ')' : $id;
            }
        }

        if (!empty($missing)) {
            throw new \RuntimeException('Mercado Livre: faltam dados obrigatorios para essa categoria: ' . implode(', ', $missing) . '. Salve o produto com Codigo de barras, Cor principal, Marca, Modelo e Homologacao Anatel No antes de publicar.');
        }
    }

    private function requiredAttributeIdsForCategory(string $categoryId): array
    {
        $attributes = $this->request('GET', '/categories/' . rawurlencode($categoryId) . '/attributes');
        $required = [];

        foreach ($attributes as $attribute) {
            if (!is_array($attribute)) {
                continue;
            }

            $id = strtoupper(trim((string) ($attribute['id'] ?? '')));
            if ($id === '') {
                continue;
            }

            $tags = is_array($attribute['tags'] ?? null) ? $attribute['tags'] : [];
            $isRequired = !empty($tags['required']) || !empty($tags['catalog_required']);
            if (!$isRequired) {
                continue;
            }

            $required[$id] = trim((string) ($attribute['name'] ?? ''));
        }

        return $required;
    }

    private function sendDescription(string $itemId, string $description): void
    {
        $description = $this->normalizePlainText($description, 50000);
        if ($description === '') {
            return;
        }

        try {
            $this->request('POST', '/items/' . rawurlencode($itemId) . '/description', [
                'plain_text' => $description,
            ]);
        } catch (\Throwable $e) {
            try {
                $this->request('PUT', '/items/' . rawurlencode($itemId) . '/description', [
                    'plain_text' => $description,
                ]);
            } catch (\Throwable $secondError) {
                app_log('Falha ao enviar descricao Mercado Livre', ['item_id' => $itemId, 'erro' => $secondError->getMessage()]);
            }
        }
    }

    private function syncAuthenticatedUser(): void
    {
        $user = $this->request('GET', '/users/me');
        $this->config->update([
            'mercado_livre_user_id' => (string) ($user['id'] ?? ''),
            'mercado_livre_nickname' => (string) ($user['nickname'] ?? ''),
        ]);
    }

    private function accessToken(): string
    {
        $settings = $this->settings();
        $accessToken = trim((string) ($settings['mercado_livre_access_token'] ?? ''));
        $refreshToken = trim((string) ($settings['mercado_livre_refresh_token'] ?? ''));
        $expiresAt = strtotime((string) ($settings['mercado_livre_token_expires_at'] ?? '')) ?: 0;

        if ($accessToken !== '' && $expiresAt > time() + 60) {
            return $accessToken;
        }

        if ($refreshToken === '') {
            throw new \RuntimeException('Conecte sua conta do Mercado Livre antes de sincronizar produtos.');
        }

        $settings = $this->settings();
        $token = $this->postForm('/oauth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => trim((string) ($settings['mercado_livre_client_id'] ?? '')),
            'client_secret' => trim((string) ($settings['mercado_livre_client_secret'] ?? '')),
            'refresh_token' => $refreshToken,
        ]);
        $this->storeToken($token);

        return (string) ($token['access_token'] ?? '');
    }

    private function storeToken(array $token): void
    {
        $expiresIn = max(1, (int) ($token['expires_in'] ?? 0));
        $this->config->update([
            'mercado_livre_access_token' => (string) ($token['access_token'] ?? ''),
            'mercado_livre_refresh_token' => (string) ($token['refresh_token'] ?? ''),
            'mercado_livre_token_expires_at' => date('Y-m-d H:i:s', time() + $expiresIn),
            'mercado_livre_user_id' => (string) ($token['user_id'] ?? ''),
        ]);
    }

    private function storeProductSuccess(int $productId, array $response): void
    {
        (new EstoqueModel())->updateMercadoLivreData($productId, [
            'mercado_livre_item_id' => $response['id'] ?? null,
            'mercado_livre_permalink' => $response['permalink'] ?? null,
            'mercado_livre_status' => $response['status'] ?? null,
            'mercado_livre_category_id' => $response['category_id'] ?? null,
            'mercado_livre_listing_type_id' => $response['listing_type_id'] ?? null,
            'mercado_livre_condition' => $response['condition'] ?? null,
            'mercado_livre_last_sync_at' => date('Y-m-d H:i:s'),
            'mercado_livre_last_error' => null,
        ]);
    }

    private function storeProductError(int $productId, string $message): void
    {
        (new EstoqueModel())->updateMercadoLivreData($productId, [
            'mercado_livre_last_error' => $this->limitText($message, 1000),
        ]);
    }

    private function request(string $method, string $path, ?array $payload = null): array
    {
        return $this->http($method, self::API_BASE . $path, [
            'Authorization: Bearer ' . $this->accessToken(),
            'Accept: application/json',
            'Content-Type: application/json',
        ], $payload !== null ? $this->jsonBody($payload) : null);
    }

    private function postForm(string $path, array $fields): array
    {
        return $this->http('POST', self::API_BASE . $path, [
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
        ], http_build_query($fields));
    }

    private function http(string $method, string $url, array $headers, ?string $body): array
    {
        if (!function_exists('curl_init')) {
            throw new \RuntimeException('A extensao cURL do PHP precisa estar habilitada para usar o Mercado Livre.');
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $raw = curl_exec($ch);
        $curlError = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($raw === false) {
            throw new \RuntimeException('Falha de comunicacao com Mercado Livre: ' . $curlError);
        }

        $data = json_decode((string) $raw, true);
        if (!is_array($data)) {
            $data = ['raw' => (string) $raw];
        }

        if ($status < 200 || $status >= 300) {
            $message = $data['message'] ?? $data['error'] ?? $data['raw'] ?? 'Erro desconhecido';
            if (!empty($data['cause']) && is_array($data['cause'])) {
                $message .= ' - ' . $this->safeJson($data['cause']);
                if ($this->hasApiCauseCode($data['cause'], 'shipping.lost_me1_by_user')) {
                    $message .= ' | Use Mercado Envios 2 (me2) ou revise as preferencias de envio da conta no Mercado Livre.';
                }
                if ($this->hasApiCauseCode($data['cause'], 'item.attribute.missing_catalog_required')) {
                    $missing = $this->missingAttributeReferences($data['cause']);
                    $message .= ' | Salve o produto com Codigo de barras, Cor principal, Marca, Modelo e Homologacao Anatel No antes de publicar.';
                    if (!empty($missing)) {
                        $message .= ' Faltando: ' . implode(', ', $missing) . '.';
                    }
                }
            }
            throw new \RuntimeException('Mercado Livre HTTP ' . $status . ': ' . $message);
        }

        return $data;
    }

    private function hasApiCauseCode(array $causes, string $code): bool
    {
        foreach ($causes as $cause) {
            if (is_array($cause) && ($cause['code'] ?? '') === $code) {
                return true;
            }
        }

        return false;
    }

    private function missingAttributeReferences(array $causes): array
    {
        $missing = [];
        foreach ($causes as $cause) {
            if (!is_array($cause) || ($cause['code'] ?? '') !== 'item.attribute.missing_catalog_required') {
                continue;
            }

            foreach (($cause['references'] ?? []) as $reference) {
                $reference = (string) $reference;
                if (preg_match('/attributes(?:\\[|\\.|:)([A-Z0-9_]+)/i', $reference, $match)) {
                    $missing[] = strtoupper($match[1]);
                    continue;
                }
                if (preg_match('/\\b([A-Z0-9_]{2,})\\b/', $reference, $match)) {
                    $missing[] = strtoupper($match[1]);
                }
            }
        }

        return array_values(array_unique($missing));
    }

    private function log(?int $productId, ?string $itemId, string $action, string $status, string $message, array $payload = []): void
    {
        $stmt = $this->db->prepare("INSERT INTO mercado_livre_logs (produto_id, item_id, acao, status, mensagem, payload)
            VALUES (:produto_id, :item_id, :acao, :status, :mensagem, :payload)");
        $stmt->execute([
            ':produto_id' => $productId,
            ':item_id' => $itemId,
            ':acao' => $action,
            ':status' => $status,
            ':mensagem' => $message,
            ':payload' => $this->safeJson($payload),
        ]);
    }

    private function limitText(string $text, int $limit): string
    {
        $text = $this->normalizePlainText($text);
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $limit, 'UTF-8');
        }

        return substr($text, 0, $limit);
    }

    private function jsonBody(array $payload): string
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false) {
            throw new \RuntimeException('Nao foi possivel preparar os dados para o Mercado Livre: ' . json_last_error_msg());
        }

        return $json;
    }

    private function safeJson(array $payload): string
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        return $json !== false ? $json : '[]';
    }

    private function normalizePlainText(string $text, int $limit = 0): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        if (function_exists('mb_check_encoding') && !mb_check_encoding($text, 'UTF-8')) {
            $converted = @mb_convert_encoding($text, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
            if (is_string($converted)) {
                $text = $converted;
            }
        } elseif (!preg_match('//u', $text)) {
            $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
            if (is_string($converted)) {
                $text = $converted;
            }
        }

        $text = preg_replace('/[^\P{C}\n\t]+/u', '', $text) ?? $text;
        $text = trim($text);
        if ($limit > 0 && function_exists('mb_substr')) {
            return mb_substr($text, 0, $limit, 'UTF-8');
        }

        return $limit > 0 ? substr($text, 0, $limit) : $text;
    }
}
