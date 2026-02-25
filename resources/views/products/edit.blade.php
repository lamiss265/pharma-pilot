@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
    <div class="container-fluid">
        <h1 class="mt-4 mb-4">{{ __('messages.edit_product') }}: {{ $product->name }}</h1>
        
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-edit me-1"></i>
                {{ __('messages.product_details') }}
            </div>
            <div class="card-body">
                <form action="{{ route('products.update', $product) }}" method="POST" id="productForm">
                    @csrf
                    @method('PUT')
                    
                    <ul class="nav nav-tabs mb-3" id="productTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="basic-tab" data-toggle="tab" href="#basic" role="tab" aria-controls="basic" aria-selected="true">
                                {{ __('messages.basic_information') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="inventory-tab" data-toggle="tab" href="#inventory" role="tab" aria-controls="inventory" aria-selected="false">
                                {{ __('messages.inventory_details') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pharma-tab" data-toggle="tab" href="#pharma" role="tab" aria-controls="pharma" aria-selected="false">
                                {{ __('messages.pharmaceutical_details') }}
                            </a>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="productTabsContent">
                        <!-- Basic Information Tab -->
                        <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">{{ __('messages.product_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="category_id" class="form-label">{{ __('messages.category') }}</label>
                                    <select class="form-control @error('category_id') is-invalid @enderror" id="category_id" name="category_id">
                                        <option value="">{{ __('messages.select_category') }}</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="price" class="form-label">{{ __('messages.price') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">DH</span>
                                        </div>
                                        <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $product->price) }}" required>
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="supplier" class="form-label">{{ __('messages.supplier') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('supplier') is-invalid @enderror" id="supplier" name="supplier" value="{{ old('supplier', $product->supplier) }}" required>
                                    @error('supplier')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="barcode" class="form-label">{{ __('messages.barcode') }} (EAN-13)</label>
                                    <input type="text" class="form-control @error('barcode') is-invalid @enderror" id="barcode" name="barcode" value="{{ old('barcode', $product->barcode) }}" maxlength="13" pattern="[0-9]{13}">
                                    <small class="form-text text-muted">{{ __('messages.barcode_hint') }}</small>
                                    @error('barcode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="batch_number" class="form-label">{{ __('messages.batch_number') }}</label>
                                    <input type="text" class="form-control @error('batch_number') is-invalid @enderror" id="batch_number" name="batch_number" value="{{ old('batch_number', $product->batch_number) }}">
                                    @error('batch_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Inventory Details Tab -->
                        <div class="tab-pane fade" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="quantity" class="form-label">{{ __('messages.quantity') }} <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity', $product->quantity) }}" min="0" required>
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="reorder_point" class="form-label">{{ __('messages.reorder_point') }}</label>
                                    <input type="number" class="form-control @error('reorder_point') is-invalid @enderror" id="reorder_point" name="reorder_point" value="{{ old('reorder_point', $product->reorder_point) }}" min="0">
                                    <small class="form-text text-muted">{{ __('messages.reorder_point_hint') }}</small>
                                    @error('reorder_point')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="optimal_stock_level" class="form-label">{{ __('messages.optimal_stock_level') }}</label>
                                    <input type="number" class="form-control @error('optimal_stock_level') is-invalid @enderror" id="optimal_stock_level" name="optimal_stock_level" value="{{ old('optimal_stock_level', $product->optimal_stock_level) }}" min="0">
                                    @error('optimal_stock_level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="manufacturing_date" class="form-label">{{ __('messages.manufacturing_date') }}</label>
                                    <input type="date" class="form-control @error('manufacturing_date') is-invalid @enderror" id="manufacturing_date" name="manufacturing_date" value="{{ old('manufacturing_date', $product->manufacturing_date ? $product->manufacturing_date->format('Y-m-d') : '') }}">
                                    @error('manufacturing_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="expiry_date" class="form-label">{{ __('messages.expiry_date') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" id="expiry_date" name="expiry_date" value="{{ old('expiry_date', $product->expiry_date->format('Y-m-d')) }}" required>
                                    @error('expiry_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="storage_conditions" class="form-label">{{ __('messages.storage_conditions') }}</label>
                                <input type="text" class="form-control @error('storage_conditions') is-invalid @enderror" id="storage_conditions" name="storage_conditions" value="{{ old('storage_conditions', $product->storage_conditions) }}">
                                <small class="form-text text-muted">{{ __('messages.storage_conditions_hint') }}</small>
                                @error('storage_conditions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Pharmaceutical Details Tab -->
                        <div class="tab-pane fade" id="pharma" role="tabpanel" aria-labelledby="pharma-tab">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="dci" class="form-label">{{ __('messages.dci') }}</label>
                                    <input type="text" class="form-control @error('dci') is-invalid @enderror" id="dci" name="dci" value="{{ old('dci', $product->dci) }}">
                                    <small class="form-text text-muted">{{ __('messages.dci_hint') }}</small>
                                    @error('dci')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="dosage_form" class="form-label">{{ __('messages.dosage_form') }}</label>
                                    <input type="text" class="form-control @error('dosage_form') is-invalid @enderror" id="dosage_form" name="dosage_form" value="{{ old('dosage_form', $product->dosage_form) }}" list="dosage_forms">
                                    <datalist id="dosage_forms">
                                        <option value="Tablet">
                                        <option value="Capsule">
                                        <option value="Syrup">
                                        <option value="Injection">
                                        <option value="Cream">
                                        <option value="Ointment">
                                        <option value="Drops">
                                        <option value="Inhaler">
                                        <option value="Suppository">
                                    </datalist>
                                    @error('dosage_form')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="therapeutic_class" class="form-label">{{ __('messages.therapeutic_class') }}</label>
                                    <input type="text" class="form-control @error('therapeutic_class') is-invalid @enderror" id="therapeutic_class" name="therapeutic_class" value="{{ old('therapeutic_class', $product->therapeutic_class) }}">
                                    @error('therapeutic_class')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="composition" class="form-label">{{ __('messages.composition') }}</label>
                                <textarea class="form-control @error('composition') is-invalid @enderror" id="composition" name="composition" rows="3">{{ old('composition', $product->composition) }}</textarea>
                                @error('composition')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="indications" class="form-label">{{ __('messages.indications') }}</label>
                                <textarea class="form-control @error('indications') is-invalid @enderror" id="indications" name="indications" rows="3">{{ old('indications', $product->indications) }}</textarea>
                                @error('indications')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="contraindications" class="form-label">{{ __('messages.contraindications') }}</label>
                                <textarea class="form-control @error('contraindications') is-invalid @enderror" id="contraindications" name="contraindications" rows="3">{{ old('contraindications', $product->contraindications) }}</textarea>
                                @error('contraindications')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="side_effects" class="form-label">{{ __('messages.side_effects') }}</label>
                                <textarea class="form-control @error('side_effects') is-invalid @enderror" id="side_effects" name="side_effects" rows="3">{{ old('side_effects', $product->side_effects) }}</textarea>
                                @error('side_effects')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save me-1"></i> {{ __('messages.update_product') }}
                        </button>
                        <a href="{{ route('inventory') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}
                        </a>
                    </div>
                    
                    <div class="mt-3">
                        <a href="{{ route('products.batches.index', $product) }}" class="btn btn-info">{{ __('messages.batches') }}</a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Product Status Card -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-1"></i>
                {{ __('messages.product_status') }}
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card {{ $product->needsReordering() ? 'bg-info text-dark' : 'bg-light' }} mb-3">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('messages.stock_status') }}</h5>
                                <p class="card-text">
                                    @if($product->quantity <= 0)
                                        <span class="badge bg-secondary">{{ __('messages.out_of_stock') }}</span>
                                    @elseif($product->needsReordering())
                                        <span class="badge bg-info text-dark">{{ __('messages.low_stock') }} ({{ $product->quantity }} {{ __('messages.remaining') }})</span>
                                    @else
                                        <span class="badge bg-success">{{ __('messages.in_stock') }} ({{ $product->quantity }} {{ __('messages.available') }})</span>
                                    @endif
                                </p>
                                <p class="card-text">
                                    {{ __('messages.reorder_point') }}: {{ $product->reorder_point }}<br>
                                    {{ __('messages.optimal_stock') }}: {{ $product->optimal_stock_level }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card {{ $product->isExpired() ? 'bg-danger text-white' : ($product->isNearExpiry() ? 'bg-warning' : 'bg-light') }} mb-3">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('messages.expiry_status') }}</h5>
                                <p class="card-text">
                                    @if($product->isExpired())
                                        <span class="badge bg-danger">{{ __('messages.expired') }}</span>
                                    @elseif($product->isNearExpiry())
                                        <span class="badge bg-warning text-dark">{{ __('messages.near_expiry') }}</span>
                                    @else
                                        <span class="badge bg-success">{{ __('messages.good') }}</span>
                                    @endif
                                    <br>
                                    {{ __('messages.expiry_date') }}: {{ $product->expiry_date->format('Y-m-d') }}
                                </p>
                                @if($product->manufacturing_date)
                                <p class="card-text">
                                    {{ __('messages.manufacturing_date') }}: {{ $product->manufacturing_date->format('Y-m-d') }}
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('messages.sales_history') }}</h5>
                                <p class="card-text">
                                    {{ __('messages.total_sales') }}: {{ $product->saleItems->count() }} {{ __('messages.units') }}
                                </p>
                                <p class="card-text">
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> {{ __('messages.view_details') }}
                                    </a>
                                </p>
                            </div>
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
        // Initialize Bootstrap tabs
        $('#productTabs a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });
        
        // Set minimum expiry date to today
        const today = new Date().toISOString().split('T')[0];
        $('#manufacturing_date').attr('max', today);
        
        // Validate manufacturing date is before expiry date
        $('#manufacturing_date, #expiry_date').change(function() {
            const mfgDate = $('#manufacturing_date').val();
            const expDate = $('#expiry_date').val();
            
            if (mfgDate && expDate && mfgDate >= expDate) {
                alert('{{ __("messages.manufacturing_date_before_expiry") }}');
                $('#manufacturing_date').val('');
            }
        });
        
        // Validate optimal stock level is greater than reorder point
        $('#reorder_point, #optimal_stock_level').change(function() {
            const reorderPoint = parseInt($('#reorder_point').val()) || 0;
            const optimalStock = parseInt($('#optimal_stock_level').val()) || 0;
            
            if (reorderPoint >= optimalStock) {
                alert('{{ __("messages.reorder_point_must_be_less_than_optimal") }}');
                $('#optimal_stock_level').val(reorderPoint + 15);
            }
        });
        
        // Barcode validation
        $('#barcode').change(function() {
            const barcode = $(this).val();
            if (barcode && barcode.length === 13) {
                // Simple EAN-13 validation
                let sum = 0;
                for (let i = 0; i < 12; i++) {
                    sum += parseInt(barcode[i]) * (i % 2 === 0 ? 1 : 3);
                }
                const checkDigit = (10 - (sum % 10)) % 10;
                
                if (parseInt(barcode[12]) !== checkDigit) {
                    alert('{{ __("messages.invalid_barcode") }}');
                    $(this).val('');
                }
            } else if (barcode && barcode.length !== 13) {
                alert('{{ __("messages.barcode_must_be_13_digits") }}');
                $(this).val('');
            }
        });
        
        // Form validation
        $('#productForm').submit(function(e) {
            const name = $('#name').val().trim();
            const quantity = $('#quantity').val();
            const expiryDate = $('#expiry_date').val();
            const supplier = $('#supplier').val().trim();
            const price = $('#price').val();
            
            if (!name) {
                e.preventDefault();
                alert('{{ __("messages.enter_product_name") }}');
                $('#name').focus();
                return false;
            }
            
            if (quantity === '' || isNaN(quantity) || parseInt(quantity) < 0) {
                e.preventDefault();
                alert('{{ __("messages.enter_valid_quantity") }}');
                $('#quantity').focus();
                return false;
            }
            
            if (!expiryDate) {
                e.preventDefault();
                alert('{{ __("messages.select_expiry_date") }}');
                $('#expiry_date').focus();
                return false;
            }
            
            if (!supplier) {
                e.preventDefault();
                alert('{{ __("messages.enter_supplier_name") }}');
                $('#supplier').focus();
                return false;
            }
            
            if (price === '' || isNaN(price) || parseFloat(price) < 0) {
                e.preventDefault();
                alert('{{ __("messages.enter_valid_price") }}');
                $('#price').focus();
                return false;
            }
            
            // Show loading state
            $('#submitBtn').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __("messages.updating") }}');
            $('#submitBtn').prop('disabled', true);
        });
    });
</script>
@endsection 