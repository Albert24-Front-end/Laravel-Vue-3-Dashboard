<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Финансовый отчет компании</title>
    <style>
        @page {
            margin: 20mm 15mm 20mm 15mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.4;
            background: #ffffff;
        }
        .header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
        }
        .header-subtitle {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }
        .header-meta {
            text-align: right;
            font-size: 10px;
            color: #64748b;
        }
        .kpi-grid {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: separate;
            border-spacing: 8px 0;
        }
        .kpi-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
        }
        .kpi-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 4px;
            font-weight: bold;
        }
        .kpi-value {
            font-size: 15px;
            font-weight: bold;
        }
        .text-green {
            color: #16a34a;
        }
        .text-red {
            color: #dc2626;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            border-left: 3px solid #2563eb;
            padding-left: 8px;
            margin: 20px 0 10px 0;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table.data-table th {
            background: #f1f5f9;
            color: #475569;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
        }
        table.data-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
        }
        table.data-table tr:nth-child(even) {
            background: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-green {
            background: #dcfce7;
            color: #166534;
        }
        .badge-red {
            background: #fee2e2;
            color: #991b1b;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    <div class="header-title">КОРПОРАТИВНЫЙ ФИНАНСОВЫЙ ОТЧЕТ</div>
                    <div class="header-subtitle">Период: <strong>{{ $periodLabel }}</strong> | Валюта: <strong>{{ $currency }} ({{ $currencySymbol }})</strong></div>
                </td>
                <td class="header-meta">
                    <div>Сформировано: {{ $generatedAt }}</div>
                    <div>Курс к базовому RUB: 1 {{ $currency }} = {{ number_format($rate, 2, '.', ' ') }} ₽</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- 4 Карточки KPI -->
    <table class="kpi-grid">
        <tr>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Выручка</div>
                <div class="kpi-value text-green">
                    {{ number_format($revenue, 2, '.', ' ') }} {{ $currencySymbol }}
                </div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Расходы</div>
                <div class="kpi-value text-red">
                    {{ number_format($expenses, 2, '.', ' ') }} {{ $currencySymbol }}
                </div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Чистая прибыль</div>
                <div class="kpi-value {{ $isProfitable ? 'text-green' : 'text-red' }}">
                    {{ number_format($netProfit, 2, '.', ' ') }} {{ $currencySymbol }}
                </div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-label">Рентабельность</div>
                <div class="kpi-value {{ $margin >= 0 ? 'text-green' : 'text-red' }}">
                    {{ number_format($margin, 2, '.', ' ') }}%
                </div>
            </td>
        </tr>
    </table>

    <!-- Структура расходов -->
    <div class="section-title">СТРУКТУРА РАСХОДОВ ПО СТАТЬЯМ</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Статья расходов</th>
                <th class="text-right">Сумма ({{ $currencySymbol }})</th>
                <th class="text-right">Доля в расходах</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenseCategories as $category => $catSum)
                @php
                    $share = $expenses > 0 ? round(($catSum / $expenses) * 100, 1) : 0;
                @endphp
                <tr>
                    <td><strong>{{ $category }}</strong></td>
                    <td class="text-right">{{ number_format($catSum, 2, '.', ' ') }} {{ $currencySymbol }}</td>
                    <td class="text-right">{{ $share }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #94a3b8;">Нет данных о расходах за выбранный период</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Реестр операций -->
    <div class="section-title" style="margin-top: 25px;">РЕЕСТР ОПЕРАЦИЙ (ПОСЛЕДНИЕ ЗАПИСИ)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 70px;">Дата</th>
                <th style="width: 65px;">Тип</th>
                <th style="width: 140px;">Категория</th>
                <th>Комментарий</th>
                <th class="text-right" style="width: 100px;">Сумма ({{ $currencySymbol }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $tx)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($tx->date)->format('d.m.Y') }}</td>
                    <td>
                        @if($tx->type === 'revenue')
                            <span class="badge badge-green">Выручка</span>
                        @else
                            <span class="badge badge-red">Расход</span>
                        @endif
                    </td>
                    <td>{{ $tx->category }}</td>
                    <td>{{ $tx->comment ?? '—' }}</td>
                    <td class="text-right">
                        <strong>{{ number_format($tx->amount / $rate, 2, '.', ' ') }}</strong>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #94a3b8;">Операции не найдены</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Корпоративный финансовый дашборд компании. Документ сформирован автоматически в системе учета.
    </div>

</body>
</html>
