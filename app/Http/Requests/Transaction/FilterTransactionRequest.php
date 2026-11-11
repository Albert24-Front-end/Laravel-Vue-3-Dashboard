<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class FilterTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:100',
            'type' => 'nullable|string|in:all,revenue,expense',
            'category' => 'nullable|string|max:100',
            'year' => 'nullable',
            'month' => 'nullable',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:200',
        ];
    }
}
