<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\PasswordReset;
use App\Models\User;
use App\Models\Sale;
use App\Models\Product;
use App\Models\UserActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Log successful login
        Event::listen(Login::class, function ($event) {
            $this->logActivity(
                $event->user,
                'login',
                __('messages.user_login_log'),
                null
            );
            
            // Update last login timestamp
            $event->user->updateLastLogin();
        });
        
        // Log logout
        Event::listen(Logout::class, function ($event) {
            $this->logActivity(
                $event->user,
                'logout',
                __('messages.user_logout_log'),
                null
            );
        });
        
        // Log failed login attempt
        Event::listen(Failed::class, function ($event) {
            $this->logActivity(
                null,
                'login_failed',
                __('messages.login_failed_log', ['email' => $event->credentials['email'] ?? 'unknown']),
                null
            );
        });
        
        // Log password reset
        Event::listen(PasswordReset::class, function ($event) {
            $this->logActivity(
                $event->user,
                'password_reset',
                __('messages.password_reset_log'),
                $event->user
            );
        });
        
        // Log sale creation
        Sale::created(function ($sale) {
            try {
                if (Auth::check()) {
                    $productName = optional(optional($sale->saleItems->first())->product)->name ?? __('messages.unknown_product');
                    $clientName = $sale->client ? $sale->client->name : __('messages.no_client');
                    $this->logActivity(
                        Auth::user(),
                        'sale_created',
                        __('messages.sale_created_log', [
                            'product' => $productName,
                            'quantity' => $sale->quantity,
                            'client' => $clientName
                        ]),
                        $sale
                    );
                }
            } catch (\Throwable $e) {
                Log::error('ActivityServiceProvider sale_created listener error: ' . $e->getMessage());
            }
        });
        
        // Log sale deletion
        Sale::deleted(function ($sale) {
            try {
                if (Auth::check()) {
                    $productName = optional(optional($sale->saleItems->first())->product)->name ?? __('messages.unknown_product');
                    $clientName = $sale->client ? $sale->client->name : __('messages.no_client');
                    $this->logActivity(
                        Auth::user(),
                        'sale_deleted',
                        __('messages.sale_deleted_log', [
                            'product' => $productName,
                            'quantity' => $sale->quantity,
                            'client' => $clientName
                        ]),
                        null
                    );
                }
            } catch (\Throwable $e) {
                Log::error('ActivityServiceProvider sale_deleted listener error: ' . $e->getMessage());
            }
        });
        
        // Log product creation
        Product::created(function ($product) {
            if (Auth::check()) {
                $this->logActivity(
                    Auth::user(),
                    'product_created',
                    __('messages.product_created_log', ['name' => $product->name]),
                    $product
                );
            }
        });
        
        // Log product update
        Product::updated(function ($product) {
            if (Auth::check()) {
                $this->logActivity(
                    Auth::user(),
                    'product_updated',
                    __('messages.product_updated_log', ['name' => $product->name]),
                    $product
                );
            }
        });
        
        // Log product deletion
        Product::deleted(function ($product) {
            if (Auth::check()) {
                $this->logActivity(
                    Auth::user(),
                    'product_deleted',
                    __('messages.product_deleted_log', ['name' => $product->name]),
                    null
                );
            }
        });
    }
    
    /**
     * Log an activity.
     *
     * @param  \App\Models\User|null  $user
     * @param  string  $action
     * @param  string  $description
     * @param  mixed  $subject
     * @return void
     */
    protected function logActivity($user, $action, $description, $subject)
    {
        try {
            $activity = new UserActivity([
                'user_id' => $user ? $user->id : null,
                'action' => $action,
                'description' => $description,
                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id' => $subject ? $subject->id : null,
                'properties' => $subject ? json_encode($subject->toArray()) : null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
            
            $activity->save();
        } catch (\Exception $e) {
            // Log the error but don't crash the application
            \Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }
}
