<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function read(Request $request, $notification) {

        $notification = $request->user()->notifications()->find($notification);
        $notification->markAsRead();

        return Inertia::location($notification->data['url']);
        
    }
}