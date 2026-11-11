<?php

namespace App\Services\Contracts;

interface DashboardServiceInterface
{
    /**
     * Get aggregate statistics and chart data for the given period.
     *
     * @param string|int|null $year
     * @param string|int|null $month
     * @return array
     */
    public function getDashboardData($year = 2026, $month = 'all'): array;
}
