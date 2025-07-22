<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Purchase;
use App\Models\Document;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $users = User::where('role', 'user')
                ->withCount('tickets') // assuming User has tickets() relationship
                ->latest()
                ->paginate($request->get('per_page', 10));

            return response()->json($users);
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

            return response()->json([
                'user' => $user,
                'tickets' => $tickets,
                'quotations' => $quotations,
                'documents' => $documents,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch user details',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
