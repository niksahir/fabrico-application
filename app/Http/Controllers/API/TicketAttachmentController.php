<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TicketAttachment;

class TicketAttachmentController extends Controller
{
    public function index()
    {
        return TicketAttachment::with('ticket')->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'file_path' => 'required|string',
            'file_type' => 'required|in:invoice,warranty'
        ]);

        return TicketAttachment::create($validated);
    }

    public function show(TicketAttachment $ticketAttachment)
    {
        return $ticketAttachment->load('ticket');
    }

    public function update(Request $request, TicketAttachment $ticketAttachment)
    {
        $validated = $request->validate([
            'file_path' => 'sometimes|string',
            'file_type' => 'sometimes|in:invoice,warranty'
        ]);

        $ticketAttachment->update($validated);
        return $ticketAttachment;
    }

    public function destroy(TicketAttachment $ticketAttachment)
    {
        $ticketAttachment->delete();
        return response()->json(['message' => 'Ticket attachment deleted successfully']);
    }
}
