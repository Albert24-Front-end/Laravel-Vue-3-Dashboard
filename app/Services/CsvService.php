<?php

namespace App\Services;

use App\Models\Transaction;
use App\Services\Contracts\CsvServiceInterface;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvService implements CsvServiceInterface
{
    public function import(UploadedFile $file): array
    {
        $content = file_get_contents($file->getRealPath());

        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1251');
        }

        // Strip UTF-8 BOM if present
        $bom = pack('H*', 'EFBBBF');
        $content = preg_replace("/^$bom/", '', $content);

        $lines = preg_split('/\r\n|\r|\n/', trim($content));
        if (empty($lines)) {
            return ['imported_count' => 0, 'errors' => ['Файл пуст']];
        }

        $firstLine = $lines[0];
        $delimiter = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';

        $header = str_getcsv(array_shift($lines), $delimiter);
        $header = array_map(fn($h) => mb_strtolower(trim($h)), $header);

        $dateIdx = null;
        $typeIdx = null;
        $catIdx = null;
        $amountIdx = null;
        $commentIdx = null;

        foreach ($header as $idx => $colName) {
            if (in_array($colName, ['дата', 'date'])) $dateIdx = $idx;
            if (in_array($colName, ['тип', 'type'])) $typeIdx = $idx;
            if (in_array($colName, ['категория', 'статья', 'category'])) $catIdx = $idx;
            if (in_array($colName, ['сумма', 'сумма (rub)', 'amount'])) $amountIdx = $idx;
            if (in_array($colName, ['комментарий', 'описание', 'comment', 'description'])) $commentIdx = $idx;
        }

        if ($dateIdx === null && $typeIdx === null && count($header) >= 4) {
            $dateIdx = 0;
            $typeIdx = 1;
            $catIdx = 2;
            $amountIdx = 3;
            $commentIdx = 4;
        }

        $imported = 0;
        $errors = [];

        foreach ($lines as $lineNum => $line) {
            if (trim($line) === '') continue;

            $row = str_getcsv($line, $delimiter);
            if (count($row) < 4) {
                $errors[] = "Строка " . ($lineNum + 2) . ": недостаточно колонок";
                continue;
            }

            try {
                $rawDate = trim($row[$dateIdx] ?? '');
                $rawType = mb_strtolower(trim($row[$typeIdx] ?? ''));
                $category = trim($row[$catIdx] ?? 'Прочее');
                $rawAmount = trim($row[$amountIdx] ?? '0');
                $comment = $commentIdx !== null && isset($row[$commentIdx]) ? trim($row[$commentIdx]) : null;

                $date = Carbon::parse($rawDate)->format('Y-m-d');
                $type = in_array($rawType, ['выручка', 'доход', 'revenue', 'income']) ? 'revenue' : 'expense';

                $cleanAmount = str_replace([' ', "\xc2\xa0"], '', $rawAmount);
                $cleanAmount = str_replace(',', '.', $cleanAmount);
                $amount = abs((float) $cleanAmount);

                if ($amount <= 0) {
                    $errors[] = "Строка " . ($lineNum + 2) . ": некорректная сумма";
                    continue;
                }

                Transaction::create([
                    'type' => $type,
                    'category' => $category ?: 'Прочее',
                    'amount' => $amount,
                    'date' => $date,
                    'comment' => $comment,
                ]);

                $imported++;
            } catch (\Throwable $e) {
                $errors[] = "Строка " . ($lineNum + 2) . ": " . $e->getMessage();
            }
        }

        return [
            'imported_count' => $imported,
            'errors' => $errors,
        ];
    }

    public function export(array $filters): StreamedResponse
    {
        $query = Transaction::query();

        if (!empty($filters['search'])) {
            $query->where('comment', 'like', "%{$filters['search']}%");
        }
        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $query->where('category', $filters['category']);
        }
        if (!empty($filters['year']) && $filters['year'] !== 'all') {
            $query->whereYear('date', $filters['year']);
        }
        if (!empty($filters['month']) && $filters['month'] !== 'all') {
            $query->whereMonth('date', $filters['month']);
        }

        $transactions = $query->orderBy('date', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="transactions_' . date('Y-m-d_H-i') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['ID', 'Дата', 'Тип', 'Категория', 'Сумма (RUB)', 'Комментарий'], ';');

            foreach ($transactions as $tx) {
                fputcsv($handle, [
                    $tx->id,
                    Carbon::parse($tx->date)->format('Y-m-d'),
                    $tx->type === 'revenue' ? 'Выручка' : 'Расход',
                    $tx->category,
                    number_format($tx->amount, 2, '.', ''),
                    $tx->comment ?? '',
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function getTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="transactions_template.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['Дата', 'Тип', 'Категория', 'Сумма', 'Комментарий'], ';');
            fputcsv($handle, ['2026-03-15', 'Выручка', 'Основная деятельность (продажи)', '1250000.00', 'Оплата по договору №102'], ';');
            fputcsv($handle, ['2026-03-16', 'Расход', 'ФОТ', '450000.00', 'Аванс сотрудникам'], ';');
            fputcsv($handle, ['2026-03-18', 'Расход', 'Аренда офиса или его обслуживание', '120000.00', 'Аренда за март'], ';');
            fputcsv($handle, ['2026-03-20', 'Расход', 'Маркетинг и реклама', '85000.00', 'Рекламная кампания'], ';');
            fputcsv($handle, ['2026-03-22', 'Расход', 'Закупки и услуги подрядчиков', '95000.00', 'Хостинг'], ';');
            fputcsv($handle, ['2026-03-25', 'Расход', 'Уплата налогов', '140000.00', 'УСН за квартал'], ';');
            fclose($handle);
        }, 200, $headers);
    }
}
