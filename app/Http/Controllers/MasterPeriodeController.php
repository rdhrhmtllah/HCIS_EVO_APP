<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\MasterPeriode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MasterPeriodeController extends Controller
{
    public function index()
    {
        $title = "Periode";
        $data = MasterPeriode::all();
        return view('master_periode.index', ['data' => $data, 'title' => $title]);
    }
    public function display_tambah_periode()
    {
        $title = "Tambah Periode";
        // $data = MasterPeriode::all();

        $query = "select Id_Division, Name from KPI_Divisions a, KPI_User_Divisions b where ";
        $query .= "a.Id_Division = b.Division_Id and  b.User_Id = :Id_User ";
        // dd(Auth::user()->Id_Users);
        $division = DB::select($query, [ 
            'Id_User' => Auth::user()->Id_Users
        ]);

        // dd($division);
        // $division = Division::select('Id_Division', 'Name')->where('Id_Division', Auth::user()->Division_Id)->get();
        return view('master_periode.tambah', ['title' => $title, 'division' => $division]);
    }

    public function tambah_periode(Request $request)
    {
        $request->validate([
            'kode_periode' => 'required',
            'keterangan' => 'required',
            'tanggal_awal' => 'required',
            'tanggal_akhir' => 'required',
            'division_id' => 'required'
        ], [
            'kode_periode' => ':attribute harus di isi',
            'keterangan' => ':attribute harus di isi',
            'tanggal_awal' => ':attribute harus di isi',
            'tanggal_akhir' => ':attribute harus di isi',
            'division_id' => ':attribute harus di isi',
        ]);
        // dd($request->all());
        try {
            DB::beginTransaction();

            $checkData = MasterPeriode::where([
                'Kode_Periode' =>
                $request->input('kode_periode'),
                'Division_Id' => $request->input('division_id')
            ])->first();

            if ($checkData) {
                DB::rollBack();
                return back()->with('pesan', 'Data periode sudah pernah disimpan');
            }

            MasterPeriode::create([
                'Kode_Periode' => $request->input('kode_periode'),
                'Keterangan' => $request->input('keterangan'),
                'Tanggal_Awal' => $request->input('tanggal_awal'),
                'Tanggal_Akhir' => $request->input('tanggal_akhir'),
                'Division_Id' => $request->input('division_id'),
                'User_Id' => Auth::user()->Id_Users
            ]);

            DB::commit();

            return back()->with('pesan', 'Data berhasil disimpan');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('pesan', 'Terjadi kesalahan: ' . $th->getMessage());
        } catch (ValidationException $e) {
            // Tangkap error validasi
            return back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('pesan', 'Validasi gagal, cek kembali inputan.');
        }
    }
}
