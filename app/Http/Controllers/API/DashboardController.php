<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Exception;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        try {
            $totalTickets = Ticket::count();
            $pendingTickets = Ticket::whereIn('status', ['pending', 'assigned', 'closed'])->count();
            $completedTickets = Ticket::where('status', 'completed')->count();
            $totalUsers = User::where('role', 'user')->count();
            $staffMembers = User::where('role', 'staff')->count();
            $latestTickets = Ticket::with(['user', 'staff'])
                ->latest('updated_at')
                ->take(10)
                ->get();

            return response()->json([
                'total_tickets' => $totalTickets,
                'pending_tickets' => $pendingTickets,
                'completed_tickets' => $completedTickets,
                'total_users' => $totalUsers,
                'staff_members' => $staffMembers,
                'latest_tickets' => $latestTickets
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch dashboard stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
