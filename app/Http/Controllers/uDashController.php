<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class uDashController extends Controller
{
    public function index(){
        $title = 'uDash for You';

        return inertia('uDash', ['title' => $title]);
    }
}
