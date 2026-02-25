@extends('layouts.app')

@section('title', __('messages.pos_system'))

@section('content')
<div class="pos-container">
    <!-- Header -->
    <div class="pos-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="pos-title">
                <i class="fas fa-cash-register me-2"></i>
                {{ __('messages.pos_system') }}
            </h2>
            <div class="pos-controls">
                <input type="datetime-local" id="saleDate" class="form-control form-control-sm me-2" value="{{ now()->format('Y-m-d\\TH:i') }}">
<span class="badge bg-info">{{ __('messages.pos_system') }}</span>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Panel - Product Search & Cart -->
        <div class="col-lg-8">
            <!-- Barcode Scanner Section -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-barcode me-2"></i>
                        {{ __('messages.barcode_scanner') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="text" class="form-control" id="barcodeInput" 
                                       placeholder="{{ __('messages.scan_or_enter_barcode') }}"
                                       autocomplete="off">
                                <button class="btn btn-primary" id="scanBtn">
                                    <i class="fas fa-search"></i>
                                    {{ __('messages.scan') }}
                                </button>
                            </div>
                        </div>
                        
                    </div>
                    <div id="scanResult" class="mt-3" style="display: none;"></div>
                </div>
            </div>

            <!-- Product Search Section -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-search me-2"></i>
                        {{ __('messages.product_search') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" class="form-control" id="productSearch" 
                               placeholder="{{ __('messages.search_by_name_brand_ingredient') }}"
                               autocomplete="off">
                        <button class="btn btn-outline-primary" id="clearSearchBtn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="searchResults" class="search-results mt-3"></div>
                </div>
            </div>

            <!-- Shopping Cart -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>
                        {{ __('messages.shopping_cart') }}
                        <span class="badge bg-primary ms-2" id="cartItemCount">0</span>
                    </h5>
                    <button class="btn btn-outline-danger btn-sm" id="clearCartBtn">
                        <i class="fas fa-trash"></i>
                        {{ __('messages.clear_cart') }}
                    </button>
                </div>
                <div class="card-body">
                    <div id="cartItems" class="cart-items">
                        <div class="empty-cart text-center text-muted py-4">
                            <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                            <p>{{ __('messages.cart_empty') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel - Customer & Payment -->
        <div class="col-lg-4">
            <!-- Customer Selection -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        {{ __('messages.customer') }}
                    </h5>
                </div>
                <div class="card-body">
                    <select class="form-select" id="customerSelect">
                        <option value="">{{ __('messages.select_customer') }}</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" 
                                    data-points="{{ $customer->loyalty_points }}"
                                    data-phone="{{ $customer->phone }}">
                                {{ $customer->name }}
                                @if($customer->loyalty_points > 0)
                                    ({{ number_format($customer->loyalty_points) }} {{ __('messages.points') }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <div class="mt-2">
                        <button class="btn btn-outline-primary btn-sm" id="addCustomerBtn">
                            <i class="fas fa-plus"></i>
                            {{ __('messages.add_customer') }}
                        </button>
                    </div>
                    <div id="customerInfo" class="mt-3" style="display: none;">
                        <div class="customer-details p-2 bg-light rounded">
                            <div class="d-flex justify-content-between">
                                <span>{{ __('messages.loyalty_points') }}:</span>
                                <span id="customerPoints" class="fw-bold text-primary">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Promotions & Discounts -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tags me-2"></i>
                        {{ __('messages.promotions_discounts') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.promotion_code') }}</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="promotionCode" 
                                   placeholder="{{ __('messages.enter_promo_code') }}">
                            <button class="btn btn-outline-primary" id="applyPromoBtn">
                                {{ __('messages.apply') }}
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.quick_discount') }}</label>
                        <div class="row">
                            <div class="col-6">
                                <select class="form-select" id="discountType">
                                    <option value="percentage">%</option>
                                    <option value="fixed">{{ __('messages.fixed') }}</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <input type="number" class="form-control" id="discountValue" 
                                       placeholder="{{ __('messages.zero_placeholder') }}" min="0" step="0.01">
                            </div>
                        </div>
                        <button class="btn btn-outline-secondary btn-sm mt-2 w-100" id="applyQuickDiscountBtn">
                            {{ __('messages.apply_discount') }}
                        </button>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.loyalty_points_to_use') }}</label>
                        <input type="number" class="form-control" id="loyaltyPointsUse" 
                                placeholder="{{ __('messages.zero_placeholder') }}" min="0" max="0" disabled>
                        <small class="text-muted">{{ __('messages.points_value_info') }}</small>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>
                        {{ __('messages.order_summary') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="order-summary">
                        <div class="d-flex justify-content-between">
                            <span>{{ __('messages.subtotal') }}:</span>
                            <span id="subtotalAmount">0.00</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>{{ __('messages.discount') }}:</span>
                            <span id="discountAmount" class="text-success">-0.00</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>{{ __('messages.loyalty_discount') }}:</span>
                            <span id="loyaltyDiscountAmount" class="text-success">-0.00</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>{{ __('messages.tax') }}:</span>
                            <span id="taxAmount">0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>{{ __('messages.total') }}:</span>
                            <span id="totalAmount" class="text-primary">0.00</span>
                        </div>
                        <div class="mt-2 text-muted small">
                            <span>{{ __('messages.points_to_earn') }}: </span>
                            <span id="pointsToEarn" class="text-success fw-bold">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        {{ __('messages.payment_method') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="payment-methods">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="paymentMethod" 
                                   id="paymentCash" value="cash" checked>
                            <label class="form-check-label" for="paymentCash">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                {{ __('messages.cash') }}
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="paymentMethod" 
                                   id="paymentCard" value="card">
                            <label class="form-check-label" for="paymentCard">
                                <i class="fas fa-credit-card me-2"></i>
                                {{ __('messages.card') }}
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="paymentMethod" 
                                   id="paymentMobile" value="mobile">
                            <label class="form-check-label" for="paymentMobile">
                                <i class="fas fa-mobile-alt me-2"></i>
                                {{ __('messages.mobile_payment') }}
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="paymentMethod" 
                                   id="paymentMixed" value="mixed">
                            <label class="form-check-label" for="paymentMixed">
                                <i class="fas fa-coins me-2"></i>
                                {{ __('messages.mixed_payment') }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card">
                <div class="card-body">
                    <button class="btn btn-success btn-lg w-100 mb-2" id="processPaymentBtn" disabled>
                        <i class="fas fa-check me-2"></i>
                        {{ __('messages.process_payment') }}
                    </button>
                    <div class="row mb-2">
                        <div class="col-6">
                            <button class="btn btn-outline-primary w-100" id="holdSaleBtn">
                                <i class="fas fa-pause"></i>
                                {{ __('messages.hold') }}
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-secondary w-100" id="printReceiptBtn" disabled>
                                <i class="fas fa-print"></i>
                                {{ __('messages.print') }}
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-info w-100" id="useCameraBtn">
                                <i class="fas fa-camera"></i>
                                {{ __('messages.use_camera') }}
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-outline-warning w-100" id="lowStockBtn">
                                <i class="fas fa-exclamation-triangle"></i>
                                {{ __('messages.low_stock_alerts') }}
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-outline-success w-100" id="syncOfflineBtn">
                                <i class="fas fa-sync"></i>
                                {{ __('messages.sync_offline') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('pos.modals.add-customer')
@include('pos.modals.low-stock-alerts')
@include('pos.modals.camera-scanner')
@include('pos.modals.payment-confirmation')

@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/pos.css') }}">
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
<script>window.posScanUrl = "{{ route('pos.scan-barcode') }}";</script>
<script src="{{ asset('js/pos.js') }}"></script>
@endsection
