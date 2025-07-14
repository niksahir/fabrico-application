<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $staff = User::where('role', 'staff')
            ->latest()
            ->paginate($request->get('per_page', 10));

        if ($staff->isEmpty()) {
            return response()->json(['message' => 'No staff found'], 200);
        }

        return response()->json($staff);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string',
            'password' => 'required|string|min:6'
        ]);

        $validated['role'] = 'staff';
        $validated['password'] = Hash::make($validated['password']);

        return User::create($validated);
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

            $staff = User::where('role', 'staff')->findOrFail($id);

            $tickets = Ticket::where('assigned_to', $id)
                ->with(['user', 'attachments'])
                ->latest()
                ->paginate($request->get('per_page', 10));

            return response()->json([
                'staff' => $staff,
                'tickets' => $tickets
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $staff = User::where('role', 'staff')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $staff->id,
            'phone' => 'sometimes|string',
            'password' => 'nullable|string|min:6'
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $staff->update($validated);
        return $staff;
    }

    public function destroy($id)
    {
        $staff = User::where('role', 'staff')->findOrFail($id);
        $staff->delete();
        return response()->json(['message' => 'Staff user deleted']);
    }
}

