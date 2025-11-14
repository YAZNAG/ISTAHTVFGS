<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function read(Request $request, $notification) {

        $notification = $request->user()->notifications()->find($notification);
        $notification->markAsRead();

        $url = data_get($notification->data, 'url');

        if ($url) {
            return Inertia::location($url);
        } 

        return redirect()->back();
        
    }

    public function readAll(Request $request) {
        $request->user()->unreadNotifications->markAsRead();
        return redirect()->back();
    }
}