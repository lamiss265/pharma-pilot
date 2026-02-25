@extends('layouts.app')

@section('title', __('messages.add_new_sale'))

@section('content')
    <div class="container-fluid">
        <h1 class="mt-4 mb-4">{{ __('messages.add_new_sale') }}</h1>
        
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-cash-register me-1"></i>
                {{ __('messages.sale_entry_form') }}
            </div>
            <div class="card-body">
                <form action="{{ route('sales.store') }}" method="POST" id="saleForm">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="product_id" class="form-label">{{ __('messages.product') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="product_id" name="product_id" required>
                                <option value="">{{ __('messages.select_product') }}</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-stock="{{ $product->quantity }}">
                                        {{ $product->name }} (Stock: {{ $product->quantity }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text" id="stockInfo"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="quantity" class="form-label">{{ __('messages.quantity') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                            <div class="invalid-feedback" id="quantityFeedback">
                                Please enter a valid quantity.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="client_id" class="form-label">{{ __('messages.client_optional') }}</label>
                            <div class="input-group">
                                <select class="form-select" id="client_id" name="client_id">
                                    <option value="">{{ __('messages.no_client') }}</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->phone }})</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#addClientModal">
                                    <i class="fas fa-plus"></i> {{ __('messages.new') }}
                                </button>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="sale_date" class="form-label">{{ __('messages.sale_date') }} <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="sale_date" name="sale_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save me-1"></i> {{ __('messages.record_sale') }}
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> {{ __('messages.cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add Client Modal -->
    <div class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addClientModalLabel">{{ __('messages.add_new_client') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addClientForm">
                        @csrf
                        <div class="mb-3">
                            <label for="client_name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="client_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="client_phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="client_phone" name="phone">
                        </div>
                        <div class="mb-3">
                            <label for="client_notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="client_notes" name="notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="saveClientBtn">{{ __('messages.save_client') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Update stock info when product is selected
        $('#product_id').change(function() {
            const selectedOption = $(this).find('option:selected');
            const stockAvailable = selectedOption.data('stock');
            
            if (stockAvailable !== undefined) {
                if (stockAvailable > 10) {
                    $('#stockInfo').html(`<span class="text-success">Stock Available: ${stockAvailable}</span>`);
                } else if (stockAvailable > 0) {
                    $('#stockInfo').html(`<span class="text-warning">Low Stock: ${stockAvailable} remaining</span>`);
                } else {
                    $('#stockInfo').html(`<span class="text-danger">Out of Stock</span>`);
                    $('#submitBtn').prop('disabled', true);
                }
                
                // Set max quantity to available stock
                $('#quantity').attr('max', stockAvailable);
            } else {
                $('#stockInfo').html('');
                $('#quantity').removeAttr('max');
            }
        });
        
        // Validate quantity against available stock
        $('#quantity').on('input', function() {
            const selectedOption = $('#product_id').find('option:selected');
            const stockAvailable = selectedOption.data('stock');
            const quantity = parseInt($(this).val());
            
            if (isNaN(quantity) || quantity <= 0) {
                $(this).addClass('is-invalid');
                $('#quantityFeedback').text('Please enter a valid quantity.');
                $('#submitBtn').prop('disabled', true);
                return;
            }
            
            if (quantity > stockAvailable) {
                $(this).addClass('is-invalid');
                $('#quantityFeedback').text(`Not enough stock. Only ${stockAvailable} available.`);
                $('#submitBtn').prop('disabled', true);
            } else {
                $(this).removeClass('is-invalid');
                $('#submitBtn').prop('disabled', false);
            }
        });
        
        // Form validation before submit
        $('#saleForm').submit(function(e) {
            const productId = $('#product_id').val();
            const quantity = $('#quantity').val();
            
            if (!productId) {
                e.preventDefault();
                alert('Please select a product.');
                return false;
            }
            
            if (!quantity || quantity <= 0) {
                e.preventDefault();
                alert('Please enter a valid quantity.');
                return false;
            }
            
            const selectedOption = $('#product_id').find('option:selected');
            const stockAvailable = selectedOption.data('stock');
            
            if (parseInt(quantity) > stockAvailable) {
                e.preventDefault();
                alert(`Not enough stock. Only ${stockAvailable} available.`);
                return false;
            }
            
            // Show loading state
            $('#submitBtn').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
            $('#submitBtn').prop('disabled', true);
        });
        
        // Handle adding a new client
        $('#saveClientBtn').click(function() {
            const formData = $('#addClientForm').serialize();
            
            // Disable button and show loading state
            $(this).prop('disabled', true);
            $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
            
            $.ajax({
                url: '{{ route("clients.store") }}',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Add the new client to the dropdown
                        const newOption = new Option(
                            `${response.client.name} (${response.client.phone || 'No Phone'})`, 
                            response.client.id, 
                            true, 
                            true
                        );
                        $('#client_id').append(newOption).trigger('change');
                        
                        // Close the modal and reset the form
                        $('#addClientModal').modal('hide');
                        $('#addClientForm')[0].reset();
                        
                        // Show success message
                        alert('Client added successfully!');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while saving the client.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessage = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    }
                    
                    alert(errorMessage);
                },
                complete: function() {
                    // Reset button state
                    $('#saveClientBtn').prop('disabled', false);
                    $('#saveClientBtn').html('{{ __('messages.save_client') }}');
                }
            });
        });
    });
</script>
@endsection 