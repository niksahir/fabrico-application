<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Purchase;
use App\Models\Transaction;
use App\Models\Document;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = User::where('role', 'user')->withCount('tickets');

            if ($search = $request->get('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
                });
            }

            $users = $query->latest()->paginate($request->get('per_page', 10));

            return response()->json($users, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch users',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|integer|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = User::where('role', 'user')->findOrFail($id);
            $perPage = $request->get('per_page', 10);

            $tickets = Ticket::where('user_id', $id)
                ->latest()
                ->paginate($perPage, ['*'], 'tickets_page');

            $quotations = Quotation::where('user_id', $id)
                ->latest()
                ->paginate($perPage, ['*'], 'quotations_page');

            $documents = Document::where('user_id', $id)
                ->latest()
                ->paginate($perPage, ['*'], 'documents_page');

            // Transactions with optional date filter
            $transactionsQuery = Transaction::whereIn('ticket_id', function ($query) use ($id) {
                $query->select('id')->from('tickets')->where('user_id', $id);
            });

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $transactionsQuery->whereBetween('created_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ]);
            }

            $transactions = $transactionsQuery
                ->latest()
                ->paginate($perPage, ['*'], 'transactions_page');

            // Calculate amounts
            $pendingAmount = Transaction::whereIn('ticket_id', function ($query) use ($id) {
                    $query->select('id')->from('tickets')->where('user_id', $id);
                })
                ->where('status', 'pending')
                ->sum('amount');

            $completedAmount = Transaction::whereIn('ticket_id', function ($query) use ($id) {
                    $query->select('id')->from('tickets')->where('user_id', $id);
                })
                ->where('status', 'admin_credit')
                ->sum('amount');

            // Admin Calculate amounts
            $adminPendingAmount = Transaction::whereIn('ticket_id', function ($query) {
                    $query->select('id')->from('tickets');
                })
                ->where('status', 'pending')
                ->sum('amount');

            $adminCompletedAmount = Transaction::whereIn('ticket_id', function ($query) {
                    $query->select('id')->from('tickets');
                })
                ->where('status', 'admin_credit')
                ->sum('amount');

            $totalPendingAmount = $pendingAmount - $completedAmount;
            if ($totalPendingAmount < 0) {
                $totalPendingAmount = 0;
            }

            $totalAdminPendingAmount = $adminPendingAmount - $adminCompletedAmount;
            if ($totalAdminPendingAmount < 0) {
                $totalAdminPendingAmount = 0;
            }

            return response()->json([
                'user' => $user,
                'tickets' => $tickets,
                'quotations' => $quotations,
                'documents' => $documents,
                'transactions' => $transactions,
                'total_pending_amount' => $totalPendingAmount,
                'total_admin_pending_amount' => $totalAdminPendingAmount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch user details',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
