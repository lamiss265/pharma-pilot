<!-- Low Stock Alerts Modal -->
<div class="modal fade" id="lowStockModal" tabindex="-1" aria-labelledby="lowStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lowStockModalLabel">
                    <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                    {{ __('messages.low_stock_alerts') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="lowStockContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">{{ __('messages.loading') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('messages.close') }}
                </button>
                <button type="button" class="btn btn-primary" id="refreshAlertsBtn">
                    <i class="fas fa-sync-alt me-2"></i>
                    {{ __('messages.refresh') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// DISABLED FOR TESTING
if (false) {
document.addEventListener('DOMContentLoaded', function() {
    console.log('Low stock alerts modal script loaded - DISABLED');
    
    const lowStockModal = new bootstrap.Modal(document.getElementById('lowStockModal'));
    const lowStockContent = document.getElementById('lowStockContent');
    
    // Show low stock alerts
    const lowStockBtn = document.getElementById('lowStockBtn');
    if (lowStockBtn) {
        lowStockBtn.addEventListener('click', function() {
            console.log('Low stock alerts button clicked');
            lowStockModal.show();
            loadLowStockAlerts();
        });
        console.log('Low stock alerts button event listener attached');
    } else {
        console.error('lowStockBtn not found in DOM');
    }
    
    // Refresh alerts
    document.getElementById('refreshAlertsBtn').addEventListener('click', function() {
        loadLowStockAlerts();
    });
    
    async function loadLowStockAlerts() {
        lowStockContent.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">{{ __('messages.loading') }}</span>
                </div>
            </div>
        `;
        
        try {
            const response = await fetch('{{ route("pos.low-stock-alerts") }}');
            const data = await response.json();
            
            if (data.success) {
                displayLowStockAlerts(data.alerts);
            } else {
                lowStockContent.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ __('messages.error_loading_alerts') }}
                    </div>
                `;
            }
        } catch (error) {
            console.error('Low stock alerts error:', error);
            lowStockContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ __('messages.error_loading_alerts') }}
                </div>
            `;
        }
    }
    
    function displayLowStockAlerts(alerts) {
        if (alerts.length === 0) {
            lowStockContent.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                    <h5>{{ __('messages.no_low_stock_items') }}</h5>
                    <p>{{ __('messages.all_products_well_stocked') }}</p>
                </div>
            `;
            return;
        }
        
        let alertsHtml = `
            <div class="alert alert-warning">
                <i class="fas fa-info-circle me-2"></i>
                {{ __('messages.low_stock_warning', ['count' => '${alerts.length}']) }}
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('messages.product') }}</th>
                            <th>{{ __('messages.category') }}</th>
                            <th>{{ __('messages.current_stock') }}</th>
                            <th>{{ __('messages.reorder_point') }}</th>
                            <th>{{ __('messages.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        alerts.forEach(alert => {
            const stockLevel = alert.current_stock;
            const reorderPoint = alert.reorder_point;
            const percentage = (stockLevel / reorderPoint) * 100;
            
            let statusClass = 'warning';
            let statusText = '{{ __("messages.low_stock") }}';
            
            if (stockLevel === 0) {
                statusClass = 'danger';
                statusText = '{{ __("messages.out_of_stock") }}';
            } else if (percentage <= 25) {
                statusClass = 'danger';
                statusText = '{{ __("messages.critical_low") }}';
            }
            
            alertsHtml += `
                <tr class="alert-item ${statusClass === 'danger' ? 'critical' : ''}">
                    <td>
                        <strong>${alert.name}</strong>
                    </td>
                    <td>${alert.category || '{{ __("messages.no_category") }}'}</td>
                    <td>
                        <span class="badge bg-${statusClass}">${stockLevel}</span>
                    </td>
                    <td>${reorderPoint}</td>
                    <td>
                        <span class="badge bg-${statusClass}">${statusText}</span>
                    </td>
                </tr>
            `;
        });
        
        alertsHtml += `
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    {{ __('messages.low_stock_help_text') }}
                </small>
            </div>
        `;
        
        lowStockContent.innerHTML = alertsHtml;
    }
});
} // End disabled block
</script>
