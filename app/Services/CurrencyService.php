<?php

namespace App\Services;

use App\Services\Contracts\CurrencyServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyService implements CurrencyServiceInterface
{
    private const CBR_API_URL = 'https://www.cbr-xml-daily.ru/daily_json.js';
    private const CACHE_TTL = 43200; // 12 hours

    public function getCurrencies(): array
    {
        return Cache::remember('currency_rates_cbr', self::CACHE_TTL, function () {
            $defaultRates = [
                'RUB' => ['name' => 'Российский рубль', 'symbol' => '₽', 'rate' => 1.0],
                'USD' => ['name' => 'Доллар США', 'symbol' => '$', 'rate' => 91.50],
                'EUR' => ['name' => 'Евро', 'symbol' => '€', 'rate' => 98.80],
                'CNY' => ['name' => 'Китайский юань', 'symbol' => '¥', 'rate' => 12.60],
                'KZT' => ['name' => 'Казахстанский тенге', 'symbol' => '₸', 'rate' => 0.185],
            ];

            try {
                $response = Http::timeout(3)->get(self::CBR_API_URL);
                if ($response->successful()) {
                    $valute = $response->json('Valute') ?? [];

                    if (isset($valute['USD']['Value'])) {
                        $defaultRates['USD']['rate'] = (float) $valute['USD']['Value'];
                    }
                    if (isset($valute['EUR']['Value'])) {
                        $defaultRates['EUR']['rate'] = (float) $valute['EUR']['Value'];
                    }
                    if (isset($valute['CNY']['Value'])) {
                        $defaultRates['CNY']['rate'] = (float) $valute['CNY']['Value'];
                    }
                    if (isset($valute['KZT']['Value'], $valute['KZT']['Nominal'])) {
                        $defaultRates['KZT']['rate'] = (float) ($valute['KZT']['Value'] / $valute['KZT']['Nominal']);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('CBR API error: ' . $e->getMessage());
            }

            return $defaultRates;
        });
    }

    public function convertFromRub(float $amount, string $currencyCode): float
    {
        $currencies = $this->getCurrencies();
        $rate = $currencies[$currencyCode]['rate'] ?? 1.0;
        return $rate > 0 ? ($amount / $rate) : $amount;
    }

    public function convertToRub(float $amount, string $currencyCode): float
    {
        $currencies = $this->getCurrencies();
        $rate = $currencies[$currencyCode]['rate'] ?? 1.0;
        return $amount * $rate;
    }
}
