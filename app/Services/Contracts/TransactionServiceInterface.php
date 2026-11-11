<?php

namespace App\Services\Contracts;

use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransactionServiceInterface
{
    /**
     * Get paginated and filtered transactions.
     */
    public function getFilteredTransactions(array $filters, int $perPage = 50): LengthAwarePaginator;

    /**
     * Create a new transaction.
     */
    public function createTransaction(array $data): Transaction;

    /**
     * Delete an existing transaction.
     */
    public function deleteTransaction(int $id): bool;
}
