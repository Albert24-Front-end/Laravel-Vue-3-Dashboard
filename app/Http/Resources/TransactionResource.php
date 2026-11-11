<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'category' => $this->category,
            'amount' => (float) $this->amount,
            'date' => Carbon::parse($this->date)->format('Y-m-d'),
            'comment' => $this->comment,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
