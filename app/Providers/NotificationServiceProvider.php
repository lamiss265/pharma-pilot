<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\NotificationService;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });
        
        $this->app->alias(NotificationService::class, 'notification');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Send notification when a sale is created
        Sale::created(function ($sale) {
            app(NotificationService::class)->notifySale($sale);
        });
        
        // Send notification when a product stock goes below reorder point
        Product::updated(function ($product) {
            if ($product->isDirty('quantity') && $product->quantity <= $product->reorder_point) {
                app(NotificationService::class)->notifyLowStock($product);
            }
        });
        
        // Send notification when a product is near expiry
        Product::updated(function ($product) {
            if ($product->isDirty('expiry_date') && $product->isNearExpiry(30)) {
                $days = $product->getRemainingShelfLife();
                app(NotificationService::class)->notifyExpiringProduct($product, $days);
            }
        });
        
        // Register event listener for login events
        Event::listen(Login::class, function ($event) {
            // Log the login activity
            if ($event->user) {
                $event->user->updateLastLogin();
                $event->user->logActivity('user_login', __('messages.user_login_log', ['name' => $event->user->name]));
            }
        });
        
        // Share notifications data with all views
        view()->composer('*', function ($view) {
            if (auth()->check()) {
                $notificationService = app(NotificationService::class);
                $view->with('unreadNotificationsCount', $notificationService->getUnreadCount());
                $view->with('recentNotifications', $notificationService->getRecentNotifications(5));
            }
        });
        view()->composer('*', function ($view) {
            if (auth()->check()) {
                $notificationService = app(NotificationService::class);
                $view->with('unreadNotificationsCount', $notificationService->getUnreadCount());
                $view->with('recentNotifications', $notificationService->getRecentNotifications(5));
            }
        });
    }
}
