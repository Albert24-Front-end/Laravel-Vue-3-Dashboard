<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'category',
        'amount',
        'date',
        'comment',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public const TYPE_REVENUE = 'revenue';
    public const TYPE_EXPENSE = 'expense';

    public const EXPENSE_CATEGORIES = [
        'ФОТ',
        'Аренда офиса или его обслуживание',
        'Маркетинг и реклама',
        'Закупки и услуги подрядчиков',
        'Уплата налогов',
    ];

    public const REVENUE_CATEGORIES = [
        'Основная деятельность (продажи)',
        'Оказание консалтинговых услуг',
        'Лицензионные платежи и подписки',
        'Прочие доходы',
    ];
}
