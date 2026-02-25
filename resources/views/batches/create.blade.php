@extends('layouts.app')

@section('title', __('messages.add_batch'))

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('messages.add_batch_for') }} {{ $product->name }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('products.batches.store', $product) }}">
                        @csrf

                        <div class="mb-3">
                            <label for="batch_number" class="form-label">{{ __('messages.batch_number') }}</label>
                            <input type="text" class="form-control @error('batch_number') is-invalid @enderror" id="batch_number" name="batch_number" value="{{ old('batch_number') }}" required>
                            @error('batch_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="manufacturing_date" class="form-label">{{ __('messages.manufacturing_date') }}</label>
                            <input type="date" class="form-control @error('manufacturing_date') is-invalid @enderror" id="manufacturing_date" name="manufacturing_date" value="{{ old('manufacturing_date') }}" required>
                            @error('manufacturing_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="expiry_date" class="form-label">{{ __('messages.expiry_date') }}</label>
                            <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}" required>
                            @error('expiry_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="quantity_received" class="form-label">{{ __('messages.quantity_received') }}</label>
                            <input type="number" class="form-control @error('quantity_received') is-invalid @enderror" id="quantity_received" name="quantity_received" value="{{ old('quantity_received') }}" required>
                            @error('quantity_received')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('products.show', $product) }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('messages.add_batch') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
