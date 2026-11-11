<?php

namespace App\Services\Contracts;

use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface CsvServiceInterface
{
    /**
     * Import transactions from uploaded CSV file.
     *
     * @return array{imported_count: int, errors: array<string>}
     */
    public function import(UploadedFile $file): array;

    /**
     * Stream CSV export with UTF-8 BOM.
     */
    public function export(array $filters): StreamedResponse;

    /**
     * Generate template CSV file.
     */
    public function getTemplate(): StreamedResponse;
}
