<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BatchController extends Controller
{
    public function index(Product $product)
    {
        $batches = $product->batches()->paginate(10);
        return view('batches.index', compact('product', 'batches'));
    }

    public function create(Product $product)
    {
        return view('batches.create', compact('product'));
    }

    public function store(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), Batch::$rules, Batch::$messages);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $batch = new Batch($request->all());
        $batch->product_id = $product->id;
        $batch->quantity_remaining = $request->quantity_received;
        $batch->save();

        return redirect()->route('products.batches.index', $product)
            ->with('success', __('messages.batch_created_successfully'));
    }

    public function edit(Product $product, Batch $batch)
    {
        return view('batches.edit', compact('product', 'batch'));
    }

    public function update(Request $request, Product $product, Batch $batch)
    {
        $rules = Batch::$rules;
        $rules['batch_number'] = 'required|string|unique:batches,batch_number,' . $batch->id;
        $validator = Validator::make($request->all(), $rules, Batch::$messages);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $batch->update($request->all());

        return redirect()->route('products.batches.index', $product)
            ->with('success', __('messages.batch_updated_successfully'));
    }

    public function destroy(Product $product, Batch $batch)
    {
        $batch->delete();
        return redirect()->route('products.batches.index', $product)
            ->with('success', __('messages.batch_deleted_successfully'));
    }
}
