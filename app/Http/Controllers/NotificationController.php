<?php

namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('UserID', session('user_id'))
                                     ->where('UserType', session('user_type'))
                                     ->latest()
                                     ->get();

        return view('notifications.index', compact('notifications'));
    }
}
