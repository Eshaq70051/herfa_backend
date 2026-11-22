<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportReviewController extends Controller
{
    public function tickets(Request $request)
    {
        $query = SupportTicket::with(['user', 'serviceRequest']);

        if ($request->filled('ticket_status')) {
            $query->where('ticket_status', $request->ticket_status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tickets = $query->latest('id')->paginate(15);

        return view('admin.support.tickets', compact('tickets'));
    }

    public function showTicket($id)
    {
        $ticket = SupportTicket::with(['user', 'serviceRequest', 'supportMessages.user'])->findOrFail($id);

        return view('admin.support.ticket-show', compact('ticket'));
    }

    public function replyTicket(Request $request, $id)
    {
        $request->validate(['message' => 'required|string']);

        $ticket = SupportTicket::findOrFail($id);

        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => Auth::id(),
            'message' => $request->message,
            'is_internal' => $request->boolean('is_internal') ? 1 : 0,
        ]);

        $ticket->update([
            'last_message_at' => now(),
            'ticket_status' => $ticket->ticket_status === 'open' ? 'in_progress' : $ticket->ticket_status,
        ]);

        return back()->with('success', 'تم إرسال الرد بنجاح.');
    }

    public function updateTicketStatus(Request $request, $id)
    {
        $request->validate(['ticket_status' => 'required|in:open,in_progress,resolved,closed']);

        $ticket = SupportTicket::findOrFail($id);
        $ticket->ticket_status = $request->ticket_status;
        if (in_array($request->ticket_status, ['resolved', 'closed'])) {
            $ticket->resolved_at = now();
            $ticket->closed_by_admin_id = Auth::id();
        }
        $ticket->save();

        return back()->with('success', 'تم تحديث حالة التذكرة بنجاح.');
    }

    public function reviews()
    {
        $reviews = Review::with(['customer', 'craftsmanProfile.user', 'serviceRequest'])
            ->latest('id')
            ->paginate(15);

        return view('admin.support.reviews', compact('reviews'));
    }

    public function updateReviewStatus(Request $request, $id)
    {
        $request->validate(['review_status' => 'required|in:visible,hidden,reported']);

        $rev = Review::findOrFail($id);
        $rev->review_status = $request->review_status;
        $rev->save();

        return back()->with('success', 'تم تعديل حالة المراجعة بنجاح.');
    }
}
