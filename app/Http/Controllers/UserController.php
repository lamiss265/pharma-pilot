<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);
        
        $users = User::orderBy('name')->get();
        
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', User::class);
        
        return view('users.create');
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,worker',
            'language' => 'required|in:en,fr,ar',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'position' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
            'permissions' => 'nullable|array',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'language' => $request->language,
            'phone' => $request->phone,
            'address' => $request->address,
            'position' => $request->position,
            'status' => $request->status,
            'permissions' => $request->permissions,
        ]);
        
        // Log activity
        if (Auth::check()) {
            Auth::user()->logActivity(
                'user_created',
                __('messages.user_created_log', ['name' => $user->name]),
                $user
            );
        }
        
        return redirect()->route('users.index')
            ->with('success', __('messages.user_created'));
    }

    /**
     * Display the specified user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        
        // Get user's activities
        $activities = UserActivity::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Get user's sales statistics
        $totalSalesCount = $user->getTotalSalesCount();
        $totalSalesAmount = $user->getTotalSalesAmount();
        
        // Get monthly sales data for chart (last 12 months) using sale_items unit_price
        $monthlySales = \DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $user->id)
            ->where('sales.created_at', '>=', now()->subMonths(12))
            ->selectRaw('MONTH(sales.created_at) as month, YEAR(sales.created_at) as year, SUM(sale_items.quantity * sale_items.unit_price) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
        
        return view('users.show', compact('user', 'activities', 'totalSalesCount', 'totalSalesAmount', 'monthlySales'));
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'role' => 'required|in:admin,worker',
            'language' => 'required|in:en,fr,ar',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'position' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
            'permissions' => 'nullable|array',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'language' => $request->language,
            'phone' => $request->phone,
            'address' => $request->address,
            'position' => $request->position,
            'status' => $request->status,
            'permissions' => $request->permissions,
        ]);
        
        // Log activity
        if (Auth::check()) {
            Auth::user()->logActivity(
                'user_updated',
                __('messages.user_updated_log', ['name' => $user->name]),
                $user
            );
        }
        
        return redirect()->route('users.index')
            ->with('success', __('messages.user_updated'));
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        
        // Prevent deleting yourself
        if (Auth::id() === $user->id) {
            return redirect()->route('users.index')
                ->with('error', __('messages.cannot_delete_self'));
        }
        
        // Store user name for activity log
        $userName = $user->name;
        
        // Delete user
        $user->delete();
        
        // Log activity
        if (Auth::check()) {
            Auth::user()->logActivity(
                'user_deleted',
                __('messages.user_deleted_log', ['name' => $userName]),
                null
            );
        }
        
        return redirect()->route('users.index')
            ->with('success', __('messages.user_deleted'));
    }
    
    /**
     * Show the form for changing user password.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function changePassword(User $user)
    {
        $this->authorize('changePassword', $user);
        
        return view('users.change-password', compact('user'));
    }
    
    /**
     * Update the specified user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request, User $user)
    {
        $this->authorize('changePassword', $user);
        
        $validator = Validator::make($request->all(), [
            'current_password' => [
                'required',
                function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        $fail(__('messages.current_password_incorrect'));
                    }
                },
            ],
            'password' => 'required|string|min:8|confirmed|different:current_password',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }
        
        $user->update([
            'password' => Hash::make($request->password),
        ]);
        
        // Log activity
        if (Auth::check()) {
            Auth::user()->logActivity(
                'password_changed',
                __('messages.password_changed_log', ['name' => $user->name]),
                $user
            );
        }
        
        return redirect()->route('users.show', $user)
            ->with('success', __('messages.password_changed'));
    }
    
    /**
     * Show the user's activity log.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function activities(User $user)
    {
        $this->authorize('viewActivities', $user);
        
        $activities = UserActivity::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('users.activities', compact('user', 'activities'));
    }
    
    /**
     * Show the sales performance dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function salesPerformance()
    {
        $this->authorize('viewSalesPerformance', User::class);
        
        // Get top performing users by sales amount
        $workers = User::where('role', 'worker')
            ->withCount('sales')
            ->get();
            
        // Calculate total sales amount for each worker
        foreach ($workers as $worker) {
            $worker->total_sales_amount = $worker->sales->sum(function ($sale) {
                return $sale->saleItems->sum(function ($item) {
                    return $item->quantity * $item->unit_price;
                });
            });
        }
        
        // Sort by total sales amount
        $topUsersBySales = $workers->sortByDesc('total_sales_amount')->take(10)->values();
        
        // Get monthly sales data for chart
        $monthlySales = collect();
        
        // Get all sales from the last 12 months
        $sales = \DB::table('sales')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->join('sale_items', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select('sales.*', 'users.name', 'sale_items.unit_price as product_price', 'sale_items.quantity')
            ->whereRaw('sales.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)')
            ->get();
            
        // Group sales by year, month and user
        $salesByMonth = $sales->groupBy(function($sale) {
            $date = new \DateTime($sale->created_at);
            return $date->format('Y-m');
        })->map(function($monthSales) {
            return $monthSales->groupBy('name');
        });
        
        // Calculate totals for each month and user
        foreach ($salesByMonth as $yearMonth => $userSales) {
            list($year, $month) = explode('-', $yearMonth);
            
            foreach ($userSales as $userName => $sales) {
                $total = $sales->sum(function($sale) {
                    return $sale->quantity * ($sale->price ?? $sale->product_price ?? 0);
                });
                
                $monthlySales->push([
                    'year' => (int)$year,
                    'month' => (int)$month,
                    'name' => $userName,
                    'total' => $total
                ]);
            }
        }
        
        // Convert to array for JSON encoding
        $monthlySales = $monthlySales->sortBy('year')->sortBy('month')->values();
        
        return view('users.sales-performance', compact('topUsersBySales', 'monthlySales'));
    }

    /**
     * Toggle the user's role between admin and worker.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function toggleRole(User $user)
    {
        $this->authorize('update', $user);
        
        // Prevent changing your own role
        if (Auth::id() === $user->id) {
            return redirect()->route('users.index')
                ->with('error', __('messages.cannot_change_own_role'));
        }
        
        // Toggle role
        $newRole = $user->role === 'admin' ? 'worker' : 'admin';
        $user->update(['role' => $newRole]);
        
        // Log activity
        if (Auth::check()) {
            $action = $newRole === 'admin' ? 'user_promoted' : 'user_demoted';
            $message = $newRole === 'admin' 
                ? __('messages.user_promoted_log', ['name' => $user->name])
                : __('messages.user_demoted_log', ['name' => $user->name]);
                
            Auth::user()->logActivity($action, $message, $user);
        }
        
        $message = $newRole === 'admin' 
            ? __('messages.user_promoted', ['name' => $user->name])
            : __('messages.user_demoted', ['name' => $user->name]);
            
        return redirect()->route('users.index')
            ->with('success', $message);
    }
    
    /**
     * Toggle the user's status between active and inactive.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function toggleStatus(User $user)
    {
        $this->authorize('update', $user);
        
        // Prevent deactivating yourself
        if (Auth::id() === $user->id) {
            return redirect()->route('users.index')
                ->with('error', __('messages.cannot_deactivate_self'));
        }
        
        // Toggle status
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);
        
        // Log activity
        if (Auth::check()) {
            $action = $newStatus === 'active' ? 'user_activated' : 'user_deactivated';
            $message = $newStatus === 'active' 
                ? __('messages.user_activated_log', ['name' => $user->name])
                : __('messages.user_deactivated_log', ['name' => $user->name]);
                
            Auth::user()->logActivity($action, $message, $user);
        }
        
        $message = $newStatus === 'active' 
            ? __('messages.user_activated', ['name' => $user->name])
            : __('messages.user_deactivated', ['name' => $user->name]);
            
        return redirect()->route('users.index')
            ->with('success', $message);
    }
}
