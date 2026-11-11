<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:revenue,expense',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0.01|max:999999999999',
            'date' => 'required|date',
            'comment' => 'nullable|string|max:500',
        ];
    }
}
