@extends('layouts.app')

@section('title', 'Sale Details')

@section('content')
    <div class="container-fluid">
        <h1 class="mt-4 mb-4">Sale Details</h1>
        
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-receipt me-1"></i>
                Sale #{{ $sale->id }}
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Sale Information</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th>Sale ID</th>
                                <td>{{ $sale->id }}</td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>{{ $sale->sale_date->format('Y-m-d') }}</td>
                            </tr>
                            <tr>
                                <th>Time</th>
                                <td>{{ $sale->created_at->format('H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Product Information</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th>Product</th>
                                <td>{{ optional($sale->product)->name ?? __('messages.unknown_product') }}</td>
                            </tr>
                            <tr>
                                <th>Quantity</th>
                                <td>{{ $sale->quantity }}</td>
                            </tr>
                            <tr>
                                <th>Supplier</th>
                                <td>{{ optional($sale->product)->supplier ?? '' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5>Client Information</h5>
                        @if($sale->client)
                            <table class="table table-bordered">
                                <tr>
                                    <th>Name</th>
                                    <td>{{ $sale->client->name }}</td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td>{{ $sale->client->phone }}</td>
                                </tr>
                                <tr>
                                    <th>Notes</th>
                                    <td>{{ $sale->client->notes }}</td>
                                </tr>
                            </table>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-1"></i> Walk-in Customer (No client information)
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="{{ route('sales.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Sales
                    </a>
                    
                    <form action="{{ route('sales.destroy', $sale) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this sale? This will restore the product quantity.')">
                            <i class="fas fa-trash me-1"></i> Delete Sale
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection 