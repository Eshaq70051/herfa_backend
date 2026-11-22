<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CraftsmanEarning;
use App\Models\CraftsmanPayout;
use App\Models\CraftsmanProfile;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\SupportTicket;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_requests' => ServiceRequest::count(),
            'pending_requests' => ServiceRequest::whereHas('requestStatus', fn ($q) => $q->where('code', 'pending'))->count(),
            'in_progress_requests' => ServiceRequest::whereHas('requestStatus', fn ($q) => $q->where('code', 'in_progress'))->count(),
            'completed_requests' => ServiceRequest::whereHas('requestStatus', fn ($q) => $q->where('code', 'completed'))->count(),
            'total_craftsmen' => CraftsmanProfile::count(),
            'pending_craftsmen' => CraftsmanProfile::where('approval_status', 'pending')->count(),
            'total_users' => User::count(),
            'total_services' => Service::count(),
            'total_categories' => Category::count(),
            'total_earnings' => CraftsmanEarning::sum('total_service_amount') ?: 0,
            'company_share' => CraftsmanEarning::sum('company_share_amount') ?: 0,
            'pending_payouts' => CraftsmanPayout::where('status', 'PENDING')->sum('amount') ?: 0,
            'open_tickets' => SupportTicket::where('ticket_status', 'open')->count(),
            'total_reviews' => Review::count(),
        ];

        $recentRequests = ServiceRequest::with(['user', 'service', 'requestStatus'])
            ->latest('id')
            ->take(8)
            ->get();

        $recentCraftsmen = CraftsmanProfile::with('user')
            ->latest('id')
            ->take(6)
            ->get();

        $recentTickets = SupportTicket::with('user')
            ->where('ticket_status', 'open')
            ->latest('id')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentRequests', 'recentCraftsmen', 'recentTickets'));
    }
}
