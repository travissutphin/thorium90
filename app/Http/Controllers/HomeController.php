<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the homepage.
     */
    public function index()
    {
        $content = [
            'title' => 'Content Management Redefined',
            'excerpt' => 'Experience the power of AI-driven content management with human verification. Build, manage, and scale your digital presence with confidence.'
        ];

        return view('public.home', compact('content'));
    }
}