@extends('layouts.app')

@section('title', __('messages.sales_performance'))

@section('content')
    <div class="container-fluid">
        <h1 class="mt-4 mb-4">{{ __('messages.sales_performance') }}</h1>
        
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.top_performers') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('messages.employee') }}</th>
                                        <th>{{ __('messages.position') }}</th>
                                        <th>{{ __('messages.total_sales') }}</th>
                                        <th>{{ __('messages.sales_count') }}</th>
                                        <th>{{ __('messages.average_sale') }}</th>
                                        <th>{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topUsersBySales as $index => $user)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($index < 3)
                                                        <i class="fas fa-trophy text-{{ $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'danger') }} me-2"></i>
                                                    @endif
                                                    {{ $user->name }}
                                                </div>
                                            </td>
                                            <td>{{ $user->position ?? __('messages.worker') }}</td>
                                            <td>{{ number_format($user->total_sales_amount ?? 0, 2) }} {{ __('messages.currency') }}</td>
                                            <td>{{ $user->sales_count ?? 0 }}</td>
                                            <td>
                                                @if($user->sales_count > 0)
                                                    {{ number_format(($user->total_sales_amount ?? 0) / $user->sales_count, 2) }} {{ __('messages.currency') }}
                                                @else
                                                    0 {{ __('messages.currency') }}
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('messages.monthly_sales_performance') }}</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlySalesChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Process data for chart
        const monthlySalesData = @json($monthlySales);
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        // Group data by user
        const userSales = {};
        const labels = [];
        
        // Get unique months
        monthlySalesData.forEach(item => {
            const monthYear = months[item.month - 1] + ' ' + item.year;
            if (!labels.includes(monthYear)) {
                labels.push(monthYear);
            }
            
            if (!userSales[item.name]) {
                userSales[item.name] = {};
            }
            
            userSales[item.name][monthYear] = item.total;
        });
        
        // Sort labels chronologically
        labels.sort((a, b) => {
            const [aMonth, aYear] = a.split(' ');
            const [bMonth, bYear] = b.split(' ');
            
            if (aYear !== bYear) {
                return parseInt(aYear) - parseInt(bYear);
            }
            
            return months.indexOf(aMonth) - months.indexOf(bMonth);
        });
        
        // Generate random colors for each user
        const getRandomColor = () => {
            const r = Math.floor(Math.random() * 200);
            const g = Math.floor(Math.random() * 200);
            const b = Math.floor(Math.random() * 200);
            return `rgba(${r}, ${g}, ${b}, 0.7)`;
        };
        
        // Create datasets
        const datasets = [];
        Object.keys(userSales).forEach(userName => {
            const data = labels.map(label => userSales[userName][label] || 0);
            
            datasets.push({
                label: userName,
                data: data,
                backgroundColor: getRandomColor(),
                borderColor: getRandomColor(),
                borderWidth: 2,
                fill: false,
                tension: 0.1
            });
        });
        
        // Create chart
        const ctx = document.getElementById('monthlySalesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: '{{ __("messages.monthly_sales_by_employee") }}'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('en-US', { 
                                        style: 'currency', 
                                        currency: 'USD'
                                    }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' {{ __("messages.currency") }}';
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection 