<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::with(['user', 'notificationType']);

        if ($request->filled('type_id')) {
            $query->where('notification_type_id', $request->type_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $notifications = $query->latest('id')->paginate(15);
        $types = NotificationType::where('is_active', 1)->get();
        $users = User::select('id', 'full_name', 'phone_encrypted')->limit(50)->get();

        return view('admin.marketing.notifications', compact('notifications', 'types', 'users'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'body' => 'required|string',
            'notification_type_id' => 'required|exists:notification_types,id',
            'target_audience' => 'required|in:all,specific',
            'user_id' => 'nullable|required_if:target_audience,specific|exists:users,id',
        ]);

        if ($request->target_audience === 'all') {
            $users = User::where('account_status', 'active')->get();
            foreach ($users as $u) {
                Notification::create([
                    'user_id' => $u->id,
                    'notification_type_id' => $request->notification_type_id,
                    'title' => $request->title,
                    'body' => $request->body,
                    'is_read' => 0,
                ]);
            }
        } else {
            Notification::create([
                'user_id' => $request->user_id,
                'notification_type_id' => $request->notification_type_id,
                'title' => $request->title,
                'body' => $request->body,
                'is_read' => 0,
            ]);
        }

        return back()->with('success', 'تم إرسال الإشعار بنجاح إلى التطبيق.');
    }

    public function destroy($id)
    {
        $notif = Notification::findOrFail($id);
        $notif->delete();

        return back()->with('success', 'تم حذف الإشعار بنجاح.');
    }
}
