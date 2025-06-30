<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase;

class PurchaseController extends Controller
{
    public function index()
    {
        return Purchase::latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_name' => 'required|string',
            'purchase_date' => 'required|date',
            'quotation_id' => 'required|exists:quotations,id',
            'invoice_file' => 'nullable|string'
        ]);

        return Purchase::create($validated);
    }

    public function show(Purchase $purchase)
    {
        return $purchase;
    }

    public function update(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'product_name' => 'sometimes|string',
            'purchase_date' => 'sometimes|date',
            'quotation_id' => 'sometimes|exists:quotations,id',
            'invoice_file' => 'nullable|string'
        ]);

        $purchase->update($validated);
        return $purchase;
    }

    public function destroy(Purchase $purchase)
    {
        $purchase->delete();
        return response()->json(['message' => 'Purchase deleted successfully']);
    }
}
