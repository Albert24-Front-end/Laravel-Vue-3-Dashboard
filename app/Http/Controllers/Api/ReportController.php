<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\PdfReportServiceInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct(
        private readonly PdfReportServiceInterface $pdfReportService
    ) {}

    public function pdf(Request $request): Response
    {
        $year = $request->input('year', 2026);
        $month = $request->input('month', 'all');
        $currency = $request->input('currency', 'RUB');

        return $this->pdfReportService->generateReport($year, $month, $currency);
    }
}
