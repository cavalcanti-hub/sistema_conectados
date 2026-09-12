<?php

namespace App\Support;

/**
 * Normaliza e classifica formas de pagamento para cálculo de taxas e integração PDV/Point.
 */
final class PaymentFormNormalizer
{
    /**
     * Converte a forma de pagamento para minúsculas e translitera acentos para ASCII.
     */
    public static function normalize(string $formaPagamento): string
    {
        $forma = function_exists('mb_strtolower') ? mb_strtolower($formaPagamento, 'UTF-8') : strtolower($formaPagamento);
        if (function_exists('iconv')) {
            $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $forma);
            if (is_string($ascii) && $ascii !== '') {
                $forma = strtolower(preg_replace('/[\'~^`"]/', '', $ascii) ?? $ascii);
            }
        }

        return str_replace(
            ['á', 'à', 'â', 'ã', 'ä', 'é', 'è', 'ê', 'ë', 'í', 'ì', 'î', 'ï', 'ó', 'ò', 'ô', 'õ', 'ö', 'ú', 'ù', 'û', 'ü', 'ç'],
            ['a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'c'],
            $forma
        );
    }

    /**
     * Verifica se a forma de pagamento corresponde a crédito.
     */
    public static function isCredit(string $formaPagamento): bool
    {
        $forma = self::normalize($formaPagamento);
        return str_contains($forma, 'credito') || (bool) preg_match('/(^|\s|-)cr($|\s|-)/', $forma);
    }

    /**
     * Verifica se a forma de pagamento corresponde a débito.
     */
    public static function isDebit(string $formaPagamento): bool
    {
        $forma = self::normalize($formaPagamento);
        return str_contains($forma, 'debito');
    }

    /**
     * Verifica se a forma de pagamento é elegível para Point / maquininha.
     */
    public static function isPointPayment(string $formaPagamento): bool
    {
        $forma = self::normalize($formaPagamento);
        return str_contains($forma, 'cartao')
            || str_contains($forma, 'credito')
            || str_contains($forma, 'debito')
            || str_contains($forma, 'qr mercado')
            || str_contains($forma, 'saldo mercado');
    }

    /**
     * Extrai a quantidade de parcelas se indicada no texto (ex.: 2x a 12x). Retorna 1 por padrão.
     */
    public static function extractInstallments(string $formaPagamento): int
    {
        $forma = self::normalize($formaPagamento);
        if (preg_match('/\b([2-9]|1[0-2])x\b/', $forma, $matches)) {
            return (int) $matches[1];
        }
        return 1;
    }
}
