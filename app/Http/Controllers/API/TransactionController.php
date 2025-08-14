<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    // Add Transaction
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ticket_id' => 'required|exists:tickets,id',
                'amount' => 'required|numeric|min:0',
                'status' => 'required|in:free,pending,completed,admin_credit',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $transaction = Transaction::create($request->only([
                'ticket_id',
                'amount',
                'status',
                'description'
            ]));

            return response()->json([
                'message' => 'Transaction created',
                'transaction' => $transaction
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Failed to create transaction',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Get Transactions with Pagination
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $transactions = Transaction::with('ticket')->latest()->paginate($perPage);

            return response()->json($transactions);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Failed to fetch transactions',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
