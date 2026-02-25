<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateSalesPerformanceReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:performance-report {--period=week}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sales performance report and notify top performers';

    /**
     * The notification service instance.
     *
     * @var \App\Services\NotificationService
     */
    protected $notificationService;

    /**
     * Create a new command instance.
     *
     * @param  \App\Services\NotificationService  $notificationService
     * @return void
     */
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $period = $this->option('period');
        
        $this->info("Generating sales performance report for {$period}...");
        
        switch ($period) {
            case 'day':
                $startDate = Carbon::yesterday()->startOfDay();
                $endDate = Carbon::yesterday()->endOfDay();
                $periodName = 'yesterday';
                break;
            case 'week':
                $startDate = Carbon::now()->subWeek()->startOfWeek();
                $endDate = Carbon::now()->subWeek()->endOfWeek();
                $periodName = 'last week';
                break;
            case 'month':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                $periodName = 'last month';
                break;
            default:
                $this->error("Invalid period: {$period}");
                return 1;
        }
        
        // Get workers who made sales in the period
        $workers = User::where('role', 'worker')
            ->whereHas('sales', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->withCount(['sales as sales_count' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->get();
            
        // Calculate total sales amount for each worker
        foreach ($workers as $worker) {
            $periodSales = $worker->sales()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->with('product')
                ->get();
                
            $worker->total_sales_amount = $periodSales->sum(function($sale) {
                return $sale->quantity * ($sale->price ?? $sale->product->price ?? 0);
            });
        }
        
        // Get top 3 performers
        $topPerformers = $workers->sortByDesc('total_sales_amount')->take(3)->values();
        
        if ($topPerformers->isEmpty()) {
            $this->info("No sales data found for {$periodName}.");
            return 0;
        }
        
        // Notify top performers
        foreach ($topPerformers as $index => $performer) {
            $rank = $index + 1;
            $this->info("Notifying rank #{$rank}: {$performer->name} with {$performer->total_sales_amount} sales");
            
            // Notify the performer
            $this->notificationService->notifyTopSalesPerformer(
                $performer,
                $performer->total_sales_amount,
                $performer->sales_count,
                $periodName
            );
            
            // Notify admins about top performers
            $this->notificationService->notifyAdmins(
                'performance_report',
                __('messages.performance_report_title', ['period' => $periodName]),
                __('messages.performance_report_message', [
                    'rank' => $rank,
                    'user' => $performer->name,
                    'amount' => number_format($performer->total_sales_amount, 2) . ' ' . __('messages.currency'),
                    'count' => $performer->sales_count
                ]),
                'fas fa-chart-line',
                'info',
                route('users.sales-performance'),
                $performer,
                [
                    'rank' => $rank,
                    'amount' => $performer->total_sales_amount,
                    'count' => $performer->sales_count,
                    'period' => $periodName,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d')
                ]
            );
        }
        
        $this->info('Sales performance report generated successfully.');
        return 0;
    }
} 