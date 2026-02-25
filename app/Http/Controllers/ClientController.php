<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Store a newly created client in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $client = Client::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'client' => $client,
                'message' => 'Client created successfully'
            ]);
        }

        return redirect()->back()
                        ->with('success', 'Client created successfully.');
    }
} 