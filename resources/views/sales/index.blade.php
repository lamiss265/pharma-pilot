@extends('layouts.app')

@section('title', __('messages.sales'))

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">{{ __('messages.sales') }}</h1>
            <a href="{{ route('pos.index') }}" class="btn btn-success">
                <i class="fas fa-plus-circle me-1"></i> {{ __('messages.add_sale') }}
            </a>
        </div>
        
        <!-- Sales Table -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-cash-register me-1"></i>
                        {{ __('messages.sales_history') }}
                    </div>
                    <div class="badge bg-primary">{{ $sales->count() }} {{ __('messages.total') }}</div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover datatable" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('messages.id') }}</th>
                                <th>{{ __('messages.date') }}</th>
                                <th>{{ __('messages.product') }}</th>
                                <th>{{ __('messages.quantity') }}</th>
                                <th>{{ __('messages.price') }}</th>
                                <th>{{ __('messages.client') }}</th>
                                <th>{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sales as $sale)
                                <tr>
                                    <td>{{ $sale->id }}</td>
                                    <td>{{ $sale->sale_date->format('Y-m-d') }}</td>
                                    <td>
                                        <strong>{{ $sale->saleItems->pluck('product.name')->filter()->implode(', ') ?: (optional($sale->product)->name ?? __('messages.unknown_product')) }}</strong>
                                        
                                            
                                        
                                    </td>
                                    <td>{{ $sale->saleItems->sum('quantity') ?: $sale->quantity }}</td>
                                    <td>{{ number_format($sale->final_amount ?? $sale->total_amount ?? 0, 2) }} {{ __('messages.currency') }}</td>
                                    <td>{{ optional($sale->customer)->name ?: optional($sale->client)->name ?: __('messages.walk_in_customer') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form action="{{ route('sales.destroy', $sale) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('messages.confirm_delete_sale') }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Sales Summary -->
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 d-inline-flex mb-3">
                            <i class="fas fa-cash-register fa-2x text-primary"></i>
                        </div>
                        <h5 class="card-title">{{ __('messages.total_sales') }}</h5>
                        <p class="display-5 fw-bold">{{ $sales->count() }}</p>
                        <p class="text-muted">{{ __('messages.transactions') }}</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 d-inline-flex mb-3">
                            <i class="fas fa-box fa-2x text-success"></i>
                        </div>
                        <h5 class="card-title">{{ __('messages.total_units_sold') }}</h5>
                        <p class="display-5 fw-bold">{{ $sales->sum('quantity') }}</p>
                        <p class="text-muted">{{ __('messages.units') }}</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <div class="rounded-circle bg-info bg-opacity-10 p-3 d-inline-flex mb-3">
                            <i class="fas fa-users fa-2x text-info"></i>
                        </div>
                        <h5 class="card-title">{{ __('messages.unique_clients') }}</h5>
                        <p class="display-5 fw-bold">{{ $sales->whereNotNull('client_id')->pluck('client_id')->unique()->count() }}</p>
                        <p class="text-muted">{{ __('messages.clients') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Override DataTable options for this page only
        if ($.fn.dataTable.isDataTable('.datatable')) {
            $('.datatable').DataTable().destroy();
        }
        
        // Initialize DataTable with custom options
        $('.datatable').DataTable({
            "order": [[1, "desc"]],
            "pageLength": 25,
            "autoWidth": false
        });
    });
</script>
@endsection