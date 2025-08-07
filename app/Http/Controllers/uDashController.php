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


class uDashController extends Controller
{
    public function index(){
        try{


            $title = 'uDash for You';
            $now = Carbon::now();
            $cutoffDateThisMonth = $now->copy()->day(20);

            if ($now->gt($cutoffDateThisMonth)) {
                // Jika hari ini sudah lewat tanggal 20 bulan ini
                $startWeek = $cutoffDateThisMonth->copy()->addDay(); // Tanggal 21 bulan ini
                $endWeek = $cutoffDateThisMonth->copy()->addMonth(); // Tanggal 20 bulan depan
            } else {
                // Jika hari ini belum lewat tanggal 20 bulan ini
                $endWeek = $cutoffDateThisMonth->copy(); // Tanggal 20 bulan ini
                $startWeek = $cutoffDateThisMonth->copy()->subMonth()->addDay(); // Tanggal 21 bulan lalu
            }

            $UserID_Absen = Auth::user()->karyawan->UserID_Absen;
            $UserID_Web = Auth::user()->karyawan->UserID_Web;
            $Id_Division = Auth::user()->karyawan->division->ID_Divisi;
            $Id_Level = Auth::user()->karyawan->level->ID_Level;
            $Kode_Karyawan = Auth::user()->karyawan->Kode_Karyawan;
            // dd($Id_Level);

            // Tentukan rentang tanggal
            // Ambil data absensi utama dari stored procedure
            $query = "exec HRIS_SP_GET_ABSEN_NEW '001', '{$startWeek}', '{$endWeek}', '{$UserID_Absen}', ''";
            $absen_data = collect(DB::select($query));
            // dd($absen_data);
            // Ambil semua tanggal unik dari data absen untuk dijadikan filter
            $tanggal_absens = $absen_data->pluck('Tanggal')->map(fn($date) => Carbon::parse($date)->format('Y-m-d'));
            $CHECKIN = $absen_data->pluck('CheckIn')->map(fn($date) => Carbon::parse($date)->format('Y-m-d h:i:s'));

        $total_keterlambatan_detik = 0;

            $filtered_data = $absen_data->filter(function ($item) {
                if (is_null($item->CheckIn)) {
                    return false;
                }

                $check_in_time = Carbon::parse($item->CheckIn)->format('H:i:s');
                return $check_in_time > $item->Jam_Masuk;
            });

            foreach ($filtered_data as $item) {
                $check_in_carbon = Carbon::parse($item->CheckIn);
                $jam_masuk_value = $item->Jam_Masuk;
                if (strlen($jam_masuk_value) === 5) { // Contoh: "08:30"
                    $format_jam = 'H:i';
                } else { // Contoh: "08:30:00"
                    $format_jam = 'H:i:s';
                }

                $jam_masuk_carbon = Carbon::createFromFormat('Y-m-d ' . $format_jam, $check_in_carbon->format('Y-m-d') . ' ' . $jam_masuk_value);
                $total_keterlambatan_detik += $check_in_carbon->diffInSeconds($jam_masuk_carbon);
            }

                // Konversi total detik menjadi jam desimal
                    // $total_keterlambatan_jam = round($total_keterlambatan_detik / 3600, 2);
                    $total_keterlambatan_menit = round($total_keterlambatan_detik / 60, 2);

                // --- Pre-load Semua Data Transaksi dalam Beberapa Query ---

                // 1. Pre-load data lembur
                $lembur_data = DB::table('Transaksi_Lembur_Detail as b')
                    ->select('a.No_Transaksi', 'b.Tanggal_Lembur_dari as mulai', 'b.Tanggal_Lembur_Sampai as selesai')
                ->join('Transaksi_Lembur as a', 'a.No_Transaksi', '=', 'b.No_Transaksi')
                ->where('b.Kode_Karyawan', $Kode_Karyawan)
                ->where(function($query) use ($tanggal_absens) {
                    $query->whereIn(DB::raw('CAST(b.Tanggal_Lembur_dari AS DATE)'), $tanggal_absens)
                        ->orWhereIn(DB::raw('CAST(b.Tanggal_Lembur_Sampai AS DATE)'), $tanggal_absens);
                })
                ->get();

                $tanggal_lembur_valid = $lembur_data->pluck('mulai')->map(fn($date) => Carbon::parse($date)->toDateString())->toArray();

                // --- Langkah 2: Filter data absen berdasarkan tanggal 'CheckIn' atau 'Tanggal_Absen' ---
                $filtered_data_lembur = $absen_data->filter(function ($absen_item) use ($tanggal_lembur_valid) {
                    // Ganti 'CheckIn' dengan nama kolom yang benar-benar berisi tanggal absen
                    if (is_null($absen_item->CheckIn)) {
                        return false;
                    }

                    $tanggal_absensi = Carbon::parse($absen_item->CheckIn)->toDateString();

                    return in_array($tanggal_absensi, $tanggal_lembur_valid);
                });

                // dd($filtered_data_lembur);

                // --- Langkah 3: Jumlahkan total menit lembur dari data yang sudah difilter ---
                $total_lembur_menit = $filtered_data_lembur->sum('Overtime');

                // Konversi ke jam desimal
                $total_lembur_jam = round($total_lembur_menit / 60, 2);
                // dd($filtered_data_lembur);
                $today = Carbon::today()->toDateString();
                $tomorrow = Carbon::tomorrow()->toDateString();

                $absen_hari_ini = $absen_data->first(function ($item) use ($today) {
                    if (is_null($item->Tanggal)) {
                        return false;
                    }

                    // Bandingkan tanggal dari item dengan tanggal hari ini
                    return Carbon::parse($item->Tanggal)->toDateString() === $today;
                });

                $tanggalBesok  = Carbon::tomorrow();
                // dd($tanggalBesok);
                $queryBesok = "exec HRIS_SP_GET_ABSEN_NEW '001', '{$tanggalBesok}', '{$tanggalBesok}', '{$UserID_Absen}', ''";
                $absen_besok_raw = DB::Select($queryBesok);
                $absen_besok = collect($absen_besok_raw)->first();
                // dd($absen_besok->Nama_Shift);
                // $absen_besok = $absen_data->first(function ($item) use ($tomorrow) {
                //     if (is_null($item->Tanggal)) {
                //         return false;
                //     }

                //     return Carbon::parse($item->Tanggal)->toDateString() === $tomorrow;
                // });


                $accessPage = DB::table('HRIS_Page_Access')->select("Jenis_page")->where("UserID_Web", $UserID_Web)->where("ID_Level",$Id_Level)->where("ID_Divisi", $Id_Division)->get();
                // dd($accessPage);
                $sisaCuti = (int) Auth::user()->karyawan->sisa_cuti();
                $namaKaryawan = Auth::user()->karyawan->Nama;

                $berita = DB::table('HRIS_News_Dashboard')
                            ->orderBy("Id_News", 'desc')
                            ->first();

            return inertia('uDash', [
                'title' => $title,
                'accessPage' => $accessPage,
                'namaKaryawan' =>$namaKaryawan,
                'shiftHariIni' => $absen_hari_ini->Nama_Shift,
                'Jam_Masuk' => $absen_hari_ini->Jam_Masuk,
                'Jam_Keluar'=> $absen_hari_ini->Jam_Keluar,
                'shiftBesok' => $absen_besok->Nama_Shift,
                'totalTerlambat' => $total_keterlambatan_menit,
                'totalLembur' => $total_lembur_jam,
                'sisaCuti' => $sisaCuti,
                'judulBerita' => $berita->Judul,
                'isiBerita' =>$berita->Content


            ]);
        }catch (\Throwable $e) {
            Log::channel('uDashLog')->error('Gagal menampilkan page Dashboard: ' . $e->getMessage());
            Alert::error('error', 'Terjadi Error saat menampilkan page Dashboard!');
            return back();
        }
    }

