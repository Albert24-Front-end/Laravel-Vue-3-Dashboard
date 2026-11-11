<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\DashboardServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardServiceInterface $dashboardService
    ) {}

    public function stats(Request $request): JsonResponse
    {
        $year = $request->input('year', 2026);
        $month = $request->input('month', 'all');

        $data = $this->dashboardService->getDashboardData($year, $month);

        return response()->json($data);
    }
}
