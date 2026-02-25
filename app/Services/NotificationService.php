<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Create a new notification for a user.
     *
     * @param  \App\Models\User  $user
     * @param  string  $type
     * @param  string  $title
     * @param  string  $message
     * @param  string|null  $icon
     * @param  string|null  $color
     * @param  string|null  $link
     * @param  mixed|null  $related
     * @param  array|null  $data
     * @return \App\Models\UserNotification
     */
    public function create(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $icon = null,
        ?string $color = null,
        ?string $link = null,
        $related = null,
        ?array $data = null
    ) {
        $notification = new UserNotification([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
            'color' => $color,
            'link' => $link,
            'related_type' => $related ? get_class($related) : null,
            'related_id' => $related ? $related->id : null,
            'data' => $data,
        ]);
        
        $notification->save();
        
        return $notification;
    }
    
    /**
     * Create a notification for all admin users.
     *
     * @param  string  $type
     * @param  string  $title
     * @param  string  $message
     * @param  string|null  $icon
     * @param  string|null  $color
     * @param  string|null  $link
     * @param  mixed|null  $related
     * @param  array|null  $data
     * @return array
     */
    public function notifyAdmins(
        string $type,
        string $title,
        string $message,
        ?string $icon = null,
        ?string $color = null,
        ?string $link = null,
        $related = null,
        ?array $data = null
    ) {
        $admins = User::where('role', 'admin')->get();
        $notifications = [];
        
        foreach ($admins as $admin) {
            $notifications[] = $this->create(
                $admin,
                $type,
                $title,
                $message,
                $icon,
                $color,
                $link,
                $related,
                $data
            );
        }
        
        return $notifications;
    }
    
    /**
     * Create a notification for a sale.
     *
     * @param  \App\Models\Sale  $sale
     * @return array
     */
    public function notifySale(Sale $sale)
    {
        $user = $sale->user;
        $product = optional($sale->saleItems->first())->product;
        $client = $sale->client;
        $total = $sale->final_amount ?? $sale->saleItems->sum(function($item) { return $item->unit_price * $item->quantity; });
        
        $title = __('messages.new_sale_notification_title');
        $message = __('messages.new_sale_notification_message', [
            'user' => $user ? $user->name : __('messages.unknown_user'),
            'product' => optional($product)->name ?? __('messages.unknown_product'),
            'quantity' => $sale->saleItems->sum('quantity'),
            'client' => $client ? $client->name : __('messages.no_client'),
            'amount' => number_format($total, 2) . ' ' . __('messages.currency')
        ]);
        
        // Check if this sale makes the user a top performer for today
        if ($user) {
            $this->checkTopPerformerToday($user);
        }
        
        return $this->notifyAdmins(
            'sale',
            $title,
            $message,
            'fas fa-cash-register',
            'success',
            route('sales.show', $sale),
            $sale,
            [
                'user_id' => $user ? $user->id : null,
                'product_id' => $product ? $product->id : null,
                'client_id' => $client ? $client->id : null,
                'quantity' => $sale->saleItems->sum('quantity'),
                'price' => $sale->final_amount,
                'total' => $total,
            ]
        );
    }
    
    /**
     * Check if a user is the top performer for today and send notification
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function checkTopPerformerToday(User $user)
    {
        // Only check for worker users
        if (!$user->isWorker()) {
            return;
        }
        
        $today = now()->startOfDay();
        
        // Get the user's sales for today
        $userSales = $user->sales()
            ->whereDate('created_at', $today)
            ->get()
            ->sum(function($sale) {
                return $sale->quantity * ($sale->price ?? optional(optional($sale->saleItems->first())->product)->price ?? 0);
            });
        
        $userSalesCount = $user->sales()
            ->whereDate('created_at', $today)
            ->count();
            
        // Get the top sales amount for today from any user
        $topSales = null;
        $otherWorkers = User::where('id', '!=', $user->id)
            ->where('role', 'worker')
            ->get();
        
        $maxTotal = 0;
        foreach ($otherWorkers as $worker) {
            $total = $worker->sales()
                ->whereDate('created_at', $today)
                ->get()
                ->sum(function($sale) {
                    return $sale->quantity * ($sale->price ?? optional(optional($sale->saleItems->first())->product)->price ?? 0);
                });
            
            if ($total > $maxTotal) {
                $maxTotal = $total;
                $topSales = (object)['total' => $total];
            }
        }
        
        // If this user's sales exceed the previous top performer, notify admins
        if (!$topSales || $userSales > $topSales->total) {
            $this->notifyAdmins(
                'top_performer',
                __('messages.top_performer_today_title'),
                __('messages.top_performer_today_message', [
                    'user' => $user->name,
                    'amount' => number_format($userSales, 2) . ' ' . __('messages.currency'),
                    'count' => $userSalesCount
                ]),
                'fas fa-trophy',
                'info',
                route('users.show', $user),
                $user,
                [
                    'amount' => $userSales,
                    'count' => $userSalesCount,
                    'date' => $today->format('Y-m-d')
                ]
            );
        }
    }
    
    /**
     * Create a notification for a low stock product.
     *
     * @param  \App\Models\Product  $product
     * @return array
     */
    public function notifyLowStock(Product $product)
    {
        $title = __('messages.low_stock_notification_title');
        $message = __('messages.low_stock_notification_message', [
            'product' => optional($product)->name ?? __('messages.unknown_product'),
            'quantity' => $product->quantity,
            'reorder_point' => $product->reorder_point,
        ]);
        
        return $this->notifyAdmins(
            'low_stock',
            $title,
            $message,
            'fas fa-exclamation-triangle',
            'warning',
            route('products.show', $product),
            $product,
            [
                'quantity' => $product->quantity,
                'reorder_point' => $product->reorder_point,
                'optimal_stock_level' => $product->optimal_stock_level,
            ]
        );
    }
    
    /**
     * Create a notification for an expiring product.
     *
     * @param  \App\Models\Product  $product
     * @param  int  $days
     * @return array
     */
    public function notifyExpiringProduct(Product $product, int $days)
    {
        $title = __('messages.expiring_product_notification_title');
        $message = __('messages.expiring_product_notification_message', [
            'product' => optional($product)->name ?? __('messages.unknown_product'),
            'days' => $days,
            'expiry_date' => $product->expiry_date->format('Y-m-d'),
        ]);
        
        return $this->notifyAdmins(
            'expiring_product',
            $title,
            $message,
            'fas fa-calendar-times',
            'danger',
            route('products.show', $product),
            $product,
            [
                'days_remaining' => $days,
                'expiry_date' => $product->expiry_date->format('Y-m-d'),
            ]
        );
    }
    
    /**
     * Create a notification for top sales performer.
     *
     * @param  \App\Models\User  $user
     * @param  float  $amount
     * @param  int  $count
     * @param  string  $period
     * @return \App\Models\UserNotification
     */
    public function notifyTopSalesPerformer(User $user, float $amount, int $count, string $period)
    {
        $title = __('messages.top_performer_notification_title');
        $message = __('messages.top_performer_notification_message', [
            'period' => $period,
            'amount' => number_format($amount, 2) . ' ' . __('messages.currency'),
            'count' => $count,
        ]);
        
        return $this->create(
            $user,
            'top_performer',
            $title,
            $message,
            'fas fa-trophy',
            'info',
            route('users.show', $user),
            $user,
            [
                'amount' => $amount,
                'count' => $count,
                'period' => $period,
            ]
        );
    }
    
    /**
     * Mark all notifications as read for a user.
     *
     * @param  \App\Models\User  $user
     * @return int
     */
    public function markAllAsRead(User $user)
    {
        return UserNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
    
    /**
     * Get unread notifications count for the authenticated user.
     *
     * @return int
     */
    public function getUnreadCount()
    {
        if (!Auth::check()) {
            return 0;
        }
        
        return UserNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();
    }
    
    /**
     * Get recent notifications for the authenticated user.
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentNotifications($limit = 5)
    {
        if (!Auth::check()) {
            return collect([]);
        }
        
        return UserNotification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
} 