    public function getDateAbsen(Request $request){
        // Dapatkan data karyawan yang sedang login
        try{


            $UserID_Absen = Auth::user()->karyawan->UserID_Absen;
            $Kode_Karyawan = Auth::user()->karyawan->Kode_Karyawan;
            $requestDate = $request->tanggal;
            $nowDate = $requestDate ? Carbon::parse($requestDate) : Carbon::now();

            if ($requestDate && $request->jenis == 'next') {
                // Mendapatkan Senin minggu depan
                $date = $nowDate->next(Carbon::MONDAY)->next(Carbon::MONDAY);
            } else if ($requestDate && $request->jenis == 'prev') {
                // Mendapatkan Senin minggu lalu
                $date = $nowDate->previous(Carbon::MONDAY);
            } else {
                $date = Carbon::now();
            }
            $startWeek = $date->startOfWeek(Carbon::SUNDAY)->format('Y-m-d');
            $endWeek = $date->endOfWeek(Carbon::SATURDAY)->format('Y-m-d');
            // Tentukan rentang tanggal

            // Ambil data absensi utama dari stored procedure
            $query = "exec HRIS_SP_GET_ABSEN_NEW '001', '{$startWeek}', '{$endWeek}', '{$UserID_Absen}', ''";
            $absen_data = collect(DB::select($query));

            // Ambil semua tanggal unik dari data absen untuk dijadikan filter
            $tanggal_absens = $absen_data->pluck('Tanggal')->map(fn($date) => Carbon::parse($date)->format('Y-m-d'));


            // --- Pre-load Semua Data Transaksi dalam Beberapa Query ---

            // 1. Pre-load data lembur
            $lembur_data = DB::table('Transaksi_Lembur_Detail as b')
                ->select('a.No_Transaksi', 'b.Tanggal_Lembur_dari as mulai', 'b.Tanggal_Lembur_Sampai as selesai')
                ->join('Transaksi_Lembur as a', 'a.No_Transaksi', '=', 'b.No_Transaksi')
                ->where('b.Kode_Karyawan', $Kode_Karyawan)
                ->where("a.Status", null)
                ->where(function($query) use ($tanggal_absens) {
                    $query->whereIn(DB::raw('CAST(b.Tanggal_Lembur_dari AS DATE)'), $tanggal_absens)
                        ->orWhereIn(DB::raw('CAST(b.Tanggal_Lembur_Sampai AS DATE)'), $tanggal_absens);
                })
                ->get();
            // 2. Pre-load data sakit/izin
            $sakit_data = DB::table('Transaksi_Sakit_Izin_Detail as b')
                ->select('a.No_Transaksi', 'b.Tanggal_Sakit_Izin_dari as mulai', 'b.Tanggal_Sakit_Izin_Sampai as selesai')
                ->join('Transaksi_Sakit_Izin as a', 'a.No_Transaksi', '=', 'b.No_Transaksi')
                ->where('b.Kode_Karyawan', $Kode_Karyawan)
                ->where("b.Jenis", "sakit")
                ->where('b.Flag_Approval', 'Y')
                ->where("a.Status", null)
                ->where(function($query) use ($tanggal_absens) {
                    $query->whereIn(DB::raw('CAST(b.Tanggal_Sakit_Izin_dari AS DATE)'), $tanggal_absens)
                        ->orWhereIn(DB::raw('CAST(b.Tanggal_Sakit_Izin_Sampai AS DATE)'), $tanggal_absens);
                })
                ->get();
            $izin_data = DB::table('Transaksi_Sakit_Izin_Detail as b')
                ->select('a.No_Transaksi', 'b.Tanggal_Sakit_Izin_dari as mulai', 'b.Tanggal_Sakit_Izin_Sampai as selesai')
                ->join('Transaksi_Sakit_Izin as a', 'a.No_Transaksi', '=', 'b.No_Transaksi')
                ->where('b.Kode_Karyawan', $Kode_Karyawan)
                ->where("b.Jenis", "izinFull")
                ->where('b.Flag_Approval', 'Y')
                ->where("a.Status", null)
                ->where(function($query) use ($tanggal_absens) {
                    $query->whereIn(DB::raw('CAST(b.Tanggal_Sakit_Izin_dari AS DATE)'), $tanggal_absens)
                        ->orWhereIn(DB::raw('CAST(b.Tanggal_Sakit_Izin_Sampai AS DATE)'), $tanggal_absens);
                })
                ->get();

            // 3. Pre-load data cuti
            $cuti_data = DB::table('Transaksi_Cuti_Detail as b')
                ->select('a.No_Transaksi', 'b.Tanggal_Cuti_dari as mulai', 'b.Tanggal_Cuti_Sampai as selesai')
                ->join('Transaksi_Cuti as a', 'a.No_Transaksi', '=', 'b.No_Transaksi')
                ->where('b.Kode_Karyawan', $Kode_Karyawan)
                ->where('b.Flag_Approval', 'Y')
                ->where("b.Jenis", "cuti")
                ->where("a.Status", null)
                ->where(function($query) use ($tanggal_absens) {
                    $query->whereIn(DB::raw('CAST(b.Tanggal_Cuti_dari AS DATE)'), $tanggal_absens)
                        ->orWhereIn(DB::raw('CAST(b.Tanggal_Cuti_Sampai AS DATE)'), $tanggal_absens);
                })
                ->get();
            $cutiHutang_data = DB::table('Transaksi_Cuti_Detail as b')
                ->select('a.No_Transaksi', 'b.Tanggal_Cuti_dari as mulai', 'b.Tanggal_Cuti_Sampai as selesai')
                ->join('Transaksi_Cuti as a', 'a.No_Transaksi', '=', 'b.No_Transaksi')
                ->where('b.Kode_Karyawan', $Kode_Karyawan)
                ->where("b.Jenis", "cutiHutang")
                ->where('b.Flag_Approval', 'Y')
                ->where("a.Status", null)
                ->where(function($query) use ($tanggal_absens) {
                    $query->whereIn(DB::raw('CAST(b.Tanggal_Cuti_dari AS DATE)'), $tanggal_absens)
                        ->orWhereIn(DB::raw('CAST(b.Tanggal_Cuti_Sampai AS DATE)'), $tanggal_absens);
                })
                ->get();

                // 4. Pre-load data terlambat/pulang cepat
                $pulang_data = DB::table('Transaksi_Terlambat_Pulang_Cepat_Detail as b')
                    ->select('a.No_Transaksi', 'b.Tanggal_Masuk_Pulang as Tanggal')
                    ->join('Transaksi_Terlambat_Pulang_Cepat as a', 'a.No_Transaksi', '=', 'b.No_Transaksi')
                    ->where('b.Kode_Karyawan', $Kode_Karyawan)
                    ->where("b.Jenis", "pulangCepat")
                    ->where('b.Flag_Approval', 'Y')
                    ->where("a.Status", null)
                    ->whereIn(DB::raw('CAST(b.Tanggal_Masuk_Pulang AS DATE)'), $tanggal_absens)
                    ->get();

                $terlambat_data = DB::table('Transaksi_Terlambat_Pulang_Cepat_Detail as b')
                    ->select('a.No_Transaksi', 'b.Tanggal_Masuk_Pulang as Tanggal')
                    ->join('Transaksi_Terlambat_Pulang_Cepat as a', 'a.No_Transaksi', '=', 'b.No_Transaksi')
                    ->where('b.Kode_Karyawan', $Kode_Karyawan)
                    ->where('b.Flag_Approval', 'Y')
                    ->where("b.Jenis", "terlambat")
                    ->where("a.Status", null)
                    ->whereIn(DB::raw('CAST(b.Tanggal_Masuk_Pulang AS DATE)'), $tanggal_absens)
                    ->get();

                // --- Proses Data Absen dengan Data yang Sudah Di-preload ---

                $result = $absen_data->map(function ($item) use ($lembur_data, $sakit_data,$izin_data, $cuti_data,$cutiHutang_data, $pulang_data, $terlambat_data) {
                $tanggal_formatted = Carbon::parse($item->Tanggal)->format('Y-m-d');

                // Cari data di dalam Collection yang sudah di-load di awal
                $lembur = $lembur_data->where(function($item) use ($tanggal_formatted){

                $mulai = Carbon::parse($item->mulai)->format('Y-m-d');
                $selesai = Carbon::parse($item->selesai)->format('Y-m-d');

                return Carbon::parse($tanggal_formatted)->between($mulai, $selesai);
                })->first();
                $sakit = $sakit_data->where('mulai', '<=', $tanggal_formatted)->where('selesai', '>=', $tanggal_formatted)->first();
                $izin = $izin_data->where('mulai', '<=', $tanggal_formatted)->where('selesai', '>=', $tanggal_formatted)->first();
                $cuti = $cuti_data->where('mulai', '<=', $tanggal_formatted)->where('selesai', '>=', $tanggal_formatted)->first();
                $cutiHutang = $cutiHutang_data->where('mulai', '<=', $tanggal_formatted)->where('selesai', '>=', $tanggal_formatted)->first();
                $pulang = $pulang_data->where('Tanggal', $tanggal_formatted)->first();
                $terlambat = $terlambat_data->where('Tanggal', $tanggal_formatted)->first();

                return [
                    'Nama_Shift' => $item->Nama_Shift,
                    'CheckIn' => $item->CheckIn,
                    'CheckOut' => $item->CheckOut,
                    'Jam_Masuk' => $item->Jam_Masuk,
                    'Jam_Keluar' => $item->Jam_Keluar,
                    'Tanggal' => $item->Tanggal,
                    'Lembur_No_Transaksi' => $lembur->No_Transaksi ?? null,
                    'Lembur_Mulai' => $lembur->mulai ?? null,
                    'Lembur_Selesai' => $lembur->selesai ?? null,
                    'Sakit_No_Transaksi' => $sakit->No_Transaksi ?? null,
                    'Sakit_Mulai' => $sakit->mulai ?? null,
                    'Sakit_Selesai' => $sakit->selesai ?? null,
                    'Izin_No_Transaksi' => $izin->No_Transaksi ?? null,
                    'Izin_Mulai' => $izin->mulai ?? null,
                    'Izin_Selesai' => $izin->selesai ?? null,
                    'Cuti_No_Transaksi' => $cuti->No_Transaksi ?? null,
                    'Cuti_Mulai' => $cuti->mulai ?? null,
                    'Cuti_Selesai' => $cuti->selesai ?? null,
                    'CutiHutang_No_Transaksi' => $cutiHutang->No_Transaksi ?? null,
                    'CutiHutang_Mulai' => $cutiHutang->mulai ?? null,
                    'CutiHutang_Selesai' => $cutiHutang->selesai ?? null,
                    'Pulang_No_Transaksi' => $pulang->No_Transaksi ?? null,
                    'Pulang_Tanggal' => $pulang->Tanggal ?? null,
                    'Terlambat_No_Transaksi' => $terlambat->No_Transaksi ?? null,
                    'Terlambat_Tanggal' => $terlambat->Tanggal ?? null,
                ];
            });


            return response()->json([
                'status' => 200,
                'data' => $result ?? []
            ]);
        }catch(\Throwable $e){
            Log::channel('uDashLog')->error('Gagal Mengambil data data Absens getDateAbsen'. $e->getMessage());
            return response()->json([
                'status' => 500,
                'error' => "Tidak bisa mengambil data data Absens"
            ], 500);
        }
    }

