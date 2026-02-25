@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('messages.purchase_suggestions') }}</h5>
            <div>
                <a href="{{ route('inventory') }}" class="btn btn-sm btn-light">
                    <i class="fas fa-arrow-left"></i> {{ __('messages.back_to_inventory') }}
                </a>
                <button id="printBtn" class="btn btn-sm btn-light ml-2">
                    <i class="fas fa-print"></i> {{ __('messages.print') }}
                </button>
            </div>
        </div>
        
        <div class="card-body">
            @if(count($suggestions) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('messages.product') }}</th>
                                <th>{{ __('messages.barcode') }}</th>
                                <th>{{ __('messages.current_stock') }}</th>
                                <th>{{ __('messages.reorder_point') }}</th>
                                <th>{{ __('messages.optimal_stock') }}</th>
                                <th>{{ __('messages.suggested_quantity') }}</th>
                                <th>{{ __('messages.unit_price') }}</th>
                                <th>{{ __('messages.estimated_cost') }}</th>
                                <th>{{ __('messages.supplier') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalCost = 0; @endphp
                            @foreach($suggestions as $suggestion)
                                @php $totalCost += $suggestion['estimated_cost']; @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('products.show', $suggestion['product']) }}">
                                            {{ $suggestion['product']->name }}
                                        </a>
                                    </td>
                                    <td>{{ $suggestion['product']->barcode ?? '-' }}</td>
                                    <td>
                                        <span class="text-danger">{{ $suggestion['product']->quantity }}</span>
                                    </td>
                                    <td>{{ $suggestion['product']->reorder_point }}</td>
                                    <td>{{ $suggestion['product']->optimal_stock_level }}</td>
                                    <td>
                                        <strong>{{ $suggestion['quantity_to_order'] }}</strong>
                                    </td>
                                    <td>{{ number_format($suggestion['product']->price, 2) }} DH</td>
                                    <td>{{ number_format($suggestion['estimated_cost'], 2) }} DH</td>
                                    <td>{{ $suggestion['product']->supplier }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <td colspan="7" class="text-right">
                                    <strong>{{ __('messages.total_estimated_cost') }}:</strong>
                                </td>
                                <td colspan="2">
                                    <strong>{{ number_format($totalCost, 2) }} DH</strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="mt-4">
                    <form action="{{ route('products.batch-update') }}" method="POST" id="batchUpdateForm">
                        @csrf
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> {{ __('messages.mark_as_ordered') }}
                            </button>
                        </div>
                        
                        @foreach($suggestions as $suggestion)
                            <input type="hidden" 
                                name="products[{{ $loop->index }}][id]" 
                                value="{{ $suggestion['product']->id }}">
                            <input type="hidden" 
                                name="products[{{ $loop->index }}][quantity]" 
                                value="{{ $suggestion['product']->quantity + $suggestion['quantity_to_order'] }}">
                        @endforeach
                    </form>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> {{ __('messages.no_purchase_suggestions') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#printBtn').click(function() {
            window.print();
        });
        
        $('#batchUpdateForm').submit(function() {
            return confirm("{{ __('messages.confirm_mark_as_ordered') }}");
        });
    });
</script>
@endpush 