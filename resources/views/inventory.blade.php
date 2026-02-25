@extends('layouts.app')

@section('title', __('messages.inventory'))

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">{{ __('messages.inventory') }}</h1>
            <a href="{{ route('products.create') }}" class="btn btn-success">
                <i class="fas fa-plus me-1"></i> {{ __('messages.add_product') }}
            </a>
        </div>
        
        <!-- Filters and Search -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-filter me-1"></i>
                        {{ __('messages.filters') }} & {{ __('messages.search') }}
                    </div>
                    <div>
                        <a href="{{ route('products.purchase-suggestions') }}" class="btn btn-sm btn-info">
                            <i class="fas fa-shopping-cart me-1"></i> {{ __('messages.purchase_suggestions') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('inventory') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="search" name="search" 
                                placeholder="{{ __('messages.search_placeholder') }}" value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">{{ __('messages.all_categories') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="expiry_filter" name="expiry_filter">
                            <option value="">{{ __('messages.all_expiry') }}</option>
                            <option value="expired" {{ request('expiry_filter') == 'expired' ? 'selected' : '' }}>{{ __('messages.expired') }}</option>
                            <option value="near_expiry" {{ request('expiry_filter') == 'near_expiry' ? 'selected' : '' }}>{{ __('messages.near_expiry') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="stock_filter" name="stock_filter">
                            <option value="">{{ __('messages.all_stock') }}</option>
                            <option value="low_stock" {{ request('stock_filter') == 'low_stock' ? 'selected' : '' }}>{{ __('messages.low_stock') }}</option>
                            <option value="out_of_stock" {{ request('stock_filter') == 'out_of_stock' ? 'selected' : '' }}>{{ __('messages.out_of_stock') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-filter me-1"></i> {{ __('messages.filter') }}
                            </button>
                            <a href="{{ route('inventory') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <select class="form-select" id="therapeutic_class" name="therapeutic_class">
                            <option value="">{{ __('messages.all_therapeutic_classes') }}</option>
                            @foreach($therapeuticClasses as $class)
                                <option value="{{ $class }}" {{ request('therapeutic_class') == $class ? 'selected' : '' }}>
                                    {{ $class }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="dosage_form" name="dosage_form">
                            <option value="">{{ __('messages.all_dosage_forms') }}</option>
                            @foreach($dosageForms as $form)
                                <option value="{{ $form }}" {{ request('dosage_form') == $form ? 'selected' : '' }}>
                                    {{ $form }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Products Table -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-table me-1"></i>
                        {{ __('messages.inventory_list') }}
                    </div>
                    <div class="badge bg-primary">{{ $products->count() }} {{ __('messages.products') }}</div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover datatable" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('messages.id') }}</th>
                                <th>{{ __('messages.name') }}</th>
                                <th>{{ __('messages.dci') }}</th>
                                <th>{{ __('messages.dosage_form') }}</th>
                                <th>{{ __('messages.quantity') }}</th>
                                <th>{{ __('messages.price') }}</th>
                                <th>{{ __('messages.expiry_date') }}</th>
                                <th>{{ __('messages.batch_number') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th>{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr class="{{ $product->isExpired() ? 'table-danger' : ($product->isNearExpiry() ? 'table-warning' : ($product->needsReordering() ? 'table-info' : '')) }}">
                                    <td>{{ $product->id }}</td>
                                    <td>
                                        <strong>{{ $product->name }}</strong>
                                        @if($product->category)
                                            <div class="small text-muted">{{ $product->category->name }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $product->dci ?? '-' }}</td>
                                    <td>{{ $product->dosage_form ?? '-' }}</td>
                                    <td>
                                        <span class="fw-bold {{ $product->quantity <= 10 ? 'text-danger' : '' }}">
                                            {{ $product->quantity }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($product->price, 2) }} DH</td>
                                    <td>
                                        <span class="{{ $product->isExpired() ? 'text-danger fw-bold' : ($product->isNearExpiry() ? 'text-warning fw-bold' : '') }}">
                                            {{ $product->expiry_date->format('Y-m-d') }}
                                        </span>
                                    </td>
                                    <td>{{ $product->batch_number ?? '-' }}</td>
                                    <td>
                                        @if($product->isExpired())
                                            <span class="badge bg-danger">{{ __('messages.expired') }}</span>
                                        @elseif($product->isNearExpiry())
                                            <span class="badge bg-warning text-dark">{{ __('messages.near_expiry') }}</span>
                                        @endif
                                        
                                        @if($product->needsReordering() && $product->quantity > 0)
                                            <span class="badge bg-info text-dark">{{ __('messages.low_stock') }}</span>
                                        @elseif($product->quantity == 0)
                                            <span class="badge bg-secondary">{{ __('messages.out_of_stock') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('messages.confirm_delete') }}')">
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
        
        <!-- Legend -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <i class="fas fa-info-circle me-1"></i>
                {{ __('messages.status_legend') }}
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <div class="d-flex align-items-center">
                            <div class="bg-danger rounded-circle p-2 me-2" style="width: 15px; height: 15px;"></div>
                            <span>{{ __('messages.expired') }}</span>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning rounded-circle p-2 me-2" style="width: 15px; height: 15px;"></div>
                            <span>{{ __('messages.near_expiry') }} (< 30 {{ __('messages.days') }})</span>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="d-flex align-items-center">
                            <div class="bg-info rounded-circle p-2 me-2" style="width: 15px; height: 15px;"></div>
                            <span>{{ __('messages.low_stock') }} (≤ {{ __('messages.reorder_point') }})</span>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="d-flex align-items-center">
                            <div class="bg-secondary rounded-circle p-2 me-2" style="width: 15px; height: 15px;"></div>
                            <span>{{ __('messages.out_of_stock') }}</span>
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
        // Override DataTable options for this page only
        if ($.fn.dataTable.isDataTable('.datatable')) {
            $('.datatable').DataTable().destroy();
        }
        
        // Initialize DataTable with custom options
        $('.datatable').DataTable({
            "order": [[1, "asc"]],
            "pageLength": 25,
            "scrollX": true,
            "autoWidth": false
        });
        
        // Apply filters on change
        $('#category_id, #expiry_filter, #stock_filter, #therapeutic_class, #dosage_form').change(function() {
            var form = $(this).closest('form');
            form.submit();
        });
    });
</script>
@endsection