<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifikasi = Notification::with(['dariUser','task.project','project'])
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(20);

        // Tandai semua dibaca
        Notification::where('user_id', auth()->id())
            ->where('dibaca', false)
            ->update(['dibaca' => true, 'dibaca_at' => now()]);

        return view('notification.index', compact('notifikasi'));
    }

    public function hapusSemua()
    {
        Notification::where('user_id', auth()->id())->delete();
        return back()->with('success', 'Semua notifikasi dihapus.');
    }

    public function getUnread()
    {
        $count = Notification::where('user_id', auth()->id())->where('dibaca', false)->count();
        $items = Notification::with(['dariUser','task'])
            ->where('user_id', auth()->id())
            ->where('dibaca', false)
            ->orderByDesc('created_at')
            ->take(5)->get();

        return response()->json(['count' => $count, 'items' => $items]);
    }
}