    public function getDataShift(Request $request){
        try{


            $UserID_Absen = Auth::user()->karyawan->UserID_Absen;
            $Kode_Karyawan = Auth::user()->karyawan->Kode_Karyawan;
            $requestDate = $request->tanggal;
            $nowDate = $requestDate ? Carbon::parse($requestDate) : Carbon::now();

            if ($requestDate && $request->jenis == 'next') {
                // Mendapatkan Senin minggu depan
                $date = $nowDate->next(Carbon::MONDAY)->next(Carbon::MONDAY);
            } else if ($requestDate && $request->jenis == 'prev') {
                // Mendapatkan Senin minggu lalu
                $date = $nowDate->previous(Carbon::MONDAY);
            } else {
                $date = Carbon::now();
            }
            $startWeek = $date->startOfWeek(Carbon::SUNDAY)->format('Y-m-d');
            $endWeek = $date->endOfWeek(Carbon::SATURDAY)->format('Y-m-d');
            // Tentukan rentang tanggal

            // Ambil data absensi utama dari stored procedure
            $query = "exec HRIS_SP_GET_ABSEN_NEW '001', '{$startWeek}', '{$endWeek}', '{$UserID_Absen}', ''";

            $absen_data = collect(DB::select($query))->map(function($item) {
                return[
                    'Nama_Shift' => $item->Nama_Shift,
                    'Jam_Masuk' => $item->Jam_Masuk,
                    'Jam_Keluar' => $item->Jam_Keluar,
                    'Tanggal' => $item->Tanggal,

                ];
            });


            return response()->json([
                'status' => 200,
                'data' => $absen_data ?? []
            ]);
        }catch(\Throwable $e){
            Log::channel('uDashLog')->error('Gagal Mengambil data shift getDataShift'. $e->getMessage());
            return response()->json([
                'status' => 500,
                'error' => "Tidak bisa mengambil data shift"
            ], 500);
        }
    }

