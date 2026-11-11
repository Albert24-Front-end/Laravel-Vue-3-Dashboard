# Многослойная архитектура Laravel (Service Layer, FormRequests, Resources) и авторизация Sanctum

В рамках рефакторинга устранена монолитная логика контроллеров (God Controller) и внедрена слоистая корпоративная архитектура.

## Контекст и решение
1. **Слой сервисов (Service Layer)**: Выделены интерфейсы и конкретные реализации:
   - `DashboardService` / `DashboardServiceInterface` — агрегация показателей KPI и формирование датасетов графиков.
   - `TransactionService` / `TransactionServiceInterface` — фильтрация, поиск, пагинация и CRUD операций.
   - `CurrencyService` / `CurrencyServiceInterface` — взаимодействие с внешним API ЦБ РФ, кэширование и конвертация.
   - `CsvService` / `CsvServiceInterface` — парсинг, валидация импорта, потоковый экспорт с BOM и генерация шаблона.
   - `PdfReportService` / `PdfReportServiceInterface` — компиляция и рендеринг PDF-отчетов.
2. **HTTP Layer**: Контроллеры стали тонкими, делегируя бизнес-логику сервисам. Созданы строго типизированные FormRequests (`LoginRequest`, `RegisterRequest`, `StoreTransactionRequest`, `FilterTransactionRequest`, `ImportCsvRequest`) и API Resources (`UserResource`, `TransactionResource`).
3. **Безопасность и авторизация**: Интегрирован **Laravel Sanctum**. Доступ ко всем аналитическим и операционным данным защищен middleware `auth:sanctum`. Добавлены эндпоинты регистрации, логина и логаута с выдачей Bearer-токенов.
