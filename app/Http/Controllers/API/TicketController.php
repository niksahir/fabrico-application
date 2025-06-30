<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;

class TicketController extends Controller
{
    public function index()
    {
        return Ticket::with(['user', 'staff', 'attachments'])->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'machine' => 'required|string',
            'issue_description' => 'required|string',
            'status' => 'in:pending,assigned,completed,closed',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        return Ticket::create($validated);
    }

    public function show(Ticket $ticket)
    {
        return $ticket->load(['user', 'staff', 'attachments']);
    }

    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'machine' => 'sometimes|string',
            'issue_description' => 'sometimes|string',
            'status' => 'sometimes|in:pending,assigned,completed,closed',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        $ticket->update($validated);
        return $ticket;
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return response()->json(['message' => 'Ticket deleted successfully']);
    }
}