    public function getAllIzin(){
        try{


            $perPage = 4;
            $currentPage = request()->get('page', 1);
            $UserID_Absen = Auth::user()->karyawan->UserID_Absen;
            $Kode_Karyawan = Auth::user()->karyawan->Kode_Karyawan;
            $query = "
                        WITH
                            cte AS (
                            SELECT
                                CAST(CONCAT(CAST(cti.Tanggal AS DATE),' ', cti.Jam) AS DATETIME) AS tanggal_create,
                                k.Kode_Karyawan,
                                k.Nama,
                                ct.No_Transaksi,
                                ct.Jenis as Tipe_Izin,
                                ct.Tanggal_Cuti_Dari AS Tanggal_Mulai,
                                ct.Tanggal_Cuti_Sampai AS Tanggal_Selesai,
                                ct.Alasan,
                                ct.filePath as Lampiran,
                                ISNULL(
                                    (
                                    SELECT
                                        TOP 1 af.order_flow
                                    FROM
                                        HRIS_Approval_Request ar
                                        JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                    WHERE
                                        ar.No_Transaksi = ct.No_Transaksi
                                        AND ar.Flag_Approval IS NOT NULL
                                    ORDER BY
                                        af.order_flow DESC
                                    ),
                                    0
                                ) as currentFlow,
                                ISNULL(
                                    (
                                    select
                                        top 1 order_flow
                                    from
                                        HRIS_Approval_Flow a,
                                        HRIS_Approval_Request b
                                    where
                                        a.id = b.Flow_Id
                                        and b.No_Transaksi = ct.No_Transaksi
                                    order by
                                        a.order_flow desc
                                    ),
                                    0
                                ) as MaxFlow,
                                CASE
                                    WHEN ct.Flag_Approval = 'Y' THEN 'Selesai'
                                    WHEN ct.Flag_Approval = 'T' THEN 'Ditolak'
                                    ELSE ISNULL(
                                    (
                                        SELECT
                                        TOP 1 'Diproses'
                                        FROM
                                        HRIS_Approval_Request ar
                                        JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                        WHERE
                                        ar.No_Transaksi = ct.No_Transaksi
                                        AND ar.Kode_Karyawan = k.Kode_Karyawan
                                        AND ar.Flag_Approval IS NULL
                                        ORDER BY
                                        af.order_flow
                                    ),
                                    'NO'
                                    )
                                END as status,
                                CASE
                                    WHEN ct.Flag_Approval = 'Y' THEN 'DiSetujui'
                                    WHEN ct.Flag_Approval = 'T' THEN (
                                    select
                                        top 1 k.Nama
                                    FROM
                                        HRIS_Approval_Request ar
                                        JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                    WHERE
                                        ar.No_Transaksi = ct.No_Transaksi
                                        AND ar.Kode_Karyawan = k.Kode_Karyawan
                                        AND ar.Flag_Approval = 'T'
                                    ORDER BY
                                        af.order_flow
                                    )
                                    ELSE ISNULL(
                                    (
                                        SELECT
                                        TOP 1 ka.Nama
                                        FROM
                                        HRIS_Approval_Request ar
                                        JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                        JOIN
                                            Karyawan ka ON af.Kode_Karyawan = ka.Kode_Karyawan
                                        WHERE
                                        ar.No_Transaksi = ct.No_Transaksi
                                        AND ar.Flag_Approval IS NULL
                                        ORDER BY
                                        af.order_flow
                                    ),
                                    null
                                    )
                                END AS Approver,
                                NULL AS Jam_Mulai,
                                (
                                    select
                                    STRING_AGG(
                                        CONCAT(
                                        b.Kode_Karyawan,
                                        ': ',
                                        CASE
                                            WHEN a.Flag_Approval IS NOT NULL THEN a.Flag_Approval
                                            ELSE 'N'
                                        END
                                        ),
                                        ', '
                                    ) WITHIN GROUP (
                                        ORDER BY
                                        b.order_flow
                                    ) AS StatusList
                                    from
                                    HRIS_Approval_Request a
                                    JOIN HRIS_Approval_Flow b ON a.Flow_Id = b.id
                                    where
                                    a.No_Transaksi = ct.No_Transaksi
                                ) AS STATUSLIST
                                FROM
                                Karyawan AS k
                                INNER JOIN Transaksi_Cuti_Detail AS ct ON k.Kode_Karyawan = ct.Kode_Karyawan
                                INNER JOIN Transaksi_Cuti AS cti ON ct.No_Transaksi = cti.No_Transaksi
                                WHERE
                                k.Kode_Karyawan = ?
                                and cti.Status is null
                                UNION ALL
                                -- Query untuk Transaksi_Sakit_Izin_Detail
                                SELECT
                                CAST(CONCAT(CAST(tsip.Tanggal AS DATE),' ', tsip.Jam) AS DATETIME) AS tanggal_create,
                                k.Kode_Karyawan,
                                k.Nama,
                                tsi.No_Transaksi,
                                tsi.Jenis as Tipe_Izin,
                                tsi.Tanggal_Sakit_Izin_Dari AS Tanggal_Mulai,
                                tsi.Tanggal_Sakit_Izin_Sampai AS Tanggal_Selesai,
                                tsi.Alasan,
                                tsi.filePath as Lampiran,
                                ISNULL(
                                    (
                                    SELECT
                                        TOP 1 af.order_flow
                                    FROM
                                        HRIS_Approval_Request ar
                                        JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                    WHERE
                                        ar.No_Transaksi = tsi.No_Transaksi
                                        AND ar.Flag_Approval IS NOT NULL
                                    ORDER BY
                                        af.order_flow DESC
                                    ),
                                    0
                                ) as currentFlow,
                                ISNULL(
                                    (
                                    select
                                        top 1 order_flow
                                    from
                                        HRIS_Approval_Flow a,
                                        HRIS_Approval_Request b
                                    where
                                        a.id = b.Flow_Id
                                        and b.No_Transaksi = tsi.No_Transaksi
                                    order by
                                        a.order_flow desc
                                    ),
                                    0
                                ) as MaxFlow,
                                CASE
                                    WHEN tsi.Flag_Approval = 'Y' THEN 'Selesai'
                                    WHEN tsi.Flag_Approval = 'T' THEN 'Ditolak'
                                    ELSE ISNULL(
                                    (
                                        SELECT
                                        TOP 1 'Diproses'
                                        FROM
                                        HRIS_Approval_Request ar
                                        JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                        WHERE
                                        ar.No_Transaksi = tsi.No_Transaksi
                                        AND ar.Kode_Karyawan = k.Kode_Karyawan
                                        AND ar.Flag_Approval IS NULL
                                        ORDER BY
                                        af.order_flow
                                    ),
                                    'NO'
                                    )
                                END as status,
                                CASE
                                    WHEN tsi.Flag_Approval = 'Y' THEN 'DiSetujui'
                                    WHEN tsi.Flag_Approval = 'T' THEN (
                                    select
                                        top 1 k.Nama
                                    FROM
                                        HRIS_Approval_Request ar
                                        JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                    WHERE
                                        ar.No_Transaksi = tsi.No_Transaksi
                                        AND ar.Kode_Karyawan = k.Kode_Karyawan
                                        AND ar.Flag_Approval = 'T'
                                    ORDER BY
                                        af.order_flow
                                    )
                                    ELSE ISNULL(
                                    (
                                        SELECT
                                        TOP 1 ka.Nama
                                        FROM
                                        HRIS_Approval_Request ar
                                        JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                        JOIN
                                            Karyawan ka ON af.Kode_Karyawan = ka.Kode_Karyawan
                                        WHERE
                                        ar.No_Transaksi = tsi.No_Transaksi
                                        AND ar.Flag_Approval IS NULL
                                        ORDER BY
                                        af.order_flow
                                    ),
                                    null
                                    )
                                END AS Approver,
                                NULL AS Jam_Mulai,
                                (
                                    select
                                    STRING_AGG(
                                        CONCAT(
                                        b.Kode_Karyawan,
                                        ': ',
                                        CASE
                                            WHEN a.Flag_Approval IS NOT NULL THEN a.Flag_Approval
                                            ELSE 'N'
                                        END
                                        ),
                                        ', '
                                    ) WITHIN GROUP (
                                        ORDER BY
                                        b.order_flow
                                    ) AS StatusList
                                    from
                                    HRIS_Approval_Request a
                                    JOIN HRIS_Approval_Flow b ON a.Flow_Id = b.id
                                    where
                                    a.No_Transaksi = tsi.No_Transaksi
                                ) AS STATUSLIST
                                FROM
                                Karyawan AS k
                                INNER JOIN Transaksi_Sakit_Izin_Detail AS tsi ON k.Kode_Karyawan = tsi.Kode_Karyawan
                                INNER JOIN Transaksi_Sakit_Izin AS tsip ON tsi.No_Transaksi = tsip.No_Transaksi
                                WHERE
                                k.Kode_Karyawan = ?
                                and tsip.Status is null
                                UNION ALL -- Gunakan UNION ALL jika Anda tidak perlu menghilangkan duplikat
                                SELECT
                                CAST(CONCAT(CAST(ttpp.Tanggal AS DATE),' ', ttpp.Jam) AS DATETIME) AS tanggal_create,
                                k.Kode_Karyawan,
                                k.Nama,
                                ttp.No_Transaksi,
                                ttp.Jenis as Tipe_Izin,
                                ttp.Tanggal_Masuk_Pulang AS Tanggal_Mulai,
                                NULL AS Tanggal_Selesai,
                                ttp.Alasan,
                                ttp.filePath as Lampiran,
                                ISNULL(
                                    (
                                    SELECT
                                        TOP 1 af.order_flow
                                    FROM
                                        HRIS_Approval_Request ar
                                        JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                    WHERE
                                        ar.No_Transaksi = ttp.No_Transaksi
                                        AND ar.Kode_Karyawan = k.Kode_Karyawan
                                        AND ar.Flag_Approval IS NOT NULL
                                    ORDER BY
                                        af.order_flow DESC
                                    ),
                                    0
                                ) as currentFlow,
                                ISNULL(
                                    (
                                    select
                                        top 1 order_flow
                                    from
                                        HRIS_Approval_Flow a,
                                        HRIS_Approval_Request b
                                    where
                                        a.id = b.Flow_Id
                                        and b.No_Transaksi = ttp.No_Transaksi
                                    order by
                                        a.order_flow desc
                                    ),
                                    0
                                ) as MaxFlow,
                                CASE
                                    WHEN ttp.Flag_Approval = 'Y' THEN 'Selesai'
                                    WHEN ttp.Flag_Approval = 'T' THEN 'Ditolak'
                                    ELSE ISNULL(
                                    (
                                        SELECT
                                        TOP 1 'Diproses'
                                        FROM
                                        HRIS_Approval_Request ar
                                        JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                        WHERE
                                        ar.No_Transaksi = ttp.No_Transaksi
                                        AND ar.Kode_Karyawan = k.Kode_Karyawan
                                        AND ar.Flag_Approval IS NULL
                                        ORDER BY
                                        af.order_flow
                                    ),
                                    'NO'
                                    )
                                END as status,
                                CASE
                                    WHEN ttp.Flag_Approval = 'Y' THEN 'DiSetujui'
                                    WHEN ttp.Flag_Approval = 'T' THEN (
                                    select
                                        top 1 k.Nama
                                    FROM
                                        HRIS_Approval_Request ar
                                        JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                    WHERE
                                        ar.No_Transaksi = ttp.No_Transaksi
                                        AND ar.Kode_Karyawan = k.Kode_Karyawan
                                        AND ar.Flag_Approval = 'T'
                                    ORDER BY
                                        af.order_flow
                                    )
                                    ELSE ISNULL(
                                    (
                                        SELECT
                                        TOP 1 ka.Nama
                                        FROM
                                        HRIS_Approval_Request ar
                                        JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                        JOIN
                                            Karyawan ka ON af.Kode_Karyawan = ka.Kode_Karyawan
                                        WHERE
                                        ar.No_Transaksi = ttp.No_Transaksi
                                        AND ar.Flag_Approval IS NULL
                                        ORDER BY
                                        af.order_flow
                                    ),
                                    null
                                    )
                                END AS Approver,
                                ttp.Jam AS Jam_Mulai,
                                (
                                    select
                                    STRING_AGG(
                                        CONCAT(
                                        b.Kode_Karyawan,
                                        ': ',
                                        CASE
                                            WHEN a.Flag_Approval IS NOT NULL THEN a.Flag_Approval
                                            ELSE 'N'
                                        END
                                        ),
                                        ', '
                                    ) WITHIN GROUP (
                                        ORDER BY
                                        b.order_flow
                                    ) AS StatusList
                                    from
                                    HRIS_Approval_Request a
                                    JOIN HRIS_Approval_Flow b ON a.Flow_Id = b.id
                                    where
                                    a.No_Transaksi = ttp.No_Transaksi
                                ) AS STATUSLIST
                                FROM
                                Karyawan AS k
                                INNER JOIN Transaksi_Terlambat_Pulang_Cepat_Detail AS ttp ON k.Kode_Karyawan = ttp.Kode_Karyawan
                                INNER JOIN Transaksi_Terlambat_Pulang_Cepat AS ttpp ON ttp.No_Transaksi = ttpp.No_Transaksi
                                WHERE
                                k.Kode_Karyawan = ? and ttpp.Status is null
                            )
                            SELECT
                            *
                            FROM
                            cte
                            where
                            MaxFlow <> 0
                            ORDER BY
                            tanggal_create desc
            ";
            $result = DB::select($query, [$Kode_Karyawan, $Kode_Karyawan, $Kode_Karyawan]);
            $collection = collect($result);
            $slice_collect = $collection->slice(($currentPage-1) * $perPage, $perPage)->values();

            $final_page = new LengthAwarePaginator(
                $slice_collect,
                $collection->count(),
                $perPage,
                $currentPage,
                ['path' => request()->url()]
            );

            return response()->json($final_page);
        }catch(\Throwable $e){
            Log::channel('uDashLog')->error('Gagal Mengambil data izin getAllIzin'. $e->getMessage());
            return response()->json([
                'status' => 500,
                'error' => "Tidak bisa mengambil data izin "
            ], 500);
        }
    }

