<?php

namespace App\Providers;

use App\Services\Contracts\CsvServiceInterface;
use App\Services\Contracts\CurrencyServiceInterface;
use App\Services\Contracts\DashboardServiceInterface;
use App\Services\Contracts\PdfReportServiceInterface;
use App\Services\Contracts\TransactionServiceInterface;
use App\Services\CsvService;
use App\Services\CurrencyService;
use App\Services\DashboardService;
use App\Services\PdfReportService;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CurrencyServiceInterface::class, CurrencyService::class);
        $this->app->singleton(DashboardServiceInterface::class, DashboardService::class);
        $this->app->singleton(TransactionServiceInterface::class, TransactionService::class);
        $this->app->singleton(CsvServiceInterface::class, CsvService::class);
        $this->app->singleton(PdfReportServiceInterface::class, PdfReportService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enforce HTTPS in production or behind Vercel/proxies
        if ($this->app->environment('production') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || isset($_SERVER['HTTP_X_VERCEL_ID'])) {
            URL::forceScheme('https');
        }
        if (Config::get('database.default') === 'pgsql') {
            try {
                // Quick ping to check if PostgreSQL port is accepting connections
                $host = Config::get('database.connections.pgsql.host', '127.0.0.1');
                $port = (int) Config::get('database.connections.pgsql.port', 5432);
                
                $fp = @fsockopen($host, $port, $errno, $errstr, 0.4);
                if ($fp) {
                    fclose($fp);
                    // Port is open, test PDO
                    DB::connection('pgsql')->getPdo();
                } else {
                    // Port not open (Docker container not started yet), fallback to sqlite
                    Config::set('database.default', 'sqlite');
                }
            } catch (\Throwable $e) {
                Log::info('PostgreSQL container not reached yet, using sqlite fallback: ' . $e->getMessage());
                Config::set('database.default', 'sqlite');
            }
        }

        if (Config::get('database.default') === 'sqlite') {
            try {
                if (!\Illuminate\Support\Facades\Schema::hasTable('personal_access_tokens') || !\Illuminate\Support\Facades\Schema::hasTable('transactions')) {
                    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
                }
            } catch (\Throwable $e) {
                Log::error('Auto-migration on sqlite fallback: ' . $e->getMessage());
            }
        }
    }
}
