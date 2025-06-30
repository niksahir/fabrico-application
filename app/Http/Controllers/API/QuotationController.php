<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quotation;

class QuotationController extends Controller
{
    public function index()
    {
        return Quotation::with('attachments')->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_name' => 'required|string',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'status' => 'in:pending,approved,sent'
        ]);

        return Quotation::create($validated);
    }

    public function show(Quotation $quotation)
    {
        return $quotation->load('attachments');
    }

    public function update(Request $request, Quotation $quotation)
    {
        $validated = $request->validate([
            'product_name' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'description' => 'nullable|string',
            'status' => 'in:pending,approved,sent'
        ]);

        $quotation->update($validated);
        return $quotation;
    }

    public function destroy(Quotation $quotation)
    {
        $quotation->delete();
        return response()->json(['message' => 'Quotation deleted successfully']);
    }
}
