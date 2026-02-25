@extends('layouts.app')

@section('title', __('messages.product_details'))

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('messages.product_details') }}</h5>
            <div>
                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-light">
                    <i class="fas fa-edit"></i> {{ __('messages.edit') }}
                </a>
                <a href="{{ route('inventory') }}" class="btn btn-sm btn-light ml-2">
                    <i class="fas fa-arrow-left"></i> {{ __('messages.back') }}
                </a>
            </div>
        </div>
        
        <div class="card-body">
            <div class="row">
                <!-- Basic Information -->
                <div class="col-md-6">
                    <h5 class="border-bottom pb-2">{{ __('messages.basic_information') }}</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">{{ __('messages.name') }}</th>
                                <td>{{ $product->name }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.category') }}</th>
                                <td>{{ $product->category ? $product->category->name : __('messages.uncategorized') }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.price') }}</th>
                                <td>{{ number_format($product->price, 2) }} {{ __('messages.currency') }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.supplier') }}</th>
                                <td>{{ $product->supplier }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.barcode') }}</th>
                                <td>
                                    @if($product->barcode)
                                        <div class="d-flex align-items-center">
                                            <span class="mr-2">{{ $product->barcode }}</span>
                                            <svg id="barcode"></svg>
                                        </div>
                                    @else
                                        <span class="text-muted">{{ __('messages.not_available') }}</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Inventory Information -->
                <div class="col-md-6">
                    <h5 class="border-bottom pb-2">{{ __('messages.inventory_information') }}</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">{{ __('messages.quantity') }}</th>
                                <td>
                                    <span class="{{ $product->quantity <= $product->reorder_point ? 'text-danger' : '' }}">
                                        {{ $product->quantity }}
                                    </span>
                                    @if($product->quantity <= $product->reorder_point)
                                        <span class="badge badge-danger ml-2">{{ __('messages.low_stock') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.reorder_point') }}</th>
                                <td>{{ $product->reorder_point }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.optimal_stock') }}</th>
                                <td>{{ $product->optimal_stock_level }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.batch_number') }}</th>
                                <td>{{ $product->batch_number ?? __('messages.not_available') }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.manufacturing_date') }}</th>
                                <td>{{ $product->manufacturing_date ? $product->manufacturing_date->format('Y-m-d') : __('messages.not_available') }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.expiry_date') }}</th>
                                <td>
                                    <span class="{{ $product->isNearExpiry() ? 'text-warning' : ($product->isExpired() ? 'text-danger' : '') }}">
                                        {{ $product->expiry_date->format('Y-m-d') }}
                                    </span>
                                    @if($product->isExpired())
                                        <span class="badge badge-danger ml-2">{{ __('messages.expired') }}</span>
                                    @elseif($product->isNearExpiry())
                                        <span class="badge badge-warning ml-2">{{ __('messages.near_expiry') }}</span>
                                    @endif
                                </td>
                            </tr>
                            @if($product->manufacturing_date && $product->expiry_date)
                            <tr>
                                <th>{{ __('messages.shelf_life') }}</th>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar {{ $product->getRemainingShelfLifePercentage() < 30 ? 'bg-danger' : ($product->getRemainingShelfLifePercentage() < 70 ? 'bg-warning' : 'bg-success') }}" 
                                             role="progressbar" 
                                             style="width: {{ $product->getRemainingShelfLifePercentage() }}%"
                                             aria-valuenow="{{ $product->getRemainingShelfLifePercentage() }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            {{ $product->getRemainingShelfLifePercentage() }}%
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        {{ $product->getRemainingShelfLife() }} {{ __('messages.days_remaining') }} / {{ $product->getShelfLife() }} {{ __('messages.total_days') }}
                                    </small>
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Pharmaceutical Information -->
            <div class="row mt-4">
                <div class="col-12">
                    <h5 class="border-bottom pb-2">{{ __('messages.pharmaceutical_information') }}</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th width="20%">{{ __('messages.dci') }}</th>
                                <td>{{ $product->dci ?? __('messages.not_available') }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.dosage_form') }}</th>
                                <td>{{ $product->dosage_form ?? __('messages.not_available') }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.therapeutic_class') }}</th>
                                <td>{{ $product->therapeutic_class ?? __('messages.not_available') }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.storage_conditions') }}</th>
                                <td>{{ $product->storage_conditions ?? __('messages.not_available') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Detailed Information -->
            <div class="row mt-4">
                <div class="col-12">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" id="productDetailTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="composition-tab" data-bs-toggle="tab" data-bs-target="#composition" 
                                    type="button" role="tab" aria-controls="composition" aria-selected="true">
                                {{ __('messages.composition') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="indications-tab" data-bs-toggle="tab" data-bs-target="#indications" 
                                    type="button" role="tab" aria-controls="indications" aria-selected="false">
                                {{ __('messages.indications') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contraindications-tab" data-bs-toggle="tab" data-bs-target="#contraindications" 
                                    type="button" role="tab" aria-controls="contraindications" aria-selected="false">
                                {{ __('messages.contraindications') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="side-effects-tab" data-bs-toggle="tab" data-bs-target="#side-effects" 
                                    type="button" role="tab" aria-controls="side-effects" aria-selected="false">
                                {{ __('messages.side_effects') }}
                            </button>
                        </li>
                    </ul>
                    
                    <!-- Tab panes -->
                    <div class="tab-content p-3 border border-top-0" id="productDetailTabContent">
                        <div class="tab-pane fade show active" id="composition" role="tabpanel" aria-labelledby="composition-tab">
                            {!! $product->composition ?? '<p class="text-muted">' . __('messages.not_available') . '</p>' !!}
                        </div>
                        <div class="tab-pane fade" id="indications" role="tabpanel" aria-labelledby="indications-tab">
                            {!! $product->indications ?? '<p class="text-muted">' . __('messages.not_available') . '</p>' !!}
                        </div>
                        <div class="tab-pane fade" id="contraindications" role="tabpanel" aria-labelledby="contraindications-tab">
                            {!! $product->contraindications ?? '<p class="text-muted">' . __('messages.not_available') . '</p>' !!}
                        </div>
                        <div class="tab-pane fade" id="side-effects" role="tabpanel" aria-labelledby="side-effects-tab">
                            {!! $product->side_effects ?? '<p class="text-muted">' . __('messages.not_available') . '</p>' !!}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-3">
                <a href="{{ route('products.batches.index', $product) }}" class="btn btn-info">{{ __('messages.batches') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Include Bootstrap 5 JS bundle (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

@if($product->barcode)
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        JsBarcode("#barcode", "{{ $product->barcode }}", {
            format: "EAN13",
            lineColor: "#000",
            width: 2,
            height: 40,
            displayValue: false
        });
    });
</script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Manual tab initialization for Bootstrap 5
        var triggerTabList = [].slice.call(document.querySelectorAll('#productDetailTabs button'))
        triggerTabList.forEach(function(triggerEl) {
            var tabTrigger = new bootstrap.Tab(triggerEl)
            
            triggerEl.addEventListener('click', function(event) {
                event.preventDefault()
                tabTrigger.show()
            })
        })
    });
</script>
@endpush