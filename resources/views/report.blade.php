@extends('layouts.app')

@section('title', __('messages.reports'))

@section('content')
    <div class="container-fluid">
        <h1 class="mt-4 mb-4">{{ __('messages.reports_list') }}</h1>
        
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-bar me-1"></i>
                {{ __('messages.generate_report') }}
            </div>
            <div class="card-body">
                <form action="{{ route('reports.generate') }}" method="POST" id="reportForm">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="report_type" class="form-label">{{ __('messages.report_type') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="report_type" name="report_type" required>
                                <option value="">{{ __('messages.select_report_type') }}</option>
                                <option value="sales">{{ __('messages.sales_report') }}</option>
                                <option value="inventory">{{ __('messages.inventory_report') }}</option>
                                <option value="expiry">{{ __('messages.expiry_report') }}</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">{{ __('messages.start_date') }} <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ date('Y-m-d', strtotime('-30 days')) }}" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">{{ __('messages.end_date') }} <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary" id="generateBtn">
                            <i class="fas fa-chart-line me-1"></i> {{ __('messages.generate_report') }}
                        </button>
                        
                        <button type="submit" class="btn btn-success" id="exportBtn" name="export" value="csv">
                            <i class="fas fa-file-csv me-1"></i> {{ __('messages.export_csv') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-1"></i>
                {{ __('messages.report_types_info') }}
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('messages.sales_report') }}</h5>
                                <p class="card-text">
                                    {{ __('messages.sales_report_desc') }}
                                </p>
                                <ul>
                                    <li>{{ __('messages.total_sales_count') }}</li>
                                    <li>{{ __('messages.total_quantity_sold') }}</li>
                                    <li>{{ __('messages.sales_by_product') }}</li>
                                    <li>{{ __('messages.sales_by_client') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('messages.inventory_report') }}</h5>
                                <p class="card-text">
                                    {{ __('messages.inventory_report_desc') }}
                                </p>
                                <ul>
                                    <li>{{ __('messages.total_products_count') }}</li>
                                    <li>{{ __('messages.total_stock_quantity') }}</li>
                                    <li>{{ __('messages.low_stock_items') }}</li>
                                    <li>{{ __('messages.out_of_stock_items') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('messages.expiry_report') }}</h5>
                                <p class="card-text">
                                    {{ __('messages.expiry_report_desc') }}
                                </p>
                                <ul>
                                    <li>{{ __('messages.expired_products') }}</li>
                                    <li>{{ __('messages.near_expiry_products') }}</li>
                                    <li>{{ __('messages.expiry_by_supplier') }}</li>
                                    <li>{{ __('messages.expiry_timeline') }}</li>
                                </ul>
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
        // Show/hide date fields based on report type
        $('#report_type').change(function() {
            const reportType = $(this).val();
            
            if (reportType === 'inventory') {
                // Hide date fields for inventory reports but don't disable them
                $('#start_date, #end_date').closest('.col-md-4').fadeOut();
            } else {
                $('#start_date, #end_date').closest('.col-md-4').fadeIn();
            }
        });
        
        // Trigger the change event on page load to set initial state
        $('#report_type').trigger('change');
        
        // Validate dates
        $('#end_date').change(function() {
            const startDate = new Date($('#start_date').val());
            const endDate = new Date($(this).val());
            
            if (endDate < startDate) {
                alert('{{ __("messages.end_date_error") }}');
                $(this).val($('#start_date').val());
            }
        });
        
        // Form validation
        $('#reportForm').submit(function(e) {
            const reportType = $('#report_type').val();
            
            if (!reportType) {
                e.preventDefault();
                alert('{{ __("messages.select_report_type_error") }}');
                return false;
            }
            
            if (reportType !== 'inventory') {
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();
                
                if (!startDate || !endDate) {
                    e.preventDefault();
                    alert('{{ __("messages.select_dates_error") }}');
                    return false;
                }
            }
            
            // Show loading state for the clicked button
            $(document.activeElement).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __("messages.processing") }}');
            $(document.activeElement).prop('disabled', true);
        });
        
        // Handle export button click
        $('#exportBtn').click(function() {
            $('#reportForm').append('<input type="hidden" name="export" value="csv">');
        });
    });
</script>
@endsection