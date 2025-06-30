<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;

class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'total_tickets' => Ticket::count(),
            'pending_tickets' => Ticket::where('status', 'pending')->count(),
            'completed_tickets' => Ticket::where('status', 'completed')->count(),
            'total_users' => User::where('role', 'user')->count(),
            'staff_members' => User::where('role', 'staff')->count()
        ]);
    }
}
