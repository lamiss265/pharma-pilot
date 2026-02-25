@extends('layouts.app')

@section('title', __('messages.advanced_sales'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-barcode me-2"></i>
                    {{ __('messages.product_scanner') }}
                </div>
                <div class="card-body">
                    <!-- Barcode Scanner Section -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-barcode"></i>
                                </span>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="barcodeInput" 
                                       placeholder="{{ __('messages.scan_or_enter_barcode') }}"
                                       autocomplete="off">
                                <button class="btn btn-primary" type="button" id="scanBtn">
                                    <i class="fas fa-camera me-1"></i>
                                    {{ __('messages.scan') }}
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-outline-secondary w-100" id="bulkScanBtn">
                                <i class="fas fa-layer-group me-1"></i>
                                {{ __('messages.bulk_scan') }}
                            </button>
                        </div>
                    </div>

                    <!-- Product Search Section -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="productSearch" 
                                       placeholder="{{ __('messages.search_by_name_category_ingredient') }}">
                                <select class="form-select" id="searchType" style="max-width: 150px;">
                                    <option value="name">{{ __('messages.name') }}</option>
                                    <option value="ingredient">{{ __('messages.ingredient') }}</option>
                                    <option value="barcode">{{ __('messages.barcode') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-info w-100" id="manualEntryBtn">
                                <i class="fas fa-keyboard me-1"></i>
                                {{ __('messages.manual_entry') }}
                            </button>
                        </div>
                    </div>

                    <!-- Search Results -->
                    <div id="searchResults" class="mt-3" style="display: none;">
                        <div class="list-group" id="searchResultsList"></div>
                    </div>
                </div>
            </div>

            <!-- Shopping Cart -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-shopping-cart me-2"></i>
                        {{ __('messages.shopping_cart') }}
                    </span>
                    <span class="badge bg-primary" id="cartItemCount">0</span>
                </div>
                <div class="card-body">
                    <div id="cartItems">
                        <div class="text-center text-muted py-4" id="emptyCart">
                            <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                            <p>{{ __('messages.cart_is_empty') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkout Panel -->
        <div class="col-lg-4">
            <div class="card sticky-top">
                <div class="card-header">
                    <i class="fas fa-calculator me-2"></i>
                    {{ __('messages.checkout') }}
                </div>
                <div class="card-body">
                    <!-- Customer Selection -->
                    <div class="mb-3">
                        <label for="customerSelect" class="form-label">{{ __('messages.customer') }}</label>
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   id="customerSearch" 
                                   placeholder="{{ __('messages.search_customer') }}">
                            <button class="btn btn-outline-secondary" type="button" id="newCustomerBtn">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div id="selectedCustomer" class="mt-2" style="display: none;">
                            <div class="alert alert-info py-2">
                                <small>
                                    <strong id="customerName"></strong><br>
                                    <span id="customerTier"></span> | 
                                    <span id="customerPoints"></span> {{ __('messages.points') }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Promotion Code -->
                    <div class="mb-3">
                        <label for="promotionCode" class="form-label">{{ __('messages.promotion_code') }}</label>
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   id="promotionCode" 
                                   placeholder="{{ __('messages.enter_promo_code') }}">
                            <button class="btn btn-outline-primary" type="button" id="applyPromoBtn">
                                {{ __('messages.apply') }}
                            </button>
                        </div>
                        <div id="promoStatus" class="mt-1"></div>
                    </div>

                    <!-- Prescription Details -->
                    <div class="mb-3" id="prescriptionSection" style="display: none;">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="isPrescription">
                            <label class="form-check-label" for="isPrescription">
                                {{ __('messages.prescription_sale') }}
                            </label>
                        </div>
                        <input type="text" 
                               class="form-control" 
                               id="prescriptionNumber" 
                               placeholder="{{ __('messages.prescription_number') }}" 
                               style="display: none;">
                    </div>

                    <!-- Order Summary -->
                    <div class="bg-light p-3 rounded mb-3">
                        <div class="d-flex justify-content-between">
                            <span>{{ __('messages.subtotal') }}:</span>
                            <span id="subtotalAmount">0.00</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>{{ __('messages.discount') }}:</span>
                            <span id="discountAmount" class="text-success">-0.00</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>{{ __('messages.tax') }}:</span>
                            <span id="taxAmount">0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>{{ __('messages.total') }}:</span>
                            <span id="totalAmount">0.00</span>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.payment_method') }}</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="paymentMethod" id="cashPayment" value="cash" checked>
                            <label class="btn btn-outline-primary" for="cashPayment">
                                <i class="fas fa-money-bill-wave me-1"></i>
                                {{ __('messages.cash') }}
                            </label>
                            
                            <input type="radio" class="btn-check" name="paymentMethod" id="cardPayment" value="card">
                            <label class="btn btn-outline-primary" for="cardPayment">
                                <i class="fas fa-credit-card me-1"></i>
                                {{ __('messages.card') }}
                            </label>
                            
                            <input type="radio" class="btn-check" name="paymentMethod" id="mobilePayment" value="mobile">
                            <label class="btn btn-outline-primary" for="mobilePayment">
                                <i class="fas fa-mobile-alt me-1"></i>
                                {{ __('messages.mobile') }}
                            </label>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="saleNotes" class="form-label">{{ __('messages.notes') }}</label>
                        <textarea class="form-control" id="saleNotes" rows="2" placeholder="{{ __('messages.optional_notes') }}"></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <button class="btn btn-success btn-lg" id="checkoutBtn" disabled>
                            <i class="fas fa-cash-register me-2"></i>
                            {{ __('messages.complete_sale') }}
                        </button>
                        <button class="btn btn-outline-danger" id="clearCartBtn">
                            <i class="fas fa-trash me-2"></i>
                            {{ __('messages.clear_cart') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('sales.modals.customer-modal')
@include('sales.modals.product-modal')
@include('sales.modals.receipt-modal')
@include('sales.modals.scanner-modal')

@endsection

@section('styles')
<style>
.product-item {
    transition: all 0.2s;
}

.product-item:hover {
    background-color: var(--bs-gray-100);
    cursor: pointer;
}

.cart-item {
    border-bottom: 1px solid var(--bs-gray-200);
    padding: 10px 0;
}

.cart-item:last-child {
    border-bottom: none;
}

.near-expiry {
    background-color: #fff3cd;
    border-color: #ffeaa7;
}

.prescription-required {
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.loyalty-badge {
    font-size: 0.7em;
    padding: 2px 6px;
}

.sticky-top {
    top: 20px;
}

#searchResults {
    max-height: 300px;
    overflow-y: auto;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 5px;
}

.quantity-controls button {
    width: 25px;
    height: 25px;
    padding: 0;
    font-size: 12px;
}

.warning-indicator {
    font-size: 0.8rem;
    margin-left: 8px;
    cursor: pointer;
}
</style>
@endsection

@section('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
$(document).ready(function() {
    let cart = [];
    let selectedCustomer = null;
    let appliedPromotion = null;

    // Initialize
    updateCartDisplay();
    updateCheckoutButton();

    // Barcode input handler
    $('#barcodeInput').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            scanBarcode($(this).val());
            $(this).val('');
        }
    });

    // Scan button
    $('#scanBtn').click(function() {
        const barcode = $('#barcodeInput').val().trim();
                // Instead of reading from input, open the scanner modal
        $('#scannerModal').modal('show');
    });

    // Product search
    let searchTimeout;
    $('#productSearch').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();
        const type = $('#searchType').val();
        
        if (query.length >= 2) {
            searchTimeout = setTimeout(() => {
                searchProducts(query, type);
            }, 300);
        } else {
            $('#searchResults').hide();
        }
    });

    // Customer search
    let customerSearchTimeout;
    $('#customerSearch').on('input', function() {
        clearTimeout(customerSearchTimeout);
        const query = $(this).val().trim();
        
        if (query.length >= 2) {
            customerSearchTimeout = setTimeout(() => {
                searchCustomers(query);
            }, 300);
        }
    });

    // Apply promotion
    $('#applyPromoBtn').click(function() {
        const code = $('#promotionCode').val().trim();
        if (code) {
            applyPromotion(code);
        }
    });

    // Prescription checkbox
    $('#isPrescription').change(function() {
        if ($(this).is(':checked')) {
            $('#prescriptionNumber').show().focus();
        } else {
            $('#prescriptionNumber').hide().val('');
        }
    });

    // Checkout button
    $('#checkoutBtn').click(function() {
        if (cart.length > 0) {
            processCheckout();
        }
    });

    // Clear cart
    $('#clearCartBtn').click(function() {
        if (confirm('{{ __("messages.confirm_clear_cart") }}')) {
            clearCart();
        }
    });

    // Functions
        // Event listener for when the scanner modal is shown
    $('#scannerModal').on('shown.bs.modal', function () {
        startScanner();
    });

    // Event listener for when the scanner modal is hidden
    $('#scannerModal').on('hidden.bs.modal', function () {
        stopScanner();
    });

    let html5QrCode = null;

    function startScanner() {
        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("reader");
        }

        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
            $('#scannerModal').modal('hide');
            $('#barcodeInput').val(decodedText); // Optionally populate the input
            scanBarcode(decodedText);
        };

        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

        html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback)
            .catch(err => {
                console.error(`Unable to start scanning, error: ${err}`);
                $('#scannerModal').modal('hide');
                showToast('{{ __('messages.scanner_error_start') }}', 'error');
            });
    }

    function stopScanner() {
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().then(ignore => {
                // QR Code scanning is stopped.
            }).catch(err => {
                console.error(`Unable to stop scanning, error: ${err}`);
            });
        }
    }

    function scanBarcode(barcode) {
        $.ajax({
            url: '{{ route("sales.scan-barcode") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                barcode: barcode
            },
            success: function(response) {
                if (response.found) {
                    addToCart(response.product);
                    showToast('{{ __("messages.product_added_to_cart") }}', 'success');
                } else {
                    showToast(response.message, 'warning');
                    // Show manual entry modal
                    showManualEntryModal(barcode);
                }
            },
            error: function() {
                showToast('{{ __("messages.scan_error") }}', 'error');
            }
        });
    }

    function searchProducts(query, type) {
        $.ajax({
            url: '{{ route("sales.search-products") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                query: query,
                type: type
            },
            success: function(products) {
                displaySearchResults(products);
            }
        });
    }

    function displaySearchResults(products) {
        const resultsHtml = products.map(product => {
            const totalStock = product.batches.reduce((sum, batch) => sum + batch.quantity_remaining, 0);
            const isLowStock = totalStock <= (product.minimum_stock || 0);
            return `
                <div class="list-group-item product-item ${product.near_expiry ? 'near-expiry' : ''}" 
                     data-product='${JSON.stringify(product)}'>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${product.name}</h6>
                            <small class="text-muted">
                                ${product.category} | {{ __('messages.stock') }}: ${totalStock}
                                ${product.near_expiry ? ` <span class="badge bg-warning">{{ __('messages.near_expiry') }}</span>` : ''}
                                ${isLowStock ? ` <span class="badge bg-danger">{{ __('messages.low_stock') }}</span>` : ''}
                            </small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">$${product.price}</div>
                            <small class="text-muted">${product.barcode || 'No barcode'}</small>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        $('#searchResultsList').html(resultsHtml);
        $('#searchResults').show();

        // Add click handlers
        $('.product-item').click(function() {
            const product = JSON.parse($(this).attr('data-product'));
            addToCart(product);
            $('#searchResults').hide();
            $('#productSearch').val('');
        });
    }

    function addToCart(product, quantity = 1, batchId = null) {
        const existingIndex = cart.findIndex(item => 
            item.product_id === product.id && item.batch_id === batchId
        );

        if (existingIndex !== -1) {
            cart[existingIndex].quantity += quantity;
        } else {
            const totalStock = product.batches.reduce((sum, batch) => sum + batch.quantity_remaining, 0);
            const isLowStock = totalStock <= (product.minimum_stock || 0);
            cart.push({
                id: product.id,
                name: product.name,
                price: product.selling_price,
                quantity: quantity,
                max_quantity: totalStock,
                image: product.image_url,
                near_expiry: product.near_expiry,
                low_stock: isLowStock
            });
        }

        updateCartDisplay();
        updateTotals();
{{ ... }}
        } else {
            const cartHtml = cart.map((item, index) => `
                <div class="cart-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                            ${item.name}
                            ${item.low_stock ? `<span class="warning-indicator text-warning" data-bs-toggle="tooltip" title="{{ __('messages.low_stock_warning') }}"><i class="fas fa-exclamation-triangle"></i></span>` : ''}
                            ${item.near_expiry ? `<span class="warning-indicator text-danger" data-bs-toggle="tooltip" title="{{ __('messages.near_expiry_warning') }}"><i class="fas fa-calendar-times"></i></span>` : ''}
                        </h6>
                            <small class="text-muted">
                                $${item.product.price} each
                                ${item.product.near_expiry ? '<span class="badge bg-warning ms-1">Near Expiry</span>' : ''}
                            </small>
                        </div>
{{ ... }}
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div class="quantity-controls">
                            <button class="btn btn-sm btn-outline-secondary" onclick="updateCartQuantity(${index}, ${item.quantity - 1})">-</button>
                            <span class="mx-2">${item.quantity}</span>
                            <button class="btn btn-sm btn-outline-secondary" onclick="updateCartQuantity(${index}, ${item.quantity + 1})">+</button>
                        </div>
                        <div class="fw-bold">$${(item.product.price * item.quantity).toFixed(2)}</div>
                    </div>
                </div>
            `).join('');

            cartContainer.html(cartHtml);
        }

        updateCheckoutButton();
    }

    function updateTotals() {
        if (cart.length === 0) {
            $('#subtotalAmount').text('0.00');
            $('#discountAmount').text('-0.00');
            $('#taxAmount').text('0.00');
            $('#totalAmount').text('0.00');
            return;
        }

        $.ajax({
            url: '{{ route("sales.calculate-cart") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                items: cart,
                customer_id: selectedCustomer?.id,
                promotion_code: $('#promotionCode').val()
            },
            success: function(response) {
                $('#subtotalAmount').text(response.subtotal);
                $('#discountAmount').text('-' + response.discount_amount);
                $('#taxAmount').text(response.tax_amount);
                $('#totalAmount').text(response.final_amount);

                if (response.applied_promotion) {
                    $('#promoStatus').html(`
                        <small class="text-success">
                            <i class="fas fa-check"></i> ${response.applied_promotion}
                        </small>
                    `);
                }
            }
        });
    }

    function checkPrescriptionRequired() {
        const hasRxProduct = cart.some(item => item.prescription_required);
        if (hasRxProduct) {
            $('#prescriptionSection').show();
        } else {
            $('#prescriptionSection').hide();
            $('#isPrescription').prop('checked', false);
            $('#prescriptionNumber').hide().val('');
        }
    }

    function updateCheckoutButton() {
        $('#checkoutBtn').prop('disabled', cart.length === 0);
    }

    function processCheckout() {
        const data = {
            _token: '{{ csrf_token() }}',
            items: cart,
            customer_id: selectedCustomer?.id,
            promotion_code: $('#promotionCode').val(),
            payment_method: $('input[name="paymentMethod"]:checked').val(),
            notes: $('#saleNotes').val(),
            is_prescription: $('#isPrescription').is(':checked'),
            prescription_number: $('#prescriptionNumber').val()
        };

        // Validate prescription if required
        if ($('#isPrescription').is(':checked') && !$('#prescriptionNumber').val().trim()) {
            showToast('{{ __("messages.prescription_number_required") }}', 'warning');
            return;
        }

        $.ajax({
            url: '{{ route("sales.process-advanced") }}',
            method: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    
                    // Generate receipt
                    generateReceipt(response.sale_id);
                    
                    // Clear cart
                    clearCart();
                    
                    // Reset form
                    resetForm();
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showToast(response.message || '{{ __("messages.checkout_error") }}', 'error');
            }
        });
    }

    function generateReceipt(saleId) {
        $.ajax({
            url: `/sales/receipt/${saleId}`,
            method: 'GET',
            success: function(response) {
                showReceiptModal(response);
            }
        });
    }

    function clearCart() {
        cart = [];
        updateCartDisplay();
        updateTotals();
        checkPrescriptionRequired();
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        updateCartDisplay();
        updateTotals();
        checkPrescriptionRequired();
    }

    function updateCartQuantity(index, newQuantity) {
        if (newQuantity <= 0) {
            removeFromCart(index);
        } else if (newQuantity <= cart[index].max_quantity) {
            cart[index].quantity = newQuantity;
            updateCartDisplay();
            updateTotals();
        } else {
            showToast('{{ __('messages.insufficient_stock') }}', 'warning');
        }
    }

    function updateCartDisplay() {
        const cartContainer = $('#cartItems');
        $('#cartItemCount').text(cart.length);

        if (cart.length === 0) {
            cartContainer.html(`
                <div class="text-center text-muted py-4" id="emptyCart">
                    <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                    <p>{{ __('messages.cart_is_empty') }}</p>
                </div>
            `);
        } else {
            const cartHtml = cart.map((item, index) => `
                <div class="cart-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                ${item.name}
                                ${item.low_stock ? `<span class="warning-indicator text-warning" data-bs-toggle="tooltip" title="{{ __('messages.low_stock_warning') }}"><i class="fas fa-exclamation-triangle"></i></span>` : ''}
                                ${item.near_expiry ? `<span class="warning-indicator text-danger" data-bs-toggle="tooltip" title="{{ __('messages.near_expiry_warning') }}"><i class="fas fa-calendar-times"></i></span>` : ''}
                            </h6>
                            <small class="text-muted">$${item.price.toFixed(2)}</small>
                        </div>
                        <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div class="quantity-controls">
                            <button class="btn btn-sm btn-outline-secondary" onclick="updateCartQuantity(${index}, ${item.quantity - 1})">-</button>
                            <span class="mx-2">${item.quantity}</span>
                            <button class="btn btn-sm btn-outline-secondary" onclick="updateCartQuantity(${index}, ${item.quantity + 1})">+</button>
                        </div>
                        <div class="fw-bold">$${(item.price * item.quantity).toFixed(2)}</div>
                    </div>
                </div>
            `).join('');

            cartContainer.html(cartHtml);
            
            // Re-initialize tooltips for new cart items
            $('[data-bs-toggle="tooltip"]').tooltip();
        }

        updateCheckoutButton();
    }

    function resetForm() {
        selectedCustomer = null;
        appliedPromotion = null;
        $('#customerSearch').val('');
        $('#selectedCustomer').hide();
        $('#promotionCode').val('');
        $('#promoStatus').empty();
        $('#saleNotes').val('');
        $('#isPrescription').prop('checked', false);
        $('#prescriptionNumber').hide().val('');
        $('input[name="paymentMethod"][value="cash"]').prop('checked', true);
    }

    function showToast(message, type) {
        // Implement toast notification
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'warning' ? 'alert-warning' : 'alert-danger';
        
        const toast = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append(toast);
        setTimeout(() => toast.alert('close'), 3000);
    }

    // Make functions globally available
    window.removeFromCart = removeFromCart;
    window.updateCartQuantity = updateCartQuantity;
});
</script>
@endsection
