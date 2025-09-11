<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use RealRashid\SweetAlert\Facades\Alert;
use App\Helpers\Whatsapp;
use Illuminate\Support\Facades\Log;

class perbaikiCheckinoutController extends Controller
{
    //

    public function index(){

        $data = DB::table('Karyawan as a')
                ->join('View_Divisi_Sub_Divisi as b', 'a.ID_Divisi_Sub_Divisi', '=','b.ID_DIVISI_SUB_DIVISI')
                ->join('View_Golongan_Sub_Golongan_Level_Jabatan as c', 'a.ID_Level_Jabatan', '=', 'c.ID_Level_Jabatan')
                ->select('a.Kode_Karyawan','a.Nama', 'a.UserID_Absen', 'b.ID_Divisi' ,'b.nama_divisi','b.nama_sub_divisi', 'c.nama_jabatan')
                ->where('Aktif', 'Y')
                ->whereNull('Tanggal_Resign')
                ->get();
        $divisi = DB::table('View_Divisi_Sub_Divisi')
                    ->select('ID_Divisi', 'nama_divisi')
                    ->groupBy('ID_Divisi', 'nama_divisi')
                    ->get();


        return inertia('perbaikanCheckInOut', ['dataKaryawan' => $data, 'allDivisi' => $divisi]);
    }


    public function attendance(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|array',
            'employee_id.UserID_Absen' => 'required',
            'date' => 'required|array|size:2',
            'date.0' => 'required|date',
            'date.1' => 'required|date|after_or_equal:date.0',
        ]);

        try {
            $start = $validated['date'][0];
            $end = $validated['date'][1];
            $UserID_Absen = $validated['employee_id']['UserID_Absen'];

            $query = "EXEC HRIS_SP_GET_ABSEN_NEW_V3 ?, ?, ?, ?, ?";
            $params = [
                '001',
                $start,
                $end,
                $UserID_Absen,
                ''
            ];

            $data = collect(DB::select($query, $params))
            ->map(function ($row) {
                return [
                    'Tanggal' => $row->Tanggal,
                    'Nama_Shift' => $row->Nama_Shift,
                    'CheckIn' => $row->CheckIn,
                    'CheckOut' => $row->CheckOut,
                    'Status_Kehadiran' => $row->Status_Kehadiran,
                ];
            });
            // dd($data);
            return response()->json([
                'status' => 200,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::channel('CreateUserLog')->error('Error eksekusi attendance', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

}
