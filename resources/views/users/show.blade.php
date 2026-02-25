@extends('layouts.app')

@section('title', __('messages.user_profile'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.user_profile') }}</h1>
        <div>
            @can('update', $user)
            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary me-2">
                <i class="fas fa-edit me-1"></i> {{ __('messages.edit') }}
            </a>
            @endcan
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> {{ __('messages.back') }}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <i class="fas fa-user me-1"></i> {{ __('messages.user_information') }}
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar avatar-xl mx-auto mb-3 bg-{{ $user->role == 'admin' ? 'primary' : 'secondary' }} bg-opacity-10 rounded-circle">
                            <span class="avatar-text text-{{ $user->role == 'admin' ? 'primary' : 'secondary' }} display-4">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </span>
                        </div>
                        <h4>{{ $user->name }}</h4>
                        <p class="text-muted mb-0">{{ $user->position ?? __('messages.not_available') }}</p>
                        <span class="badge bg-{{ $user->role == 'admin' ? 'primary' : 'secondary' }} mt-2">
                            {{ __('messages.' . $user->role) }}
                        </span>
                        <span class="badge bg-{{ $user->status == 'active' ? 'success' : 'danger' }} mt-2">
                            {{ __('messages.' . $user->status) }}
                        </span>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('messages.email') }}</label>
                        <p>{{ $user->email }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('messages.phone') }}</label>
                        <p>{{ $user->phone ?? __('messages.not_available') }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('messages.address') }}</label>
                        <p>{{ $user->address ?? __('messages.not_available') }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('messages.language') }}</label>
                        <p>
                            @if($user->language == 'en')
                                🇺🇸 {{ __('messages.english') }}
                            @elseif($user->language == 'fr')
                                🇫🇷 {{ __('messages.french') }}
                            @elseif($user->language == 'ar')
                                🇸🇦 {{ __('messages.arabic') }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <i class="fas fa-shield-alt me-1"></i> {{ __('messages.permissions') }}
                </div>
                <div class="card-body">
                    @if($user->role == 'admin')
                        <div class="alert alert-primary">
                            {{ __('messages.admin_all_permissions') }}
                        </div>
                    @else
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ __('messages.manage_inventory') }}
                                @if(in_array('inventory', $user->permissions ?? []))
                                    <span class="badge bg-success rounded-pill"><i class="fas fa-check"></i></span>
                                @else
                                    <span class="badge bg-danger rounded-pill"><i class="fas fa-times"></i></span>
                                @endif
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ __('messages.manage_sales') }}
                                @if(in_array('sales', $user->permissions ?? []))
                                    <span class="badge bg-success rounded-pill"><i class="fas fa-check"></i></span>
                                @else
                                    <span class="badge bg-danger rounded-pill"><i class="fas fa-times"></i></span>
                                @endif
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ __('messages.manage_clients') }}
                                @if(in_array('clients', $user->permissions ?? []))
                                    <span class="badge bg-success rounded-pill"><i class="fas fa-check"></i></span>
                                @else
                                    <span class="badge bg-danger rounded-pill"><i class="fas fa-times"></i></span>
                                @endif
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ __('messages.view_reports') }}
                                @if(in_array('reports', $user->permissions ?? []))
                                    <span class="badge bg-success rounded-pill"><i class="fas fa-check"></i></span>
                                @else
                                    <span class="badge bg-danger rounded-pill"><i class="fas fa-times"></i></span>
                                @endif
                            </li>
                        </ul>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <i class="fas fa-chart-line me-1"></i> {{ __('messages.sales_performance') }}
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ __('messages.total_sales') }}</h5>
                                    <p class="display-4 fw-bold">{{ $totalSalesCount }}</p>
                                    <p class="text-muted">{{ __('messages.transactions') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ __('messages.total_amount') }}</h5>
                                    <p class="display-4 fw-bold">{{ number_format($totalSalesAmount, 2) }}</p>
                                    <p class="text-muted">{{ __('messages.currency') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <canvas id="salesChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-history me-1"></i> {{ __('messages.recent_activities') }}
                        </div>
                        <a href="{{ route('users.activities', $user) }}" class="btn btn-sm btn-outline-primary">
                            {{ __('messages.view_all') }}
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('messages.activity') }}</th>
                                    <th>{{ __('messages.description') }}</th>
                                    <th>{{ __('messages.date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activities as $activity)
                                <tr>
                                    <td>
                                        <span class="badge bg-info">{{ $activity->action }}</span>
                                    </td>
                                    <td>{{ __($activity->description) }}</td>
                                    <td>{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-3">{{ __('messages.no_activities') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
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
        // Sales Chart
        var ctx = document.getElementById('salesChart').getContext('2d');
        
        var months = [];
        var salesData = [];
        
        @foreach($monthlySales as $sale)
            months.push('{{ date("M Y", mktime(0, 0, 0, $sale->month, 1, $sale->year)) }}');
            salesData.push({{ $sale->total }});
        @endforeach
        
        var salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: '{{ __("messages.monthly_sales") }}',
                    data: salesData,
                    backgroundColor: 'rgba(13, 110, 253, 0.2)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 2,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' {{ __("messages.currency") }}';
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection 