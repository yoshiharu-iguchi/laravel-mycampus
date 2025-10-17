<?php

namespace App\Providers;
use Illuminate\Support\Facades\View;
use App\Models\TransportRequest;
use App\Enums\TransportRequestStatus;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ページネーションをBootstrapで描画
        Paginator::useBootstrap();
        // Adminレイアウトに「承認待ち件数」を常時共有
        View::composer('*',function($view){
            static $cached = false;
            static $value = null;

            if ($cached === false) {
                try {
                    $value=\App\Models\TransportRequest::where(
                        'status',
                        \App\Enums\TransportRequestStatus::Pending
                    )->count();
                } catch (\Throwable $e) {
                    $value = null;
                }
                $cached = true;
            }

            $view->with('pendingCount',$value);
        });
        if (App::environment(['production'])){
            URL::forceScheme('https');
        }
    }
}
