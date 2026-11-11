<?php

namespace App\Services;

use App\Models\Transaction;
use App\Services\Contracts\TransactionServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransactionService implements TransactionServiceInterface
{
    public function getFilteredTransactions(array $filters, int $perPage = 50): LengthAwarePaginator
    {
        $query = Transaction::query();

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where('comment', 'like', "%{$search}%");
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

        return $query->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function createTransaction(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function deleteTransaction(int $id): bool
    {
        $transaction = Transaction::findOrFail($id);
        return (bool) $transaction->delete();
    }
}
