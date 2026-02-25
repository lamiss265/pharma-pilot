@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('messages.batches_for') }} {{ $product->name }}</div>
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('products.batches.create', $product) }}" class="btn btn-primary">{{ __('messages.add_batch') }}</a>
                        <a href="{{ route('inventory') }}" class="btn btn-secondary">{{ __('messages.back_to_inventory') }}</a>
                    </div>
                    
                    @if ($batches->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.batch_number') }}</th>
                                    <th>{{ __('messages.manufacturing_date') }}</th>
                                    <th>{{ __('messages.expiry_date') }}</th>
                                    <th>{{ __('messages.quantity_received') }}</th>
                                    <th>{{ __('messages.quantity_remaining') }}</th>
                                    <th>{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($batches as $batch)
                                <tr>
                                    <td>{{ $batch->batch_number }}</td>
                                    <td>{{ $batch->manufacturing_date->format('Y-m-d') }}</td>
                                    <td>
                                        <span class="{{ $batch->expiry_date->isPast() ? 'text-danger' : '' }}">
                                            {{ $batch->expiry_date->format('Y-m-d') }}
                                            {{ $batch->expiry_date->isPast() ? '(' . __('messages.expired') . ')' : '' }}
                                        </span>
                                    </td>
                                    <td>{{ $batch->quantity_received }}</td>
                                    <td>{{ $batch->quantity_remaining }}</td>
                                    <td>
                                        <a href="{{ route('products.batches.edit', [$product, $batch]) }}" class="btn btn-sm btn-primary">{{ __('messages.edit') }}</a>
                                        <form action="{{ route('products.batches.destroy', [$product, $batch]) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('messages.confirm_delete') }}')">{{ __('messages.delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $batches->links() }}
                    </div>
                    @else
                    <div class="alert alert-info">{{ __('messages.no_batches_found') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
