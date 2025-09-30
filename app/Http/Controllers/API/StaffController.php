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
        try {
            $query = User::where('role', 'staff');

            if ($search = $request->get('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
                });
            }

            $staff = $query->latest()->paginate($request->get('per_page', 10));

            return response()->json($staff, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch staff',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'phone_number' => 'required|string|max:15|unique:users,phone_number',
            'password' => 'required|string'
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
        try {
            $staff = User::whereIn('role', ['staff', 'user'])->findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|string',
                'phone_number' => 'sometimes|string|max:15|unique:users,phone_number,' . $staff->id,
                'password' => 'nullable|string|min:6',
                'model_number' => 'sometimes'
            ]);

            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }

            $staff->update($validated);

            return response()->json([
                'message' => 'Staff/User updated successfully',
                'staff' => $staff
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update staff/user',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $staff = User::whereIn('role', ['staff', 'user'])->findOrFail($id);
        $staff->delete();
        return response()->json(['message' => 'Staff/User user deleted']);
    }
}

