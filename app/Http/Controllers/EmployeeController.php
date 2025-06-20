<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(){
        $divisi = Division::all();
        $title = "Karyawan";
        $data = User::all();
        return view('employee.index',['data'=> $data, 'title' => $title,'divisions'=>$divisi]);
    }
}
