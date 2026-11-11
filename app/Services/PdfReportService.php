<?php

namespace App\Services;

use App\Models\Transaction;
use App\Services\Contracts\CurrencyServiceInterface;
use App\Services\Contracts\PdfReportServiceInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class PdfReportService implements PdfReportServiceInterface
{
    public function __construct(
        private readonly CurrencyServiceInterface $currencyService
    ) {}

    public function generateReport(string|int|null $year, string|int|null $month, string $currencyCode = 'RUB'): Response
    {
        $year = $year ?: 2026;
        $month = $month ?: 'all';

        $query = Transaction::query();
        if ($year && $year !== 'all') {
            $query->whereYear('date', $year);
        }
        if ($month && $month !== 'all') {
            $query->whereMonth('date', $month);
        }

        $transactions = (clone $query)->orderBy('date', 'desc')->get();

        $revenue = (float) (clone $query)->where('type', Transaction::TYPE_REVENUE)->sum('amount');
        $expenses = (float) (clone $query)->where('type', Transaction::TYPE_EXPENSE)->sum('amount');
        $netProfit = $revenue - $expenses;
        $margin = $revenue > 0 ? round(($netProfit / $revenue) * 100, 2) : 0;

        $expenseCategories = (clone $query)->where('type', Transaction::TYPE_EXPENSE)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        $currencies = $this->currencyService->getCurrencies();
        $currency = $currencies[$currencyCode] ?? $currencies['RUB'];
        $rate = $currency['rate'] ?: 1.0;
        $symbol = $currency['symbol'] ?? '₽';

        $monthNames = [
            1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
            5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
            9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь',
        ];
        $periodLabel = ($month && $month !== 'all') ? ($monthNames[(int)$month] . ' ' . $year) : ("Весь " . $year . " год");

        $data = [
            'periodLabel' => $periodLabel,
            'currency' => $currencyCode,
            'currencySymbol' => $symbol,
            'rate' => $rate,
            'revenue' => $revenue / $rate,
            'expenses' => $expenses / $rate,
            'netProfit' => $netProfit / $rate,
            'margin' => $margin,
            'isProfitable' => $netProfit >= 0,
            'expenseCategories' => array_map(fn($v) => $v / $rate, $expenseCategories),
            'transactions' => $transactions->take(50),
            'generatedAt' => Carbon::now()->format('d.m.Y H:i'),
        ];

        $pdf = Pdf::loadView('reports.financial_pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOption(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        return $pdf->download("financial_report_{$year}_{$month}.pdf");
    }
}
