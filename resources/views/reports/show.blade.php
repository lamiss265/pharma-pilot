@extends('layouts.app')

@section('title', ucfirst($reportType) . ' Report')

@section('content')
    <div class="container-fluid">
        <h1 class="mt-4 mb-4">{{ ucfirst($reportType) }} Report</h1>
        
        <!-- Report Header -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-1"></i>
                Report Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>Report Type:</strong> {{ ucfirst($reportType) }}</p>
                    </div>
                    
                    @if($reportType !== 'inventory')
                        <div class="col-md-4">
                            <p><strong>Start Date:</strong> {{ $startDate->format('Y-m-d') }}</p>
                        </div>
                        
                        <div class="col-md-4">
                            <p><strong>End Date:</strong> {{ $endDate->format('Y-m-d') }}</p>
                        </div>
                    @endif
                </div>
                
                <div class="mt-3">
                    <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Reports
                    </a>
                    
                    <form action="{{ route('reports.generate') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="report_type" value="{{ $reportType }}">
                        @if($reportType !== 'inventory')
                            <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                            <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                        @endif
                        <input type="hidden" name="export" value="csv">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-file-csv me-1"></i> Export to CSV
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Report Content -->
        @if($reportType === 'sales')
            <!-- Sales Report -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-line me-1"></i>
                    Sales Report Summary
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Total Sales</h5>
                                    <p class="card-text h3">{{ $data['totalSales'] }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Total Units Sold</h5>
                                    <p class="card-text h3">{{ $data['totalQuantity'] }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Date Range</h5>
                                    <p class="card-text">{{ $data['startDate']->format('Y-m-d') }} to {{ $data['endDate']->format('Y-m-d') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered datatable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Client</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['sales'] as $sale)
                                    <tr>
                                        <td>{{ $sale->sale_date->format('Y-m-d') }}</td>
                                        <td>{{ optional($sale->product)->name ?? 'Unknown Product' }}</td>
                                        <td>{{ $sale->quantity }}</td>
                                        <td>{{ $sale->client ? $sale->client->name : 'Walk-in Customer' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @elseif($reportType === 'inventory')
            <!-- Inventory Report -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-boxes me-1"></i>
                    Inventory Report Summary
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Total Products</h5>
                                    <p class="card-text h3">{{ $data['totalProducts'] }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Total Stock</h5>
                                    <p class="card-text h3">{{ $data['totalStock'] }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Low Stock Items</h5>
                                    <p class="card-text h3">{{ $data['lowStockCount'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered datatable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Quantity</th>
                                    <th>Supplier</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['products'] as $product)
                                    <tr class="{{ $product->isExpired() ? 'table-danger' : ($product->isNearExpiry() ? 'table-warning' : ($product->isLowStock() ? 'table-info' : '')) }}">
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->quantity }}</td>
                                        <td>{{ $product->supplier }}</td>
                                        <td>{{ $product->expiry_date->format('Y-m-d') }}</td>
                                        <td>
                                            @if($product->isExpired())
                                                <span class="badge bg-danger">Expired</span>
                                            @elseif($product->isNearExpiry())
                                                <span class="badge bg-warning text-dark">Near Expiry</span>
                                            @endif
                                            
                                            @if($product->isLowStock())
                                                <span class="badge bg-info text-dark">Low Stock</span>
                                            @elseif($product->quantity == 0)
                                                <span class="badge bg-secondary">Out of Stock</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @elseif($reportType === 'expiry')
            <!-- Expiry Report -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar-times me-1"></i>
                    Expiry Report Summary
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Expired Products</h5>
                                    <p class="card-text h3">{{ $data['expiredCount'] }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Near Expiry Products</h5>
                                    <p class="card-text h3">{{ $data['nearExpiryCount'] }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">End Date</h5>
                                    <p class="card-text">{{ $data['endDate']->format('Y-m-d') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered datatable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Quantity</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                    <th>Supplier</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['products'] as $product)
                                    <tr class="{{ $product->isExpired() ? 'table-danger' : 'table-warning' }}">
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->quantity }}</td>
                                        <td>{{ $product->expiry_date->format('Y-m-d') }}</td>
                                        <td>
                                            @if($product->isExpired())
                                                <span class="badge bg-danger">Expired</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Near Expiry</span>
                                            @endif
                                        </td>
                                        <td>{{ $product->supplier }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
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
            "pageLength": 25
        });
    });
</script>
@endsection 