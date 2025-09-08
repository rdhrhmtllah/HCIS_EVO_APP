<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class userController extends Controller
{
    public function index(){
        $Kode_KaryawanReal = Auth::user()->karyawan->Kode_Karyawan;

        $nama_anggota = DB::table('HRIS_Karyawan_Team as t')
                    ->join('Karyawan as k', 't.Kode_Karyawan', '=', 'k.Kode_Karyawan')
                    ->join('View_Divisi_Sub_Divisi as d', 'k.ID_Divisi_Sub_Divisi', '=', 'd.ID_DIVISI_SUB_DIVISI')
                    ->where('t.Kode_Karyawan_Team', $Kode_KaryawanReal)
                    ->select('k.Nama', 'k.HP', 'd.nama_divisi as divisi')
                    ->groupBy('k.HP', 'd.nama_divisi ', 'k.Nama')
                    ->get();

        $nama_tergabung = DB::table('HRIS_Karyawan_Team as t')
            ->join('Karyawan as k', 't.Kode_Karyawan_Team', '=', 'k.Kode_Karyawan')
            ->join('View_Divisi_Sub_Divisi as d', 'k.ID_Divisi_Sub_Divisi', '=', 'd.ID_DIVISI_SUB_DIVISI')
            ->where('t.Kode_Karyawan', $Kode_KaryawanReal)
            ->select('k.Nama', 'k.HP', 'd.nama_divisi as divisi')
            ->groupBy('k.Nama', 'k.HP', 'd.nama_divisi')
            ->where("t.Kode_Karyawan_Team" ,'<>', $Kode_KaryawanReal)
            ->where("t.Kode_Karyawan_Team" ,'<>', 'RIDHO RAHMAT')
            ->get();

        $nama_Approver =DB::table('HRIS_Approval_Flow as t')
            ->join('Karyawan as k', 't.Kode_Karyawan', '=', 'k.Kode_Karyawan')
            ->where('t.Kode_Karyawan_Requester', $Kode_KaryawanReal)
            ->select('k.Nama', 't.order_flow')
            ->groupBy('k.Nama', 't.order_flow')
            ->where("t.Kode_Karyawan" ,'<>', $Kode_KaryawanReal)
            ->whereNull("t.Status")
            ->get();

        $nama_mengapprove =DB::table('HRIS_Approval_Flow as t')
            ->join('Karyawan as k', 't.Kode_Karyawan_Requester', '=', 'k.Kode_Karyawan')
            ->join('View_Divisi_Sub_Divisi as d', 'k.ID_Divisi_Sub_Divisi', '=', 'd.ID_DIVISI_SUB_DIVISI')
            ->where('t.Kode_Karyawan', $Kode_KaryawanReal)
            ->select('k.Nama', 'd.nama_divisi as divisi')
            ->groupBy('k.Nama', 'd.nama_divisi')
            ->where("t.Kode_Karyawan_Requester" ,'<>', $Kode_KaryawanReal)
            ->get();


        return inertia('userProfile', [
            'user' => Auth::user()->karyawan,
            'divisi' => Auth::user()->karyawan->division->nama_divisi,
            'nama_anggota' => $nama_anggota,
            'nama_tergabung' => $nama_tergabung,
            'nama_Approver' => $nama_Approver,
            'nama_mengapprove' => $nama_mengapprove,
        ]);
    }
}
