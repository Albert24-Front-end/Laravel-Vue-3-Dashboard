<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Don't duplicate if transactions already exist
        if (Transaction::count() > 0) {
            return;
        }

        $year = 2026;
        $months = range(1, 12);

        $revenueTemplates = [
            ['cat' => 'Основная деятельность (продажи)', 'base' => 2400000, 'comments' => ['Оплата по договору поставки ПО №412', 'Ежемесячные лицензионные отчисления Enterprise', 'Поставка серверного комплекса для ООО "Альфа"', 'Абонентская плата по подписке за месяц']],
            ['cat' => 'Оказание консалтинговых услуг', 'base' => 1100000, 'comments' => ['Консалтинг по автоматизации бизнес-процессов', 'Разработка финансовой модели для инвесторов', 'Аудит информационной безопасности клиента']],
            ['cat' => 'Лицензионные платежи и подписки', 'base' => 850000, 'comments' => ['Регулярный SaaS-платеж от пула B2B-клиентов', 'Продление годовой подписки Cloud-сервиса', 'Оплата API-доступа к аналитическому хабу']],
            ['cat' => 'Прочие доходы', 'base' => 250000, 'comments' => ['Проценты по депозитному счету', 'Возврат обеспечения по тендеру', 'Бонус от технологического партнера']],
        ];

        $expenseTemplates = [
            [
                'cat' => 'ФОТ',
                'base' => 1950000,
                'variance' => 150000,
                'comments' => [
                    'Аванс за первую половину месяца сотрудникам IT-отдела',
                    'Основная выплата заработной платы за месяц',
                    'Премиальный фонд по итогам закрытия спринта',
                ]
            ],
            [
                'cat' => 'Аренда офиса или его обслуживание',
                'base' => 340000,
                'variance' => 20000,
                'comments' => [
                    'Арендная плата за офис БЦ "Технопарк", 4 этаж',
                    'Клининг и коммунальные услуги офиса',
                    'Охрана, паркинг и эксплуатационный сбор',
                ]
            ],
            [
                'cat' => 'Маркетинг и реклама',
                'base' => 380000,
                'variance' => 80000,
                'comments' => [
                    'Пополнение рекламного кабинета Яндекс.Директ',
                    'Таргетированная реклама и промо в Telegram',
                    'Участие в профильной финтех-конференции',
                    'Оплата SEO-оптимизации и копирайтинга',
                ]
            ],
            [
                'cat' => 'Закупки и услуги подрядчиков',
                'base' => 450000,
                'variance' => 90000,
                'comments' => [
                    'Оплата серверов Yandex Cloud и CDN',
                    'Услуги внешнего UI/UX дизайн-агентства',
                    'Закупка офисной техники и периферии',
                    'Подписки на инструменты разработки (JetBrains, GitHub)',
                ]
            ],
            [
                'cat' => 'Уплата налогов',
                'base' => 310000,
                'variance' => 60000,
                'comments' => [
                    'Страховые взносы за сотрудников в СФР',
                    'НДФЛ с заработной платы',
                    'Авансовый платеж по налогу на прибыль / УСН',
                ]
            ],
        ];

        foreach ($months as $month) {
            // Seasonal multiplier (higher revenue towards end of quarters and Q4)
            $seasonality = 1.0 + ($month % 3 == 0 ? 0.15 : 0.0) + ($month >= 9 ? 0.12 : 0.0);

            // Generate Revenues
            foreach ($revenueTemplates as $rev) {
                $days = [5, 12, 19, 26];
                $day = $days[array_rand($days)];
                $randomMultiplier = (rand(85, 115) / 100.0) * $seasonality;
                $amount = round(($rev['base'] * $randomMultiplier) / 1000) * 1000;
                $comment = $rev['comments'][array_rand($rev['comments'])];

                Transaction::create([
                    'type' => Transaction::TYPE_REVENUE,
                    'category' => $rev['cat'],
                    'amount' => $amount,
                    'date' => Carbon::create($year, $month, $day)->format('Y-m-d'),
                    'comment' => $comment,
                ]);
            }

            // Generate Expenses for each category
            foreach ($expenseTemplates as $exp) {
                $days = [3, 10, 15, 20, 25];
                $numOps = rand(1, 2);
                $catTotal = $exp['base'] + (rand(-10, 15) / 100.0) * $exp['variance'];

                for ($i = 0; $i < $numOps; $i++) {
                    $day = $days[($i * 2 + $month) % count($days)];
                    $amount = round(($catTotal / $numOps) / 1000) * 1000;
                    $comment = $exp['comments'][array_rand($exp['comments'])];

                    Transaction::create([
                        'type' => Transaction::TYPE_EXPENSE,
                        'category' => $exp['cat'],
                        'amount' => $amount,
                        'date' => Carbon::create($year, $month, $day)->format('Y-m-d'),
                        'comment' => $comment,
                    ]);
                }
            }
        }
    }
}
