<?php

namespace App\Http\Controllers;

use App\Models\StaffNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StaffNotificationController extends Controller
{
    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $item = StaffNotification::where('_id', $notification)
            ->where('user_id', (string) $request->user()->_id)
            ->firstOrFail();

        if (! $item->read_at) {
            $item->read_at = now();
            $item->save();
        }

        if ($item->link) {
            return redirect()->to($item->link);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        StaffNotification::where('user_id', (string) $request->user()->_id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
