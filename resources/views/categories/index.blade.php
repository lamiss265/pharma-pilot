@extends('layouts.app')

@section('title', __('messages.categories_management'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('messages.categories_management') }}</span>
                    <a href="{{ route('categories.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> {{ __('messages.add_category') }}
                    </a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ __('messages.success_message') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ __('messages.error_message') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.id') }}</th>
                                    <th>{{ __('messages.name') }}</th>
                                    <th>{{ __('messages.description') }}</th>
                                    <th>{{ __('messages.products_count') }}</th>
                                    <th>{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $category)
                                    <tr>
                                        <td>{{ $category->id }}</td>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->description ?? __('messages.no_description') }}</td>
                                        <td>{{ $category->products->count() }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('categories.show', $category) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> {{ __('messages.view') }}
                                                </a>
                                                <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i> {{ __('messages.edit') }}
                                                </a>
                                                <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i> {{ __('messages.delete') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">{{ __('messages.no_records') }}</td>
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