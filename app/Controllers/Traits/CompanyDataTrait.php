<?php

namespace App\Controllers\Traits;

/**
 * Monta os dados da empresa a partir das configurações globais.
 *
 * Utilizado por controllers que precisam renderizar documentos de
 * impressão ou exibir informações da empresa (OS, Dashboard, PDV).
 */
trait CompanyDataTrait
{
    private function buildCompanyData(): array
    {
        $settings = (new \App\Models\ConfigModel())->getAll();

        return [
            'name'       => trim((string) ($settings['nome_empresa'] ?? 'Conectados')),
            'phone'      => trim((string) ($settings['whatsapp'] ?? '')),
            'address'    => trim((string) ($settings['endereco'] ?? '')),
            'email'      => trim((string) ($settings['email_negocio'] ?? '')),
            'website'    => trim((string) ($settings['website'] ?? '')),
            'logo'       => asset_url('assets/img/logo.png'),
            'logo_print' => asset_url('assets/img/logo-print.png?v=20260702-banner'),
        ];
    }
}
