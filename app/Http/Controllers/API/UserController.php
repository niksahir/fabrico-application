<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Purchase;
use App\Models\Quotation;

class UserController extends Controller
{
    public function index()
    {
        return User::where('role', 'user')->get();
    }

    public function show($id)
    {
        $user = User::where('role', 'user')->findOrFail($id);

        return response()->json([
            'user' => $user,
            'tickets' => Ticket::where('user_id', $id)->get(),
            'purchases' => Purchase::where('user_id', $id)->get(),
            'quotations' => Quotation::where('user_id', $id)->get(),
        ]);
    }
}