    public function getAllLembur()
    {
        try{


            $perPage = 5;
            $currentPage = request()->get('page', 1);
            $UserID_Absen = Auth::user()->karyawan->UserID_Absen;
            $Kode_Karyawan = Auth::user()->karyawan->Kode_Karyawan;

            $data = DB::table('Transaksi_Lembur as a')
                ->join("Transaksi_Lembur_Detail as b", 'a.No_Transaksi', '=', 'b.No_Transaksi')
                ->where("b.Kode_Karyawan", $Kode_Karyawan)
                ->whereNull("a.Status")
                ->orderBy("a.Tanggal")

                ->get();

            $tanggal_Lembur = $data->pluck('Tanggal_Lembur_Dari');
            // dd($tanggal_Lembur);
            // Handle case where no overtime data is found
            if ($tanggal_Lembur->isEmpty()) {
                // Log::channel('uDashLog')->error('Gagal Mengambil data lembur getAllLembur'. $e->getMessage());
                return response()->json([
                    'status' => 200,
                    'data' => []
                ]);
            }

            $startWeek = Carbon::parse($tanggal_Lembur->first())->format('Y-m-d');
            $endWeek = Carbon::parse($tanggal_Lembur->last())->format('Y-m-d');

            $query = "exec HRIS_SP_GET_ABSEN_NEW '001', '{$startWeek}', '{$endWeek}', '{$UserID_Absen}', ''";
            $absen_data = collect(DB::select($query));
            // dd($absen_data);
           $RawData = $data->map(function ($lembur) use ($absen_data) {
                $tanggalLembur = Carbon::parse($lembur->Tanggal_Lembur_Dari)->format('Y-m-d');

                $absenMatchCheckIn = collect($absen_data)
                    ->filter(function ($item) use ($tanggalLembur) {
                        return Carbon::parse($item->Tanggal)->format('Y-m-d') === $tanggalLembur
                            && !is_null($item->CheckIn);
                    })
                    ->isNotEmpty();

                $absenMatchCheckOut = collect($absen_data)
                    ->filter(function ($item) use ($tanggalLembur) {
                        return Carbon::parse($item->Tanggal)->format('Y-m-d') === $tanggalLembur
                            && !is_null($item->CheckOut);
                    })
                    ->isNotEmpty();

                $Status_Lembur = "Active";
                if ($absenMatchCheckIn && $absenMatchCheckOut) {
                    $Status_Lembur = "Selesai";
                }

                $jamAktual = collect($absen_data)
                    ->filter(function ($item) use ($tanggalLembur) {
                        return Carbon::parse($item->Tanggal)->format('Y-m-d') === $tanggalLembur;
                    })
                    ->first()?->CheckOut;

                $durasi = Carbon::parse($lembur->Tanggal_Lembur_Dari)->diffInMinutes(Carbon::parse($jamAktual)) / 60;

                return [
                    'Tanggal_Lembur_Dari' => $lembur->Tanggal_Lembur_Dari,
                    'Tanggal_Lembur_Sampai' => $lembur->Tanggal_Lembur_Sampai,
                    'JamAktual' => $jamAktual,
                    'durasi' => $durasi,
                    'Alasan' => $lembur->Alasan,
                    'Status' => $Status_Lembur
                ];
            });

            // dd($RawData);

            $collection = collect($RawData)->sortByDesc("Tanggal_Lembur_Dari")->values();
            $slice_collect = $collection->slice(($currentPage-1) * $perPage, $perPage)->values();

            $final_page = new LengthAwarePaginator(
                $slice_collect,
                $collection->count(),
                $perPage,
                $currentPage,
                ['path' => request()->url()]
            );

            return response()->json($final_page);
        }catch(\Throwable $e){
            Log::channel('uDashLog')->error('Gagal Mengambil data lembur getAllLembur'. $e->getMessage());
            return response()->json([
                'status' => 500,
                'error' => "Tidak bisa mengambil data lembur"
            ], 500);
        }
    }

