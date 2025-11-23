<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class RapportsController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_raports', only: ['index']),
        ];
    }


    /**
     * Handle the incoming request.
     */
    public function index(Request $request)
    {
        return Inertia::render('Rapports/Index');
    }
}
