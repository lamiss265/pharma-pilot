@extends('layouts.app')

@section('title', __('messages.dashboard'))

@section('content')
                    <div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">{{ __('messages.welcome') }}</h1>
            <p class="text-muted">{{ __('messages.role') }}: {{ auth()->user()->isAdmin() ? __('messages.admin') : __('messages.worker') }}</p>
                        </div>
                    </div>

                    <div class="row">
        <div class="col-md-3">
            <div class="card stat-card bg-white">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 d-inline-flex mb-3">
                        <i class="fas fa-box fa-2x text-primary"></i>
                    </div>
                                <div class="stat-number">{{ \App\Models\Product::count() }}</div>
                                <div class="stat-label">{{ __('messages.products') }}</div>
                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
            <div class="card stat-card bg-white">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 d-inline-flex mb-3">
                        <i class="fas fa-tags fa-2x text-success"></i>
                    </div>
                                <div class="stat-number">{{ \App\Models\Category::count() }}</div>
                                <div class="stat-label">{{ __('messages.categories') }}</div>
                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
            <div class="card stat-card bg-white">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 d-inline-flex mb-3">
                        <i class="fas fa-shopping-cart fa-2x text-info"></i>
                    </div>
                                <div class="stat-number">{{ \App\Models\Sale::count() }}</div>
                                <div class="stat-label">{{ __('messages.sales') }}</div>
                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
            <div class="card stat-card bg-white">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 d-inline-flex mb-3">
                        <i class="fas fa-users fa-2x text-warning"></i>
                    </div>
                                <div class="stat-number">{{ \App\Models\Client::count() }}</div>
                                <div class="stat-label">{{ __('messages.clients') }}</div>
                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('messages.low_stock') }}</h5>
                    <a href="{{ route('inventory') }}?filter=low_stock" class="btn btn-sm btn-outline-primary">{{ __('messages.view_all') }}</a>
                                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('messages.product') }}</th>
                                    <th>{{ __('messages.category') }}</th>
                                    <th class="text-end">{{ __('messages.quantity') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                        @foreach(\App\Models\Product::where('quantity', '<=', 10)->take(5)->get() as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->category ? $product->category->name : __('messages.no_category') }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-warning">{{ $product->quantity }}</span>
                                    </td>
                                </tr>
                                        @endforeach
                            </tbody>
                        </table>
                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('messages.near_expiry') }}</h5>
                    <a href="{{ route('inventory') }}?filter=near_expiry" class="btn btn-sm btn-outline-primary">{{ __('messages.view_all') }}</a>
                                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('messages.product') }}</th>
                                    <th>{{ __('messages.category') }}</th>
                                    <th class="text-end">{{ __('messages.expiry_date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                        @foreach(\App\Models\Product::whereDate('expiry_date', '<=', now()->addDays(30))->take(5)->get() as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->category ? $product->category->name : __('messages.no_category') }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-danger">{{ $product->expiry_date->format('Y-m-d') }}</span>
                                    </td>
                                </tr>
                                        @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                                </div>
                            </div>
                        </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('messages.recent_sales') }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('messages.product') }}</th>
                                    <th>{{ __('messages.client') }}</th>
                                    <th>{{ __('messages.quantity') }}</th>
                                    <th>{{ __('messages.price') }}</th>
                                    <th>{{ __('messages.date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(\App\Models\Sale::with(['saleItems.product', 'client'])->latest()->take(5)->get() as $sale)
                                <tr>
                                    <td>{{ $sale->id }}</td>
                                    <td>{{ ($sale->saleItems->first() && $sale->saleItems->first()->product) ? $sale->saleItems->first()->product->name : __('messages.deleted_product') }}</td>
                                    <td>{{ $sale->client ? $sale->client->name : __('messages.walk_in_customer') }}</td>
                                    <td>{{ $sale->quantity }}</td>
                                    <td>{{ ($sale->saleItems->first()) ? number_format($sale->quantity * ($sale->saleItems->first()->unit_price ?? ($sale->saleItems->first()->product->price ?? 0)), 2) : '0.00' }} DH</td>
                                    <td>{{ $sale->created_at->format('Y-m-d') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Any dashboard-specific JavaScript can go here
    });
</script>
@endsection 