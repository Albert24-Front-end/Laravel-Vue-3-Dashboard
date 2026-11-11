<?php

namespace App\Services\Contracts;

use Symfony\Component\HttpFoundation\Response;

interface PdfReportServiceInterface
{
    /**
     * Generate and download PDF report.
     */
    public function generateReport(string|int|null $year, string|int|null $month, string $currencyCode): Response;
}
