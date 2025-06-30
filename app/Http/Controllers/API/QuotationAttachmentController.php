<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QuotationAttachment;

class QuotationAttachmentController extends Controller
{
    public function index()
    {
        return QuotationAttachment::with('quotation')->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quotation_id' => 'required|exists:quotations,id',
            'file_path' => 'required|string'
        ]);

        return QuotationAttachment::create($validated);
    }

    public function show(QuotationAttachment $quotationAttachment)
    {
        return $quotationAttachment->load('quotation');
    }

    public function update(Request $request, QuotationAttachment $quotationAttachment)
    {
        $validated = $request->validate([
            'file_path' => 'sometimes|string'
        ]);

        $quotationAttachment->update($validated);
        return $quotationAttachment;
    }

    public function destroy(QuotationAttachment $quotationAttachment)
    {
        $quotationAttachment->delete();
        return response()->json(['message' => 'Quotation attachment deleted successfully']);
    }
}
