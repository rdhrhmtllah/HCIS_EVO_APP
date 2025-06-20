<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    public function index(){
        $title = "Jabatan";
        $data = Level::all();
        return view('level.index',['data'=> $data, 'title' => $title]);
    }
}
