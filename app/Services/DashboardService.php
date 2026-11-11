<?php

namespace App\Services;

use App\Models\Transaction;
use App\Services\Contracts\DashboardServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService implements DashboardServiceInterface
{
    private const MONTH_NAMES = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];

    public function getDashboardData($year = 2026, $month = 'all'): array
    {
        $query = Transaction::query();

        if ($year && $year !== 'all') {
            $query->whereYear('date', $year);
        }

        if ($month && $month !== 'all') {
            $query->whereMonth('date', $month);
        }

        $revenue = (float) (clone $query)->where('type', Transaction::TYPE_REVENUE)->sum('amount');
        $expenses = (float) (clone $query)->where('type', Transaction::TYPE_EXPENSE)->sum('amount');
        $netProfit = $revenue - $expenses;
        $margin = $revenue > 0 ? round(($netProfit / $revenue) * 100, 2) : 0;

        // 1. Monthly comparison bar chart
        $barChartYear = ($year && $year !== 'all') ? $year : Carbon::now()->year;
        $monthlyRevenue = array_fill(1, 12, 0);
        $monthlyExpenses = array_fill(1, 12, 0);

        $yearTransactions = Transaction::whereYear('date', $barChartYear)->get();
        foreach ($yearTransactions as $tx) {
            $m = (int) Carbon::parse($tx->date)->format('n');
            if ($tx->type === Transaction::TYPE_REVENUE) {
                $monthlyRevenue[$m] += (float) $tx->amount;
            } else {
                $monthlyExpenses[$m] += (float) $tx->amount;
            }
        }

        $barChart = [
            'labels' => self::MONTH_NAMES,
            'datasets' => [
                [
                    'label' => 'Выручка',
                    'backgroundColor' => '#10B981',
                    'data' => array_values($monthlyRevenue),
                ],
                [
                    'label' => 'Расходы',
                    'backgroundColor' => '#EF4444',
                    'data' => array_values($monthlyExpenses),
                ],
            ],
        ];

        // 2. Expense structure doughnut chart
        $expensesGrouped = (clone $query)->where('type', Transaction::TYPE_EXPENSE)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        $expenseCategorySums = [];
        foreach (Transaction::EXPENSE_CATEGORIES as $cat) {
            $expenseCategorySums[$cat] = (float) ($expensesGrouped[$cat] ?? 0);
            unset($expensesGrouped[$cat]);
        }
        foreach ($expensesGrouped as $cat => $sum) {
            $expenseCategorySums[$cat] = (float) $sum;
        }

        $doughnutLabels = array_keys($expenseCategorySums);
        $doughnutData = array_values($expenseCategorySums);
        $totalExp = array_sum($doughnutData);

        $doughnutPercentages = [];
        foreach ($doughnutData as $val) {
            $doughnutPercentages[] = $totalExp > 0 ? round(($val / $totalExp) * 100, 1) : 0;
        }

        $doughnutChart = [
            'labels' => $doughnutLabels,
            'data' => $doughnutData,
            'percentages' => $doughnutPercentages,
            'colors' => [
                '#3B82F6', // ФОТ
                '#8B5CF6', // Аренда
                '#EC4899', // Маркетинг
                '#F59E0B', // Закупки
                '#64748B', // Налоги
                '#14B8A6',
                '#6366F1',
            ],
        ];

        return [
            'database_driver' => DB::connection()->getDriverName(),
            'kpi' => [
                'revenue' => $revenue,
                'expenses' => $expenses,
                'net_profit' => $netProfit,
                'margin' => $margin,
                'is_profitable' => $netProfit >= 0,
            ],
            'bar_chart' => $barChart,
            'doughnut_chart' => $doughnutChart,
            'period' => [
                'year' => $year,
                'month' => $month,
            ],
        ];
    }
}
