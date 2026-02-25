@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ __('messages.supplier_details') }}</h5>
                    <div>
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning">{{ __('messages.edit') }}</a>
                        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">{{ __('messages.back') }}</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>{{ __('messages.basic_info') }}</h6>
                            <div class="mb-3">
                                <strong>{{ __('messages.name') }}:</strong> {{ $supplier->name }}
                            </div>
                            <div class="mb-3">
                                <strong>{{ __('messages.contact') }}:</strong> {{ $supplier->contact_name ?? '-' }}
                            </div>
                            <div class="mb-3">
                                <strong>{{ __('messages.email') }}:</strong> {{ $supplier->email ?? '-' }}
                            </div>
                            <div class="mb-3">
                                <strong>{{ __('messages.phone') }}:</strong> {{ $supplier->phone ?? '-' }}
                            </div>
                            <div class="mb-3">
                                <strong>{{ __('messages.tax_id') }}:</strong> {{ $supplier->tax_id ?? '-' }}
                            </div>
                            <div class="mb-3">
                                <strong>{{ __('messages.address') }}:</strong> <br> {{ $supplier->address ?? '-' }}
                            </div>
                            <div class="mb-3">
                                <strong>{{ __('messages.notes') }}:</strong> <br> {{ $supplier->notes ?? '-' }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>{{ __('messages.purchase_orders') }}</h6>
                            @if($supplier->purchaseOrders->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ __('messages.order_number') }}</th>
                                                <th>{{ __('messages.order_date') }}</th>
                                                <th>{{ __('messages.total_amount') }}</th>
                                                <th>{{ __('messages.status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($supplier->purchaseOrders as $order)
                                                <tr>
                                                    <td>{{ $order->id }}</td>
                                                    <td>{{ $order->order_number }}</td>
                                                    <td>{{ $order->order_date }}</td>
                                                    <td>{{ $order->total_amount }}</td>
                                                    <td>{{ $order->status }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p>{{ __('messages.no_purchase_orders') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
