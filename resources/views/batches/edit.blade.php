@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('messages.edit_batch_for') }} {{ $product->name }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('products.batches.update', [$product, $batch]) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="batch_number" class="form-label">{{ __('messages.batch_number') }}</label>
                            <input type="text" class="form-control @error('batch_number') is-invalid @enderror" id="batch_number" name="batch_number" value="{{ old('batch_number', $batch->batch_number) }}" required>
                            @error('batch_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="manufacturing_date" class="form-label">{{ __('messages.manufacturing_date') }}</label>
                            <input type="date" class="form-control @error('manufacturing_date') is-invalid @enderror" id="manufacturing_date" name="manufacturing_date" value="{{ old('manufacturing_date', $batch->manufacturing_date->format('Y-m-d')) }}" required>
                            @error('manufacturing_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="expiry_date" class="form-label">{{ __('messages.expiry_date') }}</label>
                            <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" id="expiry_date" name="expiry_date" value="{{ old('expiry_date', $batch->expiry_date->format('Y-m-d')) }}" required>
                            @error('expiry_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="quantity_received" class="form-label">{{ __('messages.quantity_received') }}</label>
                            <input type="number" class="form-control @error('quantity_received') is-invalid @enderror" id="quantity_received" name="quantity_received" value="{{ old('quantity_received', $batch->quantity_received) }}" min="1" required>
                            @error('quantity_received')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">{{ __('messages.update') }}</button>
                            <a href="{{ route('products.batches.index', $product) }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
