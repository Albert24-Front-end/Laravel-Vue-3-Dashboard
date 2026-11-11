<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\FilterTransactionRequest;
use App\Http\Requests\Transaction\ImportCsvRequest;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Services\Contracts\CsvServiceInterface;
use App\Services\Contracts\TransactionServiceInterface;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionServiceInterface $transactionService,
        private readonly CsvServiceInterface $csvService
    ) {}

    public function index(FilterTransactionRequest $request): JsonResponse
    {
        $paginator = $this->transactionService->getFilteredTransactions(
            $request->validated(),
            (int) $request->input('per_page', 50)
        );

        return response()->json([
            'data' => TransactionResource::collection($paginator->items()),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
        ]);
    }

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $transaction = $this->transactionService->createTransaction($request->validated());

        return response()->json([
            'message' => 'Операция успешно добавлена',
            'transaction' => new TransactionResource($transaction),
        ], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->transactionService->deleteTransaction($id);

        return response()->json([
            'message' => 'Операция удалена',
        ]);
    }

    public function exportCsv(FilterTransactionRequest $request): StreamedResponse
    {
        return $this->csvService->export($request->validated());
    }

    public function csvTemplate(): StreamedResponse
    {
        return $this->csvService->getTemplate();
    }

    public function importCsv(ImportCsvRequest $request): JsonResponse
    {
        $result = $this->csvService->import($request->file('file'));

        return response()->json([
            'message' => "Успешно импортировано операций: {$result['imported_count']}",
            'imported_count' => $result['imported_count'],
            'errors' => $result['errors'],
        ]);
    }
}
