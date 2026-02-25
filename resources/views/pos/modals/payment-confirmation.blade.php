<!-- Payment Confirmation Modal -->
<div class="modal fade" id="paymentConfirmationModal" tabindex="-1" aria-labelledby="paymentConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="paymentConfirmationModalLabel">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ __('messages.payment_confirmation') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="success-icon mb-3">
                        <i class="fas fa-check-circle fa-4x text-success"></i>
                    </div>
                    <h4 class="text-success">{{ __('messages.payment_successful') }}</h4>
                    <p class="text-muted">{{ __('messages.transaction_completed') }}</p>
                </div>
                
                <!-- Payment Summary -->
                <div class="payment-summary">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="summary-item">
                                <label>{{ __('messages.receipt_number') }}:</label>
                                <span id="confirmationReceiptNumber" class="fw-bold"></span>
                            </div>
                            <div class="summary-item">
                                <label>{{ __('messages.payment_method') }}:</label>
                                <span id="confirmationPaymentMethod" class="fw-bold"></span>
                            </div>
                            <div class="summary-item">
                                <label>{{ __('messages.customer') }}:</label>
                                <span id="confirmationCustomer" class="fw-bold"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="summary-item">
                                <label>{{ __('messages.total_amount') }}:</label>
                                <span id="confirmationTotalAmount" class="fw-bold text-success"></span>
                            </div>
                            <div class="summary-item">
                                <label>{{ __('messages.payment_received') }}:</label>
                                <span id="confirmationPaymentReceived" class="fw-bold"></span>
                            </div>
                            <div class="summary-item">
                                <label>{{ __('messages.change_due') }}:</label>
                                <span id="confirmationChangeDue" class="fw-bold"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Loyalty Points -->
                    <div class="loyalty-info mt-3" id="confirmationLoyaltyInfo" style="display: none;">
                        <div class="alert alert-info">
                            <i class="fas fa-star me-2"></i>
                            <span id="confirmationLoyaltyText"></span>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="action-buttons mt-4">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <button type="button" class="btn btn-primary w-100" id="printReceiptBtn">
                                <i class="fas fa-print me-2"></i>
                                {{ __('messages.print_receipt') }}
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-info w-100" id="emailReceiptBtn">
                                <i class="fas fa-envelope me-2"></i>
                                {{ __('messages.email_receipt') }}
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-warning w-100" id="smsReceiptBtn">
                                <i class="fas fa-sms me-2"></i>
                                {{ __('messages.sms_receipt') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('messages.close') }}
                </button>
                <button type="button" class="btn btn-success" id="newTransactionBtn">
                    <i class="fas fa-plus me-2"></i>
                    {{ __('messages.new_transaction') }}
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.payment-summary {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-item label {
    color: #6c757d;
    margin-bottom: 0;
}

.success-icon {
    animation: successPulse 1.5s ease-in-out;
}

@keyframes successPulse {
    0% { transform: scale(0.8); opacity: 0.5; }
    50% { transform: scale(1.1); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
}

.action-buttons .btn {
    transition: all 0.3s ease;
}

.action-buttons .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentConfirmationModal = new bootstrap.Modal(document.getElementById('paymentConfirmationModal'));
    let currentSaleData = null;
    
    // Show payment confirmation
    window.showPaymentConfirmation = function(saleData) {
        currentSaleData = saleData;
        populateConfirmationData(saleData);
        paymentConfirmationModal.show();
    };
    
    // Print receipt
    document.getElementById('printReceiptBtn').addEventListener('click', function() {
        if (currentSaleData && currentSaleData.receipt_url) {
            window.open(currentSaleData.receipt_url, '_blank');
        }
    });
    
    // Email receipt
    document.getElementById('emailReceiptBtn').addEventListener('click', async function() {
        if (!currentSaleData) return;
        
        const btn = this;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>{{ __("messages.sending") }}';
        btn.disabled = true;
        
        try {
            const response = await fetch(`{{ route("pos.email-receipt") }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    sale_id: currentSaleData.id
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('{{ __("messages.email_sent_successfully") }}', 'success');
            } else {
                showNotification('{{ __("messages.email_send_failed") }}', 'error');
            }
        } catch (error) {
            console.error('Email receipt error:', error);
            showNotification('{{ __("messages.email_send_failed") }}', 'error');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
    
    // SMS receipt
    document.getElementById('smsReceiptBtn').addEventListener('click', async function() {
        if (!currentSaleData) return;
        
        const btn = this;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>{{ __("messages.sending") }}';
        btn.disabled = true;
        
        try {
            const response = await fetch(`{{ route("pos.sms-receipt") }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    sale_id: currentSaleData.id
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('{{ __("messages.sms_sent_successfully") }}', 'success');
            } else {
                showNotification('{{ __("messages.sms_send_failed") }}', 'error');
            }
        } catch (error) {
            console.error('SMS receipt error:', error);
            showNotification('{{ __("messages.sms_send_failed") }}', 'error');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
    
    // New transaction
    document.getElementById('newTransactionBtn').addEventListener('click', function() {
        paymentConfirmationModal.hide();
        if (window.posSystem) {
            window.posSystem.clearCart();
            window.posSystem.resetForm();
        }
        // Reset customer selection
        document.getElementById('customerSelect').value = '';
        // Clear any promotions
        document.getElementById('promoCodeInput').value = '';
        document.getElementById('loyaltyPointsInput').value = '';
        // Focus on barcode input
        document.getElementById('barcodeInput').focus();
    });
    
    function populateConfirmationData(saleData) {
        document.getElementById('confirmationReceiptNumber').textContent = saleData.receipt_number || '';
        document.getElementById('confirmationPaymentMethod').textContent = saleData.payment_method || '';
        document.getElementById('confirmationCustomer').textContent = saleData.customer_name || '{{ __("messages.walk_in_customer") }}';
        document.getElementById('confirmationTotalAmount').textContent = formatCurrency(saleData.final_amount || 0);
        document.getElementById('confirmationPaymentReceived').textContent = formatCurrency(saleData.payment_received || 0);
        document.getElementById('confirmationChangeDue').textContent = formatCurrency(saleData.change_due || 0);
        
        // Loyalty points info
        if (saleData.loyalty_points_earned > 0) {
            document.getElementById('confirmationLoyaltyText').textContent = 
                `{{ __('messages.loyalty_points_earned') }}: ${saleData.loyalty_points_earned} {{ __('messages.points') }}`;
            document.getElementById('confirmationLoyaltyInfo').style.display = 'block';
        } else {
            document.getElementById('confirmationLoyaltyInfo').style.display = 'none';
        }
    }
    
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'MAD'
        }).format(amount);
    }
    
    function showNotification(message, type) {
        // You can implement your notification system here
        // For now, using a simple alert
        if (type === 'success') {
            alert('✓ ' + message);
        } else {
            alert('✗ ' + message);
        }
    }
});
</script>
