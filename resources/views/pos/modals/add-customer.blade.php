<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCustomerModalLabel">
                    <i class="fas fa-user-plus me-2"></i>
                    {{ __('messages.add_customer') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCustomerForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customerName" class="form-label">{{ __('messages.name') }} *</label>
                                <input type="text" class="form-control" id="customerName" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customerPhone" class="form-label">{{ __('messages.phone') }}</label>
                                <input type="tel" class="form-control" id="customerPhone" name="phone">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customerEmail" class="form-label">{{ __('messages.email') }}</label>
                                <input type="email" class="form-control" id="customerEmail" name="email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customerGender" class="form-label">{{ __('messages.gender') }}</label>
                                <select class="form-select" id="customerGender" name="gender">
                                    <option value="">{{ __('messages.select') }}</option>
                                    <option value="male">{{ __('messages.male') }}</option>
                                    <option value="female">{{ __('messages.female') }}</option>
                                    <option value="other">{{ __('messages.other') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customerDateOfBirth" class="form-label">{{ __('messages.date_of_birth') }}</label>
                                <input type="date" class="form-control" id="customerDateOfBirth" name="date_of_birth">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customerLanguage" class="form-label">{{ __('messages.preferred_language') }}</label>
                                <select class="form-select" id="customerLanguage" name="preferred_language">
                                    <option value="en">{{ __('messages.english') }}</option>
                                    <option value="fr">{{ __('messages.french') }}</option>
                                    <option value="ar">{{ __('messages.arabic') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="customerAddress" class="form-label">{{ __('messages.address') }}</label>
                        <textarea class="form-control" id="customerAddress" name="address" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="emailNotifications" name="email_notifications" checked>
                                <label class="form-check-label" for="emailNotifications">
                                    {{ __('messages.email_notifications') }}
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="smsNotifications" name="sms_notifications">
                                <label class="form-check-label" for="smsNotifications">
                                    {{ __('messages.sms_notifications') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        {{ __('messages.save_customer') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addCustomerForm = document.getElementById('addCustomerForm');
    const addCustomerModal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
    
    // Add customer button click
    document.getElementById('addCustomerBtn').addEventListener('click', function() {
        addCustomerModal.show();
    });
    
    // Form submission
    addCustomerForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
                // Convert checkbox values to booleans
                data.email_notifications = document.getElementById('emailNotifications').checked;
                data.sms_notifications = document.getElementById('smsNotifications').checked;
        
        try {
            const response = await fetch('{{ route("customers.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();

        if (!response.ok) {
            // Show validation or server errors
            const messages = result.errors ? Object.values(result.errors).flat() : [result.message || '{{ __("messages.error_adding_customer") }}'];
            const messageText = messages.join('\n');
            if (window.posSystem && typeof window.posSystem.showMessage === 'function') {
                window.posSystem.showMessage(messageText, 'error');
            } else {
                alert(messageText);
            }
            return;
        }
            
            if (result.success) {
                // Add customer to select dropdown
                const customerSelect = document.getElementById('customerSelect');
                const option = new Option(
                    `${result.customer.name} (${result.customer.loyalty_points || 0} points)`,
                    result.customer.id
                );
                option.dataset.points = result.customer.loyalty_points || 0;
                option.dataset.phone = result.customer.phone || '';
                customerSelect.add(option);
                customerSelect.value = result.customer.id;
                
                // Trigger customer selection
                if (window.posSystem && typeof window.posSystem.selectCustomer === 'function') {
                    window.posSystem.selectCustomer(result.customer.id);
                }
                
                // Close modal and reset form
                addCustomerModal.hide();
                addCustomerForm.reset();
                
                if (window.posSystem && typeof window.posSystem.showMessage === 'function') {
                    window.posSystem.showMessage('{{ __("messages.customer_added_successfully") }}', 'success');
                } else {
                    alert('{{ __("messages.customer_added_successfully") }}');
                }
            } else {
                if (window.posSystem && typeof window.posSystem.showMessage === 'function') {
                    window.posSystem.showMessage(result.message || '{{ __("messages.error_adding_customer") }}', 'error');
                } else {
                    alert(result.message || '{{ __("messages.error_adding_customer") }}');
                }
            }
        } catch (error) {
            console.error('Add customer error:', error);
            if (window.posSystem && typeof window.posSystem.showMessage === 'function') {
                window.posSystem.showMessage('{{ __("messages.error_adding_customer") }}', 'error');
            } else {
                alert('{{ __("messages.error_adding_customer") }}');
            }
        }
    });
});
</script>
