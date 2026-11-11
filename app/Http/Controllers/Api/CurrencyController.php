<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\CurrencyServiceInterface;
use Illuminate\Http\JsonResponse;

class CurrencyController extends Controller
{
    public function __construct(
        private readonly CurrencyServiceInterface $currencyService
    ) {}

    public function index(): JsonResponse
    {
        return response()->json($this->currencyService->getCurrencies());
    }
}
