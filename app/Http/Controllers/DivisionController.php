<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function index(){
        $title = "Divisi";
        $data = Division::all();
        return view('division.index',['data'=> $data, 'title' => $title]);
    }
}