    public function getChartData(Request $request){
        try{


            $title = 'uDash for You';
            $UserID_Absen = Auth::user()->karyawan->UserID_Absen;
            $Kode_Karyawan = Auth::user()->karyawan->Kode_Karyawan;
            if($request->jenis == "year"){
                $startWeek = Carbon::now()->startOfYear();
                $endWeek =   Carbon::now()->endOfYear();
            }else if($request->jenis == "week"){
                $startWeek = Carbon::now()->startOfWeek(Carbon::SUNDAY);
                $endWeek =   Carbon::now()->endOfWeek(Carbon::SATURDAY);
            }else{
                $startWeek = Carbon::now()->startOfMonth();
                $endWeek =  Carbon::now()->endOfMonth();
            }

            // Ambil data absensi utama dari stored procedure
            $query = "exec HRIS_SP_GET_ABSEN_NEW '001', '{$startWeek}', '{$endWeek}', '{$UserID_Absen}', ''";
            $absen_data = collect(DB::select($query));

            // Ambil semua tanggal unik dari data absen untuk dijadikan filter
            $tanggal_absens = $absen_data->pluck('Tanggal')->map(fn($date) => Carbon::parse($date)->format('Y-m-d'));

            // --- Pre-load Semua Data Transaksi dalam Beberapa Query ---
            $jumlah_checkin = $absen_data->whereNotNull('CheckIn')->count();

            // 1. Pre-load data lembur
            $lembur_data = DB::table('Transaksi_Lembur_Detail as b')
                ->select('a.No_Transaksi', 'b.Tanggal_Lembur_dari as mulai', 'b.Tanggal_Lembur_Sampai as selesai')
                ->join('Transaksi_Lembur as a', 'a.No_Transaksi', '=', 'b.No_Transaksi')
                ->where('b.Kode_Karyawan', $Kode_Karyawan)
                ->where(function($query) use ($tanggal_absens) {
                    $query->whereIn(DB::raw('CAST(b.Tanggal_Lembur_dari AS DATE)'), $tanggal_absens)
                        ->orWhereIn(DB::raw('CAST(b.Tanggal_Lembur_Sampai AS DATE)'), $tanggal_absens);
                })
                ->count();

            // 2. Pre-load data sakit/izin
            $sakit_data = DB::table('Transaksi_Sakit_Izin_Detail as b')
                ->select('a.No_Transaksi', 'b.Tanggal_Sakit_Izin_dari as mulai', 'b.Tanggal_Sakit_Izin_Sampai as selesai')
                ->join('Transaksi_Sakit_Izin as a', 'a.No_Transaksi', '=', 'b.No_Transaksi')
                ->where('b.Kode_Karyawan', $Kode_Karyawan)
                ->where("Jenis", "sakit")
                ->where('b.Flag_Approval', 'Y')
                ->where(function($query) use ($tanggal_absens) {
                    $query->whereIn(DB::raw('CAST(b.Tanggal_Sakit_Izin_dari AS DATE)'), $tanggal_absens)
                        ->orWhereIn(DB::raw('CAST(b.Tanggal_Sakit_Izin_Sampai AS DATE)'), $tanggal_absens);
                })
                ->count();

            $izin_data = DB::table('Transaksi_Sakit_Izin_Detail as b')
                ->select('a.No_Transaksi', 'b.Tanggal_Sakit_Izin_dari as mulai', 'b.Tanggal_Sakit_Izin_Sampai as selesai')
                ->join('Transaksi_Sakit_Izin as a', 'a.No_Transaksi', '=', 'b.No_Transaksi')
                ->where('b.Kode_Karyawan', $Kode_Karyawan)
                ->where("Jenis", "izinFull")
                ->where('b.Flag_Approval', 'Y')
                ->where(function($query) use ($tanggal_absens) {
                    $query->whereIn(DB::raw('CAST(b.Tanggal_Sakit_Izin_dari AS DATE)'), $tanggal_absens)
                        ->orWhereIn(DB::raw('CAST(b.Tanggal_Sakit_Izin_Sampai AS DATE)'), $tanggal_absens);
                })
                ->count();

            // 3. Pre-load data cuti
            $cuti_data = DB::table('Transaksi_Cuti_Detail as b')
                ->select('a.No_Transaksi', 'b.Tanggal_Cuti_dari as mulai', 'b.Tanggal_Cuti_Sampai as selesai')
                ->join('Transaksi_Cuti as a', 'a.No_Transaksi', '=', 'b.No_Transaksi')
                ->where('b.Kode_Karyawan', $Kode_Karyawan)
                ->where('b.Flag_Approval', 'Y')
                ->where(function($query) use ($tanggal_absens) {
                    $query->whereIn(DB::raw('CAST(b.Tanggal_Cuti_dari AS DATE)'), $tanggal_absens)
                        ->orWhereIn(DB::raw('CAST(b.Tanggal_Cuti_Sampai AS DATE)'), $tanggal_absens);
                })
                ->count();

                // 4. Pre-load data terlambat/pulang cepat
                $pulangCepat_data = DB::table('Transaksi_Terlambat_Pulang_Cepat_Detail as b')
                    ->select('a.No_Transaksi', 'b.Tanggal_Masuk_Pulang as Tanggal')
                    ->join('Transaksi_Terlambat_Pulang_Cepat as a', 'a.No_Transaksi', '=', 'b.No_Transaksi')
                    ->where('b.Kode_Karyawan', $Kode_Karyawan)
                    ->where("b.Jenis", "pulangCepat")
                    ->where('b.Flag_Approval', 'Y')
                    ->whereIn(DB::raw('CAST(b.Tanggal_Masuk_Pulang AS DATE)'), $tanggal_absens)
                    ->count();
                $Terlambat_data = DB::table('Transaksi_Terlambat_Pulang_Cepat_Detail as b')
                    ->select('a.No_Transaksi', 'b.Tanggal_Masuk_Pulang as Tanggal')
                    ->join('Transaksi_Terlambat_Pulang_Cepat as a', 'a.No_Transaksi', '=', 'b.No_Transaksi')
                    ->where('b.Kode_Karyawan', $Kode_Karyawan)
                    ->where("b.Jenis", "terlambat")
                    ->where('b.Flag_Approval', 'Y')
                    ->whereIn(DB::raw('CAST(b.Tanggal_Masuk_Pulang AS DATE)'), $tanggal_absens)
                    ->count();




                // dd([$jumlah_checkin, $Terlambat_data, $pulangCepat_data, $izin_data]);

            return response()->json([
                'status' => 200,
                'data' =>  [$jumlah_checkin, $Terlambat_data, $pulangCepat_data, $izin_data]?? [0,0,0,0]
            ]);
        }catch(\Throwable $e){
            Log::channel('uDashLog')->error('Gagal Mengambil data chat getChatData'. $e->getMessage());
            return response()->json([
                'status' => 500,
                'error' => "Tidak bisa mengambil data chat"
            ], 500);
        }

    }
}
