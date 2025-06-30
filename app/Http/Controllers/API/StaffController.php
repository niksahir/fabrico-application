<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index()
    {
        return User::where('role', 'staff')->get();
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

    public function show($id)
    {
        $staff = User::where('role', 'staff')->findOrFail($id);
        $tickets = Ticket::where('assigned_to', $id)->get();

        return response()->json([
            'staff' => $staff,
            'tickets' => $tickets
        ]);
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

