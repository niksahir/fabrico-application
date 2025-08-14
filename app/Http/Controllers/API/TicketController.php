<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\Transaction;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['user', 'staff', 'attachments'])->latest();

        if ($request->has('status')) {
            if ($request->status === 'pending') {
                $query->whereIn('status', ['pending', 'assigned', 'closed']);
            } elseif ($request->status === 'completed') {
                $query->where('status', 'completed');
            }
        }

        if (auth()->user()->role == 'user') {
            $query->where('user_id', auth()->id());
        }

        if (auth()->user()->role == 'staff') {
            $query->where('assigned_to', auth()->id());
        }

        $tickets = $query->paginate($request->get('per_page', 10));

        return response()->json($tickets);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'machine' => 'required|string',
                'issue_description' => 'required|string',
                'status' => 'in:pending,assigned,completed,closed',
                'assigned_to' => 'nullable|exists:users,id',
                'attachments.*' => 'nullable|file|max:102400',
                'address' => 'nullable|string',
                'contact_number' => 'nullable|string',
                'machine_fault' => 'nullable|string',
                'resolve_description' => 'nullable|string',
                'amount' => 'nullable|numeric|min:0',
                'transaction_status' => 'in:free,pending,completed',
            ]);

            DB::beginTransaction();

            $ticket = Ticket::create($validated);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('ticket_attachments', 'public');
                    TicketAttachment::create([
                        'ticket_id' => $ticket->id,
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                    ]);
                }
            }

            DB::commit();
            return response()->json($ticket->load(['user', 'staff', 'attachments']), 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create ticket', 'message' => $e->getMessage()], 500);
        }
    }

    public function show(Ticket $ticket)
    {
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 200);
        }

        return response()->json($ticket->load(['user', 'staff', 'attachments']));
    }

    public function update(Request $request, Ticket $ticket)
    {
        try {
            $validated = $request->validate([
                'machine' => 'sometimes|string',
                'issue_description' => 'sometimes|string',
                'status' => 'sometimes|in:pending,assigned,completed,closed',
                'assigned_to' => 'nullable|exists:users,id',
                'attachments.*' => 'nullable|file|max:102400',
                'address' => 'nullable|string',
                'contact_number' => 'nullable|string',
                'machine_fault' => 'nullable|string',
                'resolve_description' => 'nullable|string',
                'amount' => 'nullable|numeric|min:0',
                'transaction_status' => 'in:free,pending,completed,admin_credit',
            ]);

            DB::beginTransaction();

            $ticket->update($validated);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('ticket_attachments', 'public');
                    TicketAttachment::create([
                        'ticket_id' => $ticket->id,
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                    ]);
                }
            }

            // If ticket is closed, create a transaction
            if ($request->status === 'closed') {
                Transaction::create(
                    [
                        'ticket_id' => $ticket->id,
                        'amount' => $request->transaction_amount,
                        'status' => $request->transaction_status,
                        'description' => $request->transaction_description,
                    ]
                );
            }

            DB::commit();
            return response()->json($ticket->load(['user', 'staff', 'attachments']), 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update ticket', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Ticket $ticket)
    {
        try {
            foreach ($ticket->attachments as $attachment) {
                if (Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
                $attachment->delete();
            }

            $ticket->delete();

            return response()->json(['message' => 'Ticket deleted successfully'], 200);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Failed to delete ticket', 'message' => $e->getMessage()], 500);
        }
    }
}
