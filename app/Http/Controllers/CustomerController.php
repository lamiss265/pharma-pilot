<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index()
    {
        $customers = Customer::withCount('sales')
                           ->withSum('sales', 'final_amount')
                           ->orderBy('created_at', 'desc')
                           ->paginate(20);
        
        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'preferred_language' => 'required|in:en,fr,ar',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean'
        ]);

        $validated['customer_number'] = Customer::generateCustomerNumber();
        $validated['email_notifications'] = $request->has('email_notifications');
        $validated['sms_notifications'] = $request->has('sms_notifications');

        $customer = Customer::create($validated);

        // Handle AJAX requests (for POS system)
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('messages.customer_created_successfully'),
                'customer' => $customer
            ]);
        }

        return redirect()->route('customers.index')
                        ->with('success', __('messages.customer_created_successfully'));
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer)
    {
        $customer->load(['sales' => function($query) {
            $query->with(['saleItems.product', 'user'])
                  ->orderBy('created_at', 'desc')
                  ->limit(10);
        }]);

        $stats = [
            'total_sales' => $customer->sales()->count(),
            'total_spent' => $customer->total_spent,
            'average_purchase' => $customer->total_purchases > 0 ? $customer->total_spent / $customer->total_purchases : 0,
            'last_purchase' => $customer->sales()->latest()->first()?->created_at,
            'loyalty_points' => $customer->loyalty_points,
            'tier' => $customer->tier
        ];

        return view('customers.show', compact('customer', 'stats'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'preferred_language' => 'required|in:en,fr,ar',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean'
        ]);

        $validated['email_notifications'] = $request->has('email_notifications');
        $validated['sms_notifications'] = $request->has('sms_notifications');

        $customer->update($validated);

        return redirect()->route('customers.show', $customer)
                        ->with('success', __('messages.customer_updated_successfully'));
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(Customer $customer)
    {
        // Check if customer has sales
        if ($customer->sales()->count() > 0) {
            return redirect()->route('customers.index')
                            ->with('error', __('messages.cannot_delete_customer_with_sales'));
        }

        $customer->delete();

        return redirect()->route('customers.index')
                        ->with('success', __('messages.customer_deleted_successfully'));
    }

    /**
     * Search customers for AJAX requests.
     */
    public function search(Request $request)
    {
        $query = $request->get('query');
        
        $customers = Customer::where('name', 'like', "%{$query}%")
                           ->orWhere('customer_number', 'like', "%{$query}%")
                           ->orWhere('phone', 'like', "%{$query}%")
                           ->orWhere('email', 'like', "%{$query}%")
                           ->limit(10)
                           ->get(['id', 'customer_number', 'name', 'phone', 'email', 'loyalty_points', 'tier']);

        return response()->json($customers);
    }

    /**
     * Get customer purchase history.
     */
    public function purchaseHistory(Customer $customer)
    {
        $sales = $customer->sales()
                         ->with(['saleItems.product', 'user'])
                         ->orderBy('created_at', 'desc')
                         ->paginate(15);

        return view('customers.purchase-history', compact('customer', 'sales'));
    }

    /**
     * Add loyalty points manually.
     */
    public function addLoyaltyPoints(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:1',
            'reason' => 'required|string|max:255'
        ]);

        $customer->increment('loyalty_points', $validated['points']);

        // Log the activity (you can create an activity log model)
        
        return redirect()->route('customers.show', $customer)
                        ->with('success', __('messages.loyalty_points_added'));
    }

    /**
     * Redeem loyalty points.
     */
    public function redeemLoyaltyPoints(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:1|max:' . $customer->loyalty_points,
            'reason' => 'required|string|max:255'
        ]);

        if ($customer->redeemLoyaltyPoints($validated['points'])) {
            return redirect()->route('customers.show', $customer)
                            ->with('success', __('messages.loyalty_points_redeemed'));
        }

        return redirect()->route('customers.show', $customer)
                        ->with('error', __('messages.insufficient_loyalty_points'));
    }

    /**
     * Export customer data.
     */
    public function export()
    {
        $customers = Customer::with('sales')->get();
        
        $filename = 'customers_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function() use ($customers) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Customer Number', 'Name', 'Email', 'Phone', 'Total Spent', 
                'Total Purchases', 'Loyalty Points', 'Tier', 'Created Date'
            ]);
            
            // Customer data
            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->customer_number,
                    $customer->name,
                    $customer->email,
                    $customer->phone,
                    $customer->total_spent,
                    $customer->total_purchases,
                    $customer->loyalty_points,
                    $customer->tier,
                    $customer->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        }, 200, $headers);
    }
}
