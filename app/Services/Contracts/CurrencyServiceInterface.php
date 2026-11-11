<?php

namespace App\Services\Contracts;

interface CurrencyServiceInterface
{
    /**
     * Get currency rates dictionary relative to RUB.
     *
     * @return array<string, array{name: string, symbol: string, rate: float}>
     */
    public function getCurrencies(): array;

    /**
     * Convert amount from base RUB to target currency.
     */
    public function convertFromRub(float $amount, string $currencyCode): float;

    /**
     * Convert amount from given currency to base RUB.
     */
    public function convertToRub(float $amount, string $currencyCode): float;
}
