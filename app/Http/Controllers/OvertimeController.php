<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models\User;
use App\Models\Karyawan;
use App\Models\Lembur;
use App\Models\LemburDet;
use App\Models\LemburDetail;
use App\Helpers\Whatsapp;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;




class OvertimeController extends Controller
{
    public function index(){

        try{
            $title = "INI LEMBUR";
            $divisi =  Auth()->user()->divisionKaryawan->ID_Divisi;
            $karyawanAuth = Auth()->user()->karyawan->Kode_Karyawan;
            // $karyawanID = Auth()->user()->Id_Users;
            $karyawanID = Auth()->user()->karyawan->Kode_Karyawan;

            // Ini jika jam ny cutouff nya hanya 1 tanpa melihat hari
                // $cutoffHour = 4;

                // $now = now();

                // if ((int)$now->format('H') < $cutoffHour) {
                //     $startDate = $now->subDay()->format('Y-m-d');
                // } else {
                //     $startDate = $now->format('Y-m-d');
                // }

                // $week = [];
                // $date = $startDate;
                // for ($i = 0; $i <= 2; $i++) {
                //     $week[] = $date;
                //     $date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
                // }
            // ini jika jam cutoff ny lbih dari satu dan melihat hari
                $now = now();
                // $now = now()->setTime(2, 0); // untuk testing

                // $now = Carbon::createFromFormat('Y-m-d H:i', '2025-08-16 05:00');


                $dayName = $now->format('l'); // Monday, Tuesday, ..., Saturday, Sunday

                // Tentukan cutoff jam berdasarkan hari
                if (in_array($dayName, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])) {
                    $cutoffHour = 3; // jam 3 pagi
                } elseif ($dayName === 'Saturday') {
                    $cutoffHour = 5; // jam 5 pagi
                } else {
                    // Kalau Minggu, misalnya ikut jam 3 juga
                    $cutoffHour = 3;
                }

                // Logika tentukan start date
                if ((int)$now->format('H') < $cutoffHour) {
                    $startDate = $now->subDay()->format('Y-m-d');
                } else {
                    $startDate = $now->format('Y-m-d');
                }

                // Generate list tanggal
                $week = [];
                $date = $startDate;
                for ($i = 0; $i <= 2; $i++) {
                    $week[] = $date;
                    $date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
                }

                // dd($dayName, $cutoffHour, $now->format('Y-m-d H:i:s'), $week);



            // $currentDate = '2025-07-21';
            // $today = date('Y-m-d');
            // $week = [];

            // // Perulangan akan terus berjalan selama $currentDate kurang dari atau sama dengan $today
            // while (strtotime($currentDate) <= strtotime($today)) {
            //     $week[] = $currentDate;
            //     $currentDate = date('Y-m-d', strtotime("+1 day", strtotime($currentDate)));
            // }


            $queryShift = "
            select
            a.Nama,
            a.ID_Shift,
            (
                CONVERT(VARCHAR(5), c.Jam_Masuk, 108) + ' - ' + CONVERT(VARCHAR(5), c.Jam_Keluar, 108)
            ) as time,
            c.Kode_Waktu_Kerja as deksripsi
            from
            HRIS_Shift_Kerja a,
            HRIS_Shift_Kerja_Detail b,
            HRIS_Waktu_Kerja c
            where
            a.ID_Shift = b.ID_Shift
            and b.ID_Waktu_Kerja = c.ID_Waktu_Kerja
            and b.Hari = 3
            ";

            $shiftList = DB::select($queryShift);
            // dd($shiftList);

            $query = "SELECT
                        ISNULL(
                            (
                                SELECT TOP (1)
                                    CASE
                                        -- Jika CHECKTIME tidak NULL, status 'COMPLETED' dan tampilkan waktu checkout
                                        WHEN z.CHECKTIME IS NOT NULL THEN
                                            CONVERT(VARCHAR, z.CHECKTIME, 120)

                                        -- Jika CHECKTIME NULL dan Tanggal_Lembur_Sampai sudah lewat (EXPIRED)
                                        WHEN z.CHECKTIME IS NULL AND CAST(c.Tanggal_Lembur_Sampai AS DATETIME) < CAST(GETDATE() AS DATETIME) THEN
                                            'EXPIRED'

                                        -- Jika CHECKTIME NULL dan Tanggal_Lembur_Sampai masih berlaku (IN PROGRESS)
                                        WHEN z.CHECKTIME IS NULL AND CAST(c.Tanggal_Lembur_Sampai AS DATETIME) >= CAST(GETDATE() AS DATETIME) THEN
                                            'IN PROGRESS'

                                        ELSE
                                            'UNKNOWN'
                                    END AS CHECKOUT
                                FROM
                                    HRIS_Shift_Kerja v
                                    INNER JOIN HRIS_Shift_Kerja_Detail w ON v.ID_Shift = w.ID_Shift
                                    INNER JOIN HRIS_Waktu_Kerja x ON w.ID_Waktu_Kerja = x.ID_Waktu_Kerja
                                    INNER JOIN HRIS_Shift_Per_Karyawan y ON v.ID_Shift = y.ID_Shift
                                    INNER JOIN CHECKINOUT z ON z.USERID = d.UserID_Absen
                                WHERE
                                    y.Kode_Perusahaan = d.Kode_Perusahaan
                                    AND y.Kode_Karyawan = d.Kode_Karyawan
                                    AND z.CHECKTIME BETWEEN
                                        CONVERT(DATETIME, CONVERT(VARCHAR(10), c.Tanggal_Lembur_Dari, 120) + ' ' + x.Jam_Mulai_Absen_Keluar)
                                        AND CONVERT(DATETIME, CONVERT(VARCHAR(10), c.Tanggal_Lembur_Sampai, 120) + ' ' + x.Jam_Selesai_Absen_Keluar)
                            ),
                            CASE
                            WHEN CAST(c.Tanggal_Lembur_Sampai AS DATETIME) < CAST(GETDATE() AS DATETIME) THEN  'EXPIRED'
                            else 'IN PROGRESS'
                            END

                        ) AS CHECKOUT,
                        (a.Tanggal + a.Jam) AS T_Pengaju,
                        a.UserID AS Pengaju,
                        a.No_Transaksi,
                        d.Nama AS name,
                        d.UserID_Absen As userId,
                        c.Tanggal_Lembur_Dari AS start_time,
                        c.Tanggal_Lembur_Sampai AS end_time,
                        c.Alasan AS reason
                    FROM
                        Transaksi_Lembur a
                        INNER JOIN Transaksi_Lembur_Det b ON a.No_Transaksi = b.No_Transaksi
                        INNER JOIN Transaksi_Lembur_Detail c ON a.No_Transaksi = c.No_Transaksi
                        INNER JOIN Karyawan d ON b.Kode_Karyawan = d.Kode_Karyawan
                        where a.status is NULL
                    GROUP BY
                        a.UserID,
                        d.Nama,
                        a.Tanggal,
                        a.Jam,
                        c.Tanggal_Lembur_Dari,
                        c.Tanggal_Lembur_Sampai,
                        c.Alasan,
                        d.Kode_Karyawan,
                        d.UserID,
                        d.Kode_Perusahaan,
                        a.No_Transaksi,
                        d.UserID_Absen,
                        d.UserID_Absen
                    ORDER BY c.Tanggal_Lembur_Dari
                        ";

            $resultPerUser = DB::select($query);
            $queryNoTransaksi=  "
                select
                a.No_Transaksi,
                a.Kode_Perusahaan,
                a.Tanggal,
                a.Jam,
                b.Nama
                from
                Transaksi_Lembur a,
                Karyawan b,
                View_Divisi_Sub_Divisi c,
                KPI_Users d
                where
                a.UserID = b.Kode_Karyawan
                and b.UserID_Web = d.Id_Users
                and b.ID_Divisi_Sub_Divisi = c.ID_DIVISI_SUB_DIVISI
                and a.UserID = ?
                and a.status is null
                order by a.Tanggal desc, a.jam desc";
            $resultNo_Result = DB::select($queryNoTransaksi, [$karyawanID]);
            $queryUser = "SELECT a.Nama as name, a.UserID_Absen as userId FROM Karyawan a, HRIS_Shift_Per_Karyawan b
                    WHERE a.Kode_Karyawan = b.Kode_Karyawan AND a.UserID_Absen IS NOT NULL group by a.Nama, a.UserID_Absen ";
            $users = DB::select($queryUser);
            return inertia('Overtime', ['title'=>$title, 'data' => $resultNo_Result, 'dataUser' => $resultPerUser, 'tanggal' => $week, 'userChoose' =>$users, 'shiftList' => $shiftList]);

        }catch(\Throwable $e){
            Log::channel('lemburLog')->error('Gagal Mengambil data'. $e->getMessage());

            Alert::error('error', 'Terjadi Error saat menampilkan page lembur!');
            return back();
        }
        // dd($resultNo_Result);


        //    $test = "
        //         select Nama, UserID_Web from karyawan where nama like '%ridho%'
        //     ";

        //     $rsTest = DB::select($test);
        //     $collection = collect($rsTest);

        //     $hashedData = $collection->map(function ($item) {
        //         if (isset($item->UserID_Web)) {
        //             $item->UserID_Web = Hashids::connection('custom')->encode($item->UserID_Web);
        //         }
        //         if (isset($item->Nama)) {
        //             $item->Nama = Crypt::encryptString($item->Nama);
        //         }
        //         return $item;
        //     });


        //     $unhashedData = $hashedData->map(function ($item){
        //        if (isset($item->UserID_Web)) {
        //             $item->UserID_Web = Hashids::connection('custom')->decode($item->UserID_Web);
        //         }
        //         if (isset($item->Nama)) {
        //             $item->Nama = Crypt::decryptString($item->Nama);
        //         }
        //         return $item;
        //     });

        //     dd($hashedData);



    }

    public function indexAdmin(){
        try{
            $title = "INI LEMBUR";
            $divisi =  Auth()->user()->divisionKaryawan->ID_Divisi;
            $karyawanAuth = Auth()->user()->karyawan->Kode_Karyawan;
            // $karyawanID = Auth()->user()->Id_Users;
            $karyawanID = Auth()->user()->karyawan->Kode_Karyawan;
            $date = date('Y-m-d');

            $week = [];
            for($i = 0; $i <=2; $i++){
                $week[] = $date;

                $date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
            };
            $queryShift = "
            select
            a.Nama,
            a.ID_Shift,
            (
                CONVERT(VARCHAR(5), c.Jam_Masuk, 108) + ' - ' + CONVERT(VARCHAR(5), c.Jam_Keluar, 108)
            ) as time,
            c.Kode_Waktu_Kerja as deksripsi
            from
            HRIS_Shift_Kerja a,
            HRIS_Shift_Kerja_Detail b,
            HRIS_Waktu_Kerja c
            where
            a.ID_Shift = b.ID_Shift
            and b.ID_Waktu_Kerja = c.ID_Waktu_Kerja
            and b.Hari = 3
            ";

            $shiftList = DB::select($queryShift);
            // dd($shiftList);

            $query = "SELECT
                        ISNULL(
                            (
                                SELECT TOP (1)
                                    CASE
                                        -- Jika CHECKTIME tidak NULL, status 'COMPLETED' dan tampilkan waktu checkout
                                        WHEN z.CHECKTIME IS NOT NULL THEN
                                            CONVERT(VARCHAR, z.CHECKTIME, 120)

                                        -- Jika CHECKTIME NULL dan Tanggal_Lembur_Sampai sudah lewat (EXPIRED)
                                        WHEN z.CHECKTIME IS NULL AND CAST(c.Tanggal_Lembur_Sampai AS DATETIME) < CAST(GETDATE() AS DATETIME) THEN
                                            'EXPIRED'

                                        -- Jika CHECKTIME NULL dan Tanggal_Lembur_Sampai masih berlaku (IN PROGRESS)
                                        WHEN z.CHECKTIME IS NULL AND CAST(c.Tanggal_Lembur_Sampai AS DATETIME) >= CAST(GETDATE() AS DATETIME) THEN
                                            'IN PROGRESS'

                                        ELSE
                                            'UNKNOWN'
                                    END AS CHECKOUT
                                FROM
                                    HRIS_Shift_Kerja v
                                    INNER JOIN HRIS_Shift_Kerja_Detail w ON v.ID_Shift = w.ID_Shift
                                    INNER JOIN HRIS_Waktu_Kerja x ON w.ID_Waktu_Kerja = x.ID_Waktu_Kerja
                                    INNER JOIN HRIS_Shift_Per_Karyawan y ON v.ID_Shift = y.ID_Shift
                                    INNER JOIN CHECKINOUT z ON z.USERID = d.UserID_Absen
                                WHERE
                                    y.Kode_Perusahaan = d.Kode_Perusahaan
                                    AND y.Kode_Karyawan = d.Kode_Karyawan
                                    AND z.CHECKTIME BETWEEN
                                        CONVERT(DATETIME, CONVERT(VARCHAR(10), c.Tanggal_Lembur_Dari, 120) + ' ' + x.Jam_Mulai_Absen_Keluar)
                                        AND CONVERT(DATETIME, CONVERT(VARCHAR(10), c.Tanggal_Lembur_Sampai, 120) + ' ' + x.Jam_Selesai_Absen_Keluar)
                            ),
                            CASE
                            WHEN CAST(c.Tanggal_Lembur_Sampai AS DATETIME) < CAST(GETDATE() AS DATETIME) THEN  'EXPIRED'
                            else 'IN PROGRESS'
                            END

                        ) AS CHECKOUT,
                        (a.Tanggal + a.Jam) AS T_Pengaju,
                        a.UserID AS Pengaju,
                        a.No_Transaksi,
                        d.Nama AS name,
                        d.UserID_Absen As userId,
                        c.Tanggal_Lembur_Dari AS start_time,
                        c.Tanggal_Lembur_Sampai AS end_time,
                        c.Alasan AS reason
                    FROM
                        Transaksi_Lembur a
                        INNER JOIN Transaksi_Lembur_Det b ON a.No_Transaksi = b.No_Transaksi
                        INNER JOIN Transaksi_Lembur_Detail c ON a.No_Transaksi = c.No_Transaksi
                        INNER JOIN Karyawan d ON b.Kode_Karyawan = d.Kode_Karyawan
                        where a.status is NULL
                    GROUP BY
                        a.UserID,
                        d.Nama,
                        a.Tanggal,
                        a.Jam,
                        c.Tanggal_Lembur_Dari,
                        c.Tanggal_Lembur_Sampai,
                        c.Alasan,
                        d.Kode_Karyawan,
                        d.UserID,
                        d.Kode_Perusahaan,
                        a.No_Transaksi,
                        d.UserID_Absen,
                        d.UserID_Absen
                    ORDER BY c.Tanggal_Lembur_Dari
                        ";

            $resultPerUser = DB::select($query);
            $queryNoTransaksi=  "
                select
                a.No_Transaksi,
                a.Kode_Perusahaan,
                a.Tanggal,
                a.Jam,
                b.Nama
                from
                Transaksi_Lembur a,
                Karyawan b,
                View_Divisi_Sub_Divisi c,
                KPI_Users d
                where
                a.UserID = b.Kode_Karyawan
                and b.UserID_Web = d.Id_Users
                and b.ID_Divisi_Sub_Divisi = c.ID_DIVISI_SUB_DIVISI
                and a.status is null
                order by a.Tanggal desc, a.jam desc";
            $resultNo_Result = DB::select($queryNoTransaksi);
            $queryUser = "SELECT a.Nama as name, a.UserID_Absen as userId FROM Karyawan a, HRIS_Shift_Per_Karyawan b
                    WHERE a.Kode_Karyawan = b.Kode_Karyawan AND a.UserID_Absen IS NOT NULL group by a.Nama, a.UserID_Absen ";
            $users = DB::select($queryUser);

            return inertia('OvertimeAdmin', ['title'=>$title, 'data' => $resultNo_Result, 'dataUser' => $resultPerUser, 'tanggal' => $week, 'userChoose' =>$users, 'shiftList' => $shiftList]);

        }catch(\Throwable $e){
            Log::channel('lemburLog')->error('Gagal Mengambil data'. $e->getMessage());

            Alert::error('error', 'Terjadi Error saat menampilkan page lembur!');
            return back();
        }


        // dd($resultNo_Result);


        //    $test = "
        //         select Nama, UserID_Web from karyawan where nama like '%ridho%'
        //     ";

        //     $rsTest = DB::select($test);
        //     $collection = collect($rsTest);

        //     $hashedData = $collection->map(function ($item) {
        //         if (isset($item->UserID_Web)) {
        //             $item->UserID_Web = Hashids::connection('custom')->encode($item->UserID_Web);
        //         }
        //         if (isset($item->Nama)) {
        //             $item->Nama = Crypt::encryptString($item->Nama);
        //         }
        //         return $item;
        //     });


        //     $unhashedData = $hashedData->map(function ($item){
        //        if (isset($item->UserID_Web)) {
        //             $item->UserID_Web = Hashids::connection('custom')->decode($item->UserID_Web);
        //         }
        //         if (isset($item->Nama)) {
        //             $item->Nama = Crypt::decryptString($item->Nama);
        //         }
        //         return $item;
        //     });

        //     dd($hashedData);



    }


    public function getWaktuShift(Request $request){
      try{

            $ID_shift = $request->shift;
            $Hari = date('N',strtotime($request->date));


            // if ($Hari == 6) {
            //     $Hari = 7;
            // } elseif ($Hari == 7) {
            //     $Hari = 1;
            // }
            if ($Hari <= 6 ) {
                $Hari += 1 ;
                } elseif ($Hari == 7) {
                    $Hari = 1;
                }


            // dd($Hari);

            $query = "
                select
                a.Nama,
                c.Kode_Waktu_Kerja,
                b.Hari,
                c.Jam_Keluar,
                c.Flag_CO_Lintas_Hari,
                COALESCE(NULLIF(c.Jam_Selesai_Lintas_Hari, ''), NULLIF(c.Jam_Selesai_Absen_Keluar, '')) AS Jam_selesai_absen
                from
                HRIS_Shift_Kerja a,
                HRIS_Shift_Kerja_Detail b,
                HRIS_Waktu_Kerja c
                where
                a.ID_Shift = b.ID_Shift
                and b.ID_Waktu_Kerja = c.ID_Waktu_Kerja
                and b.Hari = :Hari
                and a.ID_Shift = :Shift

            ";

            $result = DB::select($query, ['Hari' => $Hari, 'Shift' => $ID_shift]);

            // dd($result);

            if (empty($result)) {
                return response()->json([
                    'status' => 200,
                    'data' => [],
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'data' => $result,
            ]);
        }catch (\Throwable $e) {
            Log::channel('lemburLog')->error('Gagal megambil data waktu shift: ' . $e->getMessage());
            return response()->json([
                'status' => 404,
                'error' => 'Terjadi error saat mengambil data shift',
            ], 404);
        }
    }

    public function getShift(Request $request){
        try{
            $tanggal =$request['tanggal'];
            // dd(date('Y-m-d',strtotime($tanggal)) <= date('Y-m-d'));
            if(date('Y-m-d',strtotime($tanggal)) == date('Y-m-d')){
            $Hari = date('N', strtotime($tanggal));
             if ($Hari <= 6 ) {
            $Hari += 1 ;
            $time = date('H:i:s');
              $query = "
            select
            a.ID_Shift,
            a.Nama as Shift,
            b.Hari,
            c.Jam_Masuk,
            c.Jam_Keluar,
            COALESCE(
                NULLIF(c.Jam_Selesai_Lintas_Hari, ''),
                NULLIF(c.Jam_Selesai_Absen_Keluar, '')
            ) AS Jam_selesai_absen
            from
            HRIS_Shift_Kerja a,
            HRIS_Shift_Kerja_Detail b,
            HRIS_Waktu_Kerja c
            where
            a.ID_Shift = b.ID_Shift
            and b.ID_Waktu_Kerja = c.ID_Waktu_Kerja
            and b.Hari = ?
            AND c.Jam_Keluar >= ?
            ";
            $result =  DB::select($query, [$Hari, $time]);


            } elseif ($Hari == 7) {
                $Hari = 1;
            }

            }else{


            $Hari = date('N', strtotime($tanggal));
             if ($Hari <= 6 ) {
            $Hari += 1 ;
            } elseif ($Hari == 7) {
                $Hari = 1;
            }

            // $TanggalSekarang = date();
            // dd($Hari);
            $query = "
            select
            a.ID_Shift,
            a.Nama as Shift,
            b.Hari,
            c.Jam_Masuk,
            c.Jam_Keluar,
            COALESCE(
                NULLIF(c.Jam_Selesai_Lintas_Hari, ''),
                NULLIF(c.Jam_Selesai_Absen_Keluar, '')
            ) AS Jam_selesai_absen
            from
            HRIS_Shift_Kerja a,
            HRIS_Shift_Kerja_Detail b,
            HRIS_Waktu_Kerja c
            where
            a.ID_Shift = b.ID_Shift
            and b.ID_Waktu_Kerja = c.ID_Waktu_Kerja
            and b.Hari = ?
            ";
            $result =  DB::select($query, [$Hari]);
            }


            // dd($result);
            return response()->json([
                    'status' => 200,
                    'data' => $result
            ]);
        }catch (\Throwable $e) {
            Log::channel('lemburLog')->error('Gagal megambil data shift: ' . $e->getMessage());
            return response()->json([
                'status' => 404,
                'error' => 'Terjadi error saat mengambil data shift',
            ], 404);
        }
    }

    public function userActive(Request $request){
        // param
        try{

            $Kode_Perusahaan = '001';
            // $ID_shift = $request->input('shift');
            $Hari = date('N',strtotime($request->input('date')));
            $time = date('H:i:s',strtotime($request->input('time')));
            $Shift = $request->shift;
            // dd($time);
            if ($Hari <= 6 ) {
                $Hari += 1 ;
            } elseif ($Hari == 7) {
                $Hari = 1;
            }
            $Tanggal = $request->input('date');
            $divisi =  Auth()->user()->divisionKaryawan->ID_Divisi;
            // dd($divisi);
            $karyawan =Auth()->user()->karyawan->Kode_Karyawan;
            // dd($karyawan);


            $query = "
                WITH KaryawanShiftPermanen AS (
                    SELECT
                        e.Kode_Karyawan,
                        f.nama_divisi,
                        f.nama_sub_divisi,
                        e.UserID_Absen,
                        c.Jam_Masuk,
                        c.Jam_Keluar,
                        e.Nama,
                        a.ID_Shift,
                        a.Nama as Nama_Shift,
                        d.Periode,
                        COALESCE(NULLIF(c.Jam_Selesai_Lintas_Hari, ''), NULLIF(c.Jam_Selesai_Absen_Keluar, '')) AS Jam_selesai_absen,
                        ROW_NUMBER() OVER (
                            PARTITION BY e.Kode_Karyawan
                            ORDER BY d.Periode DESC, d.Urut DESC
                        ) as rn_periode
                    FROM HRIS_Shift_Kerja a
                    INNER JOIN HRIS_Shift_Kerja_Detail b ON a.ID_Shift = b.ID_Shift
                    INNER JOIN HRIS_Waktu_Kerja c ON b.ID_Waktu_Kerja = c.ID_Waktu_Kerja
                    INNER JOIN HRIS_Shift_Per_Karyawan d ON a.ID_Shift = d.ID_Shift
                    INNER JOIN Karyawan e ON d.Kode_Karyawan = e.Kode_Karyawan
                                        AND d.Kode_Perusahaan = e.Kode_Perusahaan
                    INNER JOIN HRIS_Karyawan_Team h ON h.Kode_Karyawan = e.Kode_Karyawan
                    INNER JOIN View_Divisi_Sub_Divisi f ON e.ID_Divisi_Sub_Divisi = f.ID_DIVISI_SUB_DIVISI
                    INNER JOIN View_Golongan_Sub_Golongan_Level_Jabatan g ON e.ID_Level_Jabatan = g.ID_Level_Jabatan
                    WHERE e.Kode_Perusahaan = ?
                    AND g.ID_Level  IN (1,2,3)
                    AND b.Hari = ?
                    AND h.Kode_Karyawan_Team = ?
                    AND d.Periode <= ?

                ),
                ShiftPermanen AS (
                    SELECT
                        Kode_Karyawan,
                        nama_divisi,
                        nama_sub_divisi,
                        UserID_Absen,
                        Nama,
                        Jam_Masuk,
                        Jam_Keluar,
                        ID_Shift,
                        Nama_Shift,
                        'Permanen' as Jenis_Shift,
                        Periode,
                        Jam_selesai_absen
                    FROM KaryawanShiftPermanen
                    WHERE rn_periode = 1
                ),
               ShiftSementaraRaw AS (
                SELECT
                    e.Kode_Karyawan,
                    f.nama_divisi,
                    f.nama_sub_divisi,
                    e.UserID_Absen,
                    e.Nama,
                    c.Jam_Masuk,
                    c.Jam_Keluar,
                    a.ID_Shift,
                    a.Nama as Nama_Shift,
                    'Sementara' as Jenis_Shift,
                    d.Tanggal as Periode,
                    COALESCE(NULLIF(c.Jam_Selesai_Lintas_Hari, ''), NULLIF(c.Jam_Selesai_Absen_Keluar, '')) AS Jam_selesai_absen,
                    ROW_NUMBER() OVER (
                        PARTITION BY e.Kode_Karyawan
                        ORDER BY d.Urut DESC, d.Tanggal DESC
                    ) AS rn
                FROM HRIS_Shift_Kerja a
                INNER JOIN HRIS_Shift_Kerja_Detail b
                    ON a.ID_Shift = b.ID_Shift
                INNER JOIN HRIS_Waktu_Kerja c
                    ON b.ID_Waktu_Kerja = c.ID_Waktu_Kerja
                INNER JOIN HRIS_Shift_Sementara d
                    ON a.ID_Shift = d.ID_Shift
                INNER JOIN Karyawan e
                    ON d.Kode_Perusahaan = e.Kode_Perusahaan
                AND d.Kode_Karyawan = e.Kode_Karyawan
                INNER JOIN HRIS_Karyawan_Team h
                    ON h.Kode_Karyawan = e.Kode_Karyawan
                INNER JOIN View_Divisi_Sub_Divisi f
                    ON e.ID_Divisi_Sub_Divisi = f.ID_DIVISI_SUB_DIVISI
                INNER JOIN View_Golongan_Sub_Golongan_Level_Jabatan g
                    ON e.ID_Level_Jabatan = g.ID_Level_Jabatan
                WHERE e.Kode_Perusahaan = ?
                AND g.ID_Level IN (1,2,3)
                AND b.Hari = ?
                AND h.Kode_Karyawan_Team = ?
                AND d.Tanggal = ?
            ),
            ShiftSementara AS (
                SELECT *
                FROM ShiftSementaraRaw
                WHERE rn = 1
            )

                SELECT
                    CASE
                        -- Kondisi untuk Shift Lintas Hari (Jam Keluar > Jam Selesai Absen)
                        WHEN DATEADD(HOUR, 1, CONVERT(TIME, COALESCE(ss.Jam_Keluar, sp.Jam_Keluar))) > CONVERT(TIME, COALESCE(ss.Jam_selesai_absen, sp.Jam_selesai_absen))
                        THEN
                            CASE
                                -- Jika '16:20:00' lebih besar dari jam mulai (misal > 15:30) ATAU lebih kecil dari jam selesai (misal < 03:00)
                                WHEN ? >= DATEADD(HOUR, 1, CONVERT(TIME, COALESCE(ss.Jam_Keluar, sp.Jam_Keluar))) OR ? <= CONVERT(TIME, COALESCE(ss.Jam_selesai_absen, sp.Jam_selesai_absen))
                                THEN 'TRUE'
                                ELSE 'FALSE'
                            END
                        -- Kondisi untuk Shift Normal (Jam Keluar <= Jam Selesai Absen)
                        ELSE
                            CASE
                                -- Cukup gunakan BETWEEN biasa
                                WHEN ? BETWEEN DATEADD(HOUR, 1, CONVERT(TIME, COALESCE(ss.Jam_Keluar, sp.Jam_Keluar))) AND CONVERT(TIME, COALESCE(ss.Jam_selesai_absen, sp.Jam_selesai_absen))
                                THEN 'TRUE'
                                ELSE 'FALSE'
                            END
                    END AS JamComparison,
                    CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM Transaksi_Lembur_Detail a, Transaksi_Lembur b
                        WHERE
                            a.No_Transaksi = b.No_Transaksi
                            AND Kode_Karyawan = COALESCE(ss.Kode_Karyawan, sp.Kode_Karyawan)
                            AND ? = CAST(Tanggal_Lembur_Dari AS DATE)
                            AND b.Status is null
                    )
                    THEN 'TRUE'
                    ELSE 'FALSE'
                    END as lemburPernah,
                    COALESCE(ss.Kode_Karyawan, sp.Kode_Karyawan) as userId,
                    COALESCE(ss.UserID_Absen, sp.UserID_Absen) as UserID_Absen,
                    COALESCE(ss.nama_divisi, sp.nama_divisi) as Divisi,
                    COALESCE(ss.nama_sub_divisi, sp.nama_sub_divisi) as Sub_Divisi,
                    COALESCE(ss.Nama, sp.Nama) as name,
                    COALESCE(ss.Jam_Masuk, sp.Jam_Masuk) as Jam_Masuk,
                    COALESCE(ss.Jam_Keluar, sp.Jam_Keluar) as Jam_Keluar,
                    COALESCE(ss.ID_Shift, sp.ID_Shift) as ID_Shift,
                    COALESCE(ss.Nama_Shift, sp.Nama_Shift) as Nama_Shift,
                    COALESCE(ss.Jam_selesai_absen, sp.Jam_selesai_absen) as Jam_selesai_absen,
                    COALESCE(ss.Jenis_Shift, sp.Jenis_Shift) as Jenis_Shift
                FROM ShiftPermanen sp
                FULL OUTER JOIN ShiftSementara ss ON sp.Kode_Karyawan = ss.Kode_Karyawan
                where COALESCE(ss.Kode_Karyawan, sp.Kode_Karyawan) <> ?
                ORDER BY JamComparison desc
                ";

                // Parameter array harus berurutan sesuai dengan urutan ? dalam query
                $result = DB::select($query, [
                    $Kode_Perusahaan,  // Parameter 1: KaryawanShiftPermanen.Kode_Perusahaan
                    $Hari,             // Parameter 2: KaryawanShiftPermanen.Hari
                    $karyawan,
                    $Tanggal,

                    $Kode_Perusahaan,  // Parameter 5: ShiftSementara.Kode_Perusahaan
                    $Hari,             // Parameter 6: ShiftSementara.Hari
                    $karyawan,
                    $Tanggal,
                    $time,
                    $time,
                    $time,
                    $Tanggal,
                    $karyawan,           // Parameter 8: ShiftSementara.Tanggal

                ]);

            // dd($result);
            if (empty($result)) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Data tidak ditemukan',
                ], 404);
            }

            $resultCollect = collect($result);
            $hashResult = $resultCollect->map(function ($item) {
                    if (isset($item->userId)) {
                        $item->userId = Crypt::encryptString($item->userId);
                    }
                    return $item;
            });

            // dd(Crypt::decryptString('eyJpdiI6ImdzVUFLb0RnK0NNazQ2V0diWnpGL3c9PSIsInZhbHVlIjoiQWpsb2JYNlFyTFg3cFhNekNQUURpZz09IiwibWFjIjoiZTVhZjk4YzFmZTMyNThmZjFjODZhYzZjNzFlMDYyYmVhODNkNTE0NDM0MDliZDkyYWJmMDg2ZTVmNzM2NjU3OCIsInRhZyI6IiJ9'));

            // $query = "
            //         WITH
            //         KaryawanShiftPermanen AS (
            //             SELECT
            //             a.Kode_Karyawan,
            //             a.Nama AS Nama,
            //             f.nama_divisi,
            //             a.UserID_Absen,
            //             f.nama_sub_divisi,
            //             d.Jam_Keluar,
            //             CASE
            //                 WHEN c.Hari = ? THEN b.Nama
            //                 ELSE NULL
            //             END AS Nama_Shift,
            //             CASE
            //                 WHEN c.Hari = ? THEN b.ID_Shift
            //                 ELSE NULL
            //             END AS ID_Shift,
            //             c.Hari,
            //             e.Periode,
            //             ROW_NUMBER() OVER (
            //                 PARTITION BY
            //                 a.Kode_Karyawan
            //                 ORDER BY
            //                 e.Periode DESC
            //             ) AS rn_periode
            //             FROM
            //             Karyawan a
            //             INNER JOIN View_Divisi_Sub_Divisi f ON a.ID_Divisi_Sub_Divisi = f.ID_DIVISI_SUB_DIVISI
            //             LEFT JOIN HRIS_Shift_Per_Karyawan e ON a.Kode_Karyawan = e.Kode_Karyawan
            //             AND a.Kode_Perusahaan = e.Kode_Perusahaan
            //             AND e.Periode <= ?
            //             LEFT JOIN HRIS_Shift_Kerja b ON e.ID_Shift = b.ID_Shift
            //             LEFT JOIN HRIS_Shift_Kerja_Detail c ON b.ID_Shift = c.ID_Shift
            //             LEFT JOIN HRIS_Waktu_Kerja d ON c.ID_Waktu_Kerja = d.ID_Waktu_Kerja


            //         ),
            //         ShiftPermanen AS (
            //             SELECT
            //             Kode_Karyawan,
            //             nama_divisi,
            //             nama_sub_divisi,
            //             UserID_Absen,
            //             Jam_Keluar,
            //             Nama,
            //             ID_Shift,
            //             Nama_Shift,
            //             'Permanen' as Jenis_Shift,
            //             Periode
            //             FROM
            //             KaryawanShiftPermanen
            //             WHERE
            //             rn_periode = 1
            //         ),
            //         ShiftSementara AS (
            //             SELECT
            //             e.Kode_Karyawan,
            //             f.nama_divisi,
            //             f.nama_sub_divisi,
            //             e.UserID_Absen,
            //             e.Nama,
            //             c.Jam_Keluar,
            //             a.ID_Shift,
            //             a.Nama as Nama_Shift,
            //             'Sementara' as Jenis_Shift,
            //             d.Tanggal as Periode
            //             FROM
            //             HRIS_Shift_Kerja a
            //             INNER JOIN HRIS_Shift_Kerja_Detail b ON a.ID_Shift = b.ID_Shift
            //             INNER JOIN HRIS_Waktu_Kerja c ON b.ID_Waktu_Kerja = c.ID_Waktu_Kerja
            //             INNER JOIN HRIS_Shift_Sementara d ON a.ID_Shift = d.ID_Shift
            //             INNER JOIN Karyawan e ON d.Kode_Perusahaan = e.Kode_Perusahaan
            //             AND d.Kode_Karyawan = e.Kode_Karyawan
            //             INNER JOIN View_Divisi_Sub_Divisi f ON e.ID_Divisi_Sub_Divisi = f.ID_DIVISI_SUB_DIVISI
            //             WHERE
            //             e.Kode_Perusahaan = ?
            //             AND b.Hari = ?
            //             AND f.ID_Divisi = ?
            //             AND d.Tanggal = ?
            //         )
            //         SELECT
            //         CASE
            //             WHEN DATEADD(
            //             HOUR,
            //             1,
            //             CONVERT(TIME, COALESCE(ss.Jam_Keluar, sp.Jam_Keluar))
            //             ) <= ? AND COALESCE(ss.Nama_Shift, sp.Nama_Shift) IS NOT NULL THEN 'TRUE'
            //             ELSE 'FALSE'
            //         END AS JamComparison,
            //         COALESCE(ss.Kode_Karyawan, sp.Kode_Karyawan) as userId,
            //         COALESCE(ss.UserID_Absen, sp.UserID_Absen) as UserID_Absen,
            //         COALESCE(ss.nama_divisi, sp.nama_divisi) as Divisi,
            //         COALESCE(ss.nama_sub_divisi, sp.nama_sub_divisi) as Sub_Divisi,
            //         COALESCE(ss.Nama, sp.Nama) as name,
            //         COALESCE(ss.Jam_Keluar, sp.Jam_Keluar) as Jam_Keluar,
            //         COALESCE(ss.ID_Shift, sp.ID_Shift) as ID_Shift,
            //         COALESCE(ss.Nama_Shift, sp.Nama_Shift, 'NO SHIFT') as Nama_Shift,
            //         COALESCE(ss.Jenis_Shift, sp.Jenis_Shift) as Jenis_Shift
            //         FROM
            //         ShiftPermanen sp
            //         FULL OUTER JOIN ShiftSementara ss ON sp.Kode_Karyawan = ss.Kode_Karyawan
            //         ORDER BY
            //         JamComparison desc
            // ";

            // $result = DB::select($query, [
            //     $Hari,
            //     $Hari,
            //     $Tanggal,
            //     $Kode_Perusahaan,
            //     $Hari,
            //     $divisi,
            //     $Tanggal,
            //     $time
            // ]);

            // dd($hashResult);

            return response()->json([
                'status' => 200,
                'data' => $hashResult
            ]);
        }catch (\Throwable $e) {
            Log::channel('lemburLog')->error('Gagal megambil data userActive: ' . $e->getMessage());
            return response()->json([
                'status' => 404,
                'error' => 'Terjadi error saat mengambil data userActive',
            ], 404);
        }

    }
    public function userActiveAll(Request $request){
        // param
        // dd($request->all());
        try{
            $Kode_Perusahaan = '001';
            // $ID_shift = $request->input('shift');
            $Hari = date('N',strtotime($request->date));
            $time = date('H:i:s',strtotime($request->input('time')));
            $Shift = $request->shift;
            // dd($time);
            if ($Hari <= 6 ) {
                $Hari += 1 ;
            } elseif ($Hari == 7) {
                $Hari = 1;
            }
            $Tanggal = $request->date;
            $divisi =  Auth()->user()->divisionKaryawan->ID_Divisi;
            // dd($divisi);
            $karyawan =Auth()->user()->karyawan->Kode_Karyawan;
            // dd($Tanggal);


            $query = "
                WITH KaryawanShiftPermanen AS (
                    SELECT
                        e.Kode_Karyawan,
                        f.nama_divisi,
                        f.nama_sub_divisi,
                        e.UserID_Absen,
                        c.Jam_Masuk,
                        c.Jam_Keluar,
                        e.Nama,
                        a.ID_Shift,
                        a.Nama as Nama_Shift,
                        d.Periode,
                        COALESCE(NULLIF(c.Jam_Selesai_Lintas_Hari, ''), NULLIF(c.Jam_Selesai_Absen_Keluar, '')) AS Jam_selesai_absen,
                        ROW_NUMBER() OVER (
                            PARTITION BY e.Kode_Karyawan
                            ORDER BY d.Periode DESC, d.Urut DESC
                        ) as rn_periode
                    FROM HRIS_Shift_Kerja a
                    INNER JOIN HRIS_Shift_Kerja_Detail b ON a.ID_Shift = b.ID_Shift
                    INNER JOIN HRIS_Waktu_Kerja c ON b.ID_Waktu_Kerja = c.ID_Waktu_Kerja
                    INNER JOIN HRIS_Shift_Per_Karyawan d ON a.ID_Shift = d.ID_Shift
                    INNER JOIN Karyawan e ON d.Kode_Karyawan = e.Kode_Karyawan
                                        AND d.Kode_Perusahaan = e.Kode_Perusahaan

                    INNER JOIN View_Divisi_Sub_Divisi f ON e.ID_Divisi_Sub_Divisi = f.ID_DIVISI_SUB_DIVISI
                    INNER JOIN View_Golongan_Sub_Golongan_Level_Jabatan g ON e.ID_Level_Jabatan = g.ID_Level_Jabatan
                    WHERE e.Kode_Perusahaan = ?
                    AND g.ID_Level  IN (1,2,3)
                    AND b.Hari = ?

                    AND d.Periode <= ?

                ),
                ShiftPermanen AS (
                    SELECT
                        Kode_Karyawan,
                        nama_divisi,
                        nama_sub_divisi,
                        UserID_Absen,
                        Nama,
                        Jam_Masuk,
                        Jam_Keluar,
                        ID_Shift,
                        Nama_Shift,
                        'Permanen' as Jenis_Shift,
                        Periode,
                        Jam_selesai_absen
                    FROM KaryawanShiftPermanen
                    WHERE rn_periode = 1
                ),
               ShiftSementaraRaw AS (
                SELECT
                e.Kode_Karyawan,
                f.nama_divisi,
                f.nama_sub_divisi,
                e.UserID_Absen,
                e.Nama,
                c.Jam_Masuk,
                c.Jam_Keluar,
                a.ID_Shift,
                a.Nama as Nama_Shift,
                'Sementara' as Jenis_Shift,
                d.Tanggal as Periode,
                COALESCE(
                    NULLIF(c.Jam_Selesai_Lintas_Hari, ''),
                    NULLIF(c.Jam_Selesai_Absen_Keluar, '')
                ) AS Jam_selesai_absen,
                ROW_NUMBER() OVER (
                    PARTITION BY
                    e.Kode_Karyawan
                    ORDER BY
                    d.Urut DESC,
                    d.Tanggal DESC
                ) AS rn
                FROM
                HRIS_Shift_Kerja a
                INNER JOIN HRIS_Shift_Kerja_Detail b ON a.ID_Shift = b.ID_Shift
                INNER JOIN HRIS_Waktu_Kerja c ON b.ID_Waktu_Kerja = c.ID_Waktu_Kerja
                INNER JOIN HRIS_Shift_Sementara d ON a.ID_Shift = d.ID_Shift
                INNER JOIN Karyawan e ON d.Kode_Perusahaan = e.Kode_Perusahaan
                AND d.Kode_Karyawan = e.Kode_Karyawan
                INNER JOIN View_Divisi_Sub_Divisi f ON e.ID_Divisi_Sub_Divisi = f.ID_DIVISI_SUB_DIVISI
                INNER JOIN View_Golongan_Sub_Golongan_Level_Jabatan g ON e.ID_Level_Jabatan = g.ID_Level_Jabatan
                WHERE
                e.Kode_Perusahaan = ?
                AND g.ID_Level IN (1, 2, 3)
                AND b.Hari = ?
                AND d.Tanggal = ?
            ),
            ShiftSementara AS (
                SELECT
                *
                FROM
                ShiftSementaraRaw
                WHERE
                rn = 1
            )
                SELECT
                    CASE
                        -- Kondisi untuk Shift Lintas Hari (Jam Keluar > Jam Selesai Absen)
                        WHEN DATEADD(HOUR, 1, CONVERT(TIME, COALESCE(ss.Jam_Keluar, sp.Jam_Keluar))) > CONVERT(TIME, COALESCE(ss.Jam_selesai_absen, sp.Jam_selesai_absen))
                        THEN
                            CASE
                                -- Jika '16:20:00' lebih besar dari jam mulai (misal > 15:30) ATAU lebih kecil dari jam selesai (misal < 03:00)
                                WHEN ? >= DATEADD(HOUR, 1, CONVERT(TIME, COALESCE(ss.Jam_Keluar, sp.Jam_Keluar))) OR ? <= CONVERT(TIME, COALESCE(ss.Jam_selesai_absen, sp.Jam_selesai_absen))
                                THEN 'TRUE'
                                ELSE 'FALSE'
                            END
                        -- Kondisi untuk Shift Normal (Jam Keluar <= Jam Selesai Absen)
                        ELSE
                            CASE
                                -- Cukup gunakan BETWEEN biasa
                                WHEN ? BETWEEN DATEADD(HOUR, 1, CONVERT(TIME, COALESCE(ss.Jam_Keluar, sp.Jam_Keluar))) AND CONVERT(TIME, COALESCE(ss.Jam_selesai_absen, sp.Jam_selesai_absen))
                                THEN 'TRUE'
                                ELSE 'FALSE'
                            END
                    END AS JamComparison,
                    CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM Transaksi_Lembur_Detail a, Transaksi_Lembur b
                        WHERE
                            a.No_Transaksi = b.No_Transaksi
                            AND Kode_Karyawan = COALESCE(ss.Kode_Karyawan, sp.Kode_Karyawan)
                            AND ? = CAST(Tanggal_Lembur_Dari AS DATE)
                            AND b.Status is null
                    )
                    THEN 'TRUE'
                    ELSE 'FALSE'
                    END as lemburPernah,
                    COALESCE(ss.Kode_Karyawan, sp.Kode_Karyawan) as userId,
                    COALESCE(ss.UserID_Absen, sp.UserID_Absen) as UserID_Absen,
                    COALESCE(ss.nama_divisi, sp.nama_divisi) as Divisi,
                    COALESCE(ss.nama_sub_divisi, sp.nama_sub_divisi) as Sub_Divisi,
                    COALESCE(ss.Nama, sp.Nama) as name,
                    COALESCE(ss.Jam_Masuk, sp.Jam_Masuk) as Jam_Masuk,
                    COALESCE(ss.Jam_Keluar, sp.Jam_Keluar) as Jam_Keluar,
                    COALESCE(ss.ID_Shift, sp.ID_Shift) as ID_Shift,
                    COALESCE(ss.Nama_Shift, sp.Nama_Shift) as Nama_Shift,
                    COALESCE(ss.Jam_selesai_absen, sp.Jam_selesai_absen) as Jam_selesai_absen,
                    COALESCE(ss.Jenis_Shift, sp.Jenis_Shift) as Jenis_Shift
                FROM ShiftPermanen sp
                FULL OUTER JOIN ShiftSementara ss ON sp.Kode_Karyawan = ss.Kode_Karyawan

                ORDER BY JamComparison desc
                ";

                // Parameter array harus berurutan sesuai dengan urutan ? dalam query
                $result = DB::select($query, [
                    $Kode_Perusahaan,  // Parameter 1: KaryawanShiftPermanen.Kode_Perusahaan
                    $Hari,             // Parameter 2: KaryawanShiftPermanen.Hari
                    $Tanggal,

                    $Kode_Perusahaan,  // Parameter 5: ShiftSementara.Kode_Perusahaan
                    $Hari,             // Parameter 6: ShiftSementara.Hari
                    $Tanggal,
                    $time,
                    $time,
                    $time,
                    $Tanggal,
                    $karyawan,           // Parameter 8: ShiftSementara.Tanggal

                ]);

            // dd($result);
            if (empty($result)) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Data tidak ditemukan',
                ], 404);
            }

            $resultCollect = collect($result);
            $hashResult = $resultCollect->map(function ($item) {
                    if (isset($item->userId)) {
                        $item->userId = Crypt::encryptString($item->userId);
                    }
                    return $item;
            });

            // dd(Crypt::decryptString('eyJpdiI6ImdzVUFLb0RnK0NNazQ2V0diWnpGL3c9PSIsInZhbHVlIjoiQWpsb2JYNlFyTFg3cFhNekNQUURpZz09IiwibWFjIjoiZTVhZjk4YzFmZTMyNThmZjFjODZhYzZjNzFlMDYyYmVhODNkNTE0NDM0MDliZDkyYWJmMDg2ZTVmNzM2NjU3OCIsInRhZyI6IiJ9'));

            // $query = "
            //         WITH
            //         KaryawanShiftPermanen AS (
            //             SELECT
            //             a.Kode_Karyawan,
            //             a.Nama AS Nama,
            //             f.nama_divisi,
            //             a.UserID_Absen,
            //             f.nama_sub_divisi,
            //             d.Jam_Keluar,
            //             CASE
            //                 WHEN c.Hari = ? THEN b.Nama
            //                 ELSE NULL
            //             END AS Nama_Shift,
            //             CASE
            //                 WHEN c.Hari = ? THEN b.ID_Shift
            //                 ELSE NULL
            //             END AS ID_Shift,
            //             c.Hari,
            //             e.Periode,
            //             ROW_NUMBER() OVER (
            //                 PARTITION BY
            //                 a.Kode_Karyawan
            //                 ORDER BY
            //                 e.Periode DESC
            //             ) AS rn_periode
            //             FROM
            //             Karyawan a
            //             INNER JOIN View_Divisi_Sub_Divisi f ON a.ID_Divisi_Sub_Divisi = f.ID_DIVISI_SUB_DIVISI
            //             LEFT JOIN HRIS_Shift_Per_Karyawan e ON a.Kode_Karyawan = e.Kode_Karyawan
            //             AND a.Kode_Perusahaan = e.Kode_Perusahaan
            //             AND e.Periode <= ?
            //             LEFT JOIN HRIS_Shift_Kerja b ON e.ID_Shift = b.ID_Shift
            //             LEFT JOIN HRIS_Shift_Kerja_Detail c ON b.ID_Shift = c.ID_Shift
            //             LEFT JOIN HRIS_Waktu_Kerja d ON c.ID_Waktu_Kerja = d.ID_Waktu_Kerja


            //         ),
            //         ShiftPermanen AS (
            //             SELECT
            //             Kode_Karyawan,
            //             nama_divisi,
            //             nama_sub_divisi,
            //             UserID_Absen,
            //             Jam_Keluar,
            //             Nama,
            //             ID_Shift,
            //             Nama_Shift,
            //             'Permanen' as Jenis_Shift,
            //             Periode
            //             FROM
            //             KaryawanShiftPermanen
            //             WHERE
            //             rn_periode = 1
            //         ),
            //         ShiftSementara AS (
            //             SELECT
            //             e.Kode_Karyawan,
            //             f.nama_divisi,
            //             f.nama_sub_divisi,
            //             e.UserID_Absen,
            //             e.Nama,
            //             c.Jam_Keluar,
            //             a.ID_Shift,
            //             a.Nama as Nama_Shift,
            //             'Sementara' as Jenis_Shift,
            //             d.Tanggal as Periode
            //             FROM
            //             HRIS_Shift_Kerja a
            //             INNER JOIN HRIS_Shift_Kerja_Detail b ON a.ID_Shift = b.ID_Shift
            //             INNER JOIN HRIS_Waktu_Kerja c ON b.ID_Waktu_Kerja = c.ID_Waktu_Kerja
            //             INNER JOIN HRIS_Shift_Sementara d ON a.ID_Shift = d.ID_Shift
            //             INNER JOIN Karyawan e ON d.Kode_Perusahaan = e.Kode_Perusahaan
            //             AND d.Kode_Karyawan = e.Kode_Karyawan
            //             INNER JOIN View_Divisi_Sub_Divisi f ON e.ID_Divisi_Sub_Divisi = f.ID_DIVISI_SUB_DIVISI
            //             WHERE
            //             e.Kode_Perusahaan = ?
            //             AND b.Hari = ?
            //             AND f.ID_Divisi = ?
            //             AND d.Tanggal = ?
            //         )
            //         SELECT
            //         CASE
            //             WHEN DATEADD(
            //             HOUR,
            //             1,
            //             CONVERT(TIME, COALESCE(ss.Jam_Keluar, sp.Jam_Keluar))
            //             ) <= ? AND COALESCE(ss.Nama_Shift, sp.Nama_Shift) IS NOT NULL THEN 'TRUE'
            //             ELSE 'FALSE'
            //         END AS JamComparison,
            //         COALESCE(ss.Kode_Karyawan, sp.Kode_Karyawan) as userId,
            //         COALESCE(ss.UserID_Absen, sp.UserID_Absen) as UserID_Absen,
            //         COALESCE(ss.nama_divisi, sp.nama_divisi) as Divisi,
            //         COALESCE(ss.nama_sub_divisi, sp.nama_sub_divisi) as Sub_Divisi,
            //         COALESCE(ss.Nama, sp.Nama) as name,
            //         COALESCE(ss.Jam_Keluar, sp.Jam_Keluar) as Jam_Keluar,
            //         COALESCE(ss.ID_Shift, sp.ID_Shift) as ID_Shift,
            //         COALESCE(ss.Nama_Shift, sp.Nama_Shift, 'NO SHIFT') as Nama_Shift,
            //         COALESCE(ss.Jenis_Shift, sp.Jenis_Shift) as Jenis_Shift
            //         FROM
            //         ShiftPermanen sp
            //         FULL OUTER JOIN ShiftSementara ss ON sp.Kode_Karyawan = ss.Kode_Karyawan
            //         ORDER BY
            //         JamComparison desc
            // ";

            // $result = DB::select($query, [
            //     $Hari,
            //     $Hari,
            //     $Tanggal,
            //     $Kode_Perusahaan,
            //     $Hari,
            //     $divisi,
            //     $Tanggal,
            //     $time
            // ]);

            // dd($hashResult);

            return response()->json([
                'status' => 200,
                'data' => $hashResult
            ]);
        }catch (\Throwable $e) {
            Log::channel('lemburLog')->error('Gagal megambil data userActiveAll: ' . $e->getMessage());
            return response()->json([
                'status' => 404,
                'error' => 'Terjadi error saat mengambil data userActiveAll',
            ], 404);
        }

    }

    public function submitAdmin(Request $request){
        // dd($request->params['dataUsers']);
        $format_tanggal_sekarang = date('m') . '-' . date('y');
        // dd($format_tanggal_sekarang);


        try{
            if($request->params['dataUsers']){
                $userData = $request->params['dataUsers'];

                // dd($no_faktur);
                    DB::beginTransaction();

                    $userSPV= Auth()->user()->karyawan->Kode_Karyawan;
                    $namaSPV = Auth()->user()->karyawan->Nama;
                    // dd($userSPV);
                    $Kode_Perusahaan = '001';
                    $Tanggal_Sekarang = date('Y-m-d');
                    $Jam_Sekarang = date('H:i:s');

                    // no Transaksi
                    $string = "SELECT TOP 1 ";
                    $string .= "RIGHT(No_Transaksi, 4) AS angka ";
                    $string .= "FROM Transaksi_Lembur ";
                    $string .= "WHERE LEFT(No_Transaksi, 8) = 'LM" . $format_tanggal_sekarang . "-' ";
                    $string .= "ORDER BY RIGHT(No_Transaksi, 4) DESC";

                    $check_last_faktur = DB::select($string);
                    if (count($check_last_faktur) == 0) {
                        $angka_terakhir = 1;
                    } else {
                        $angka_terakhir = (int)$check_last_faktur[0]->angka + 1;
                    }
                    $init = "LM". date('m-y');
                    $no_faktur = $init . '-' . sprintf('%04d', $angka_terakhir);

                    Lembur::insert([
                        'Kode_Perusahaan' => $Kode_Perusahaan,
                        'No_Transaksi' => $no_faktur,
                        'Tanggal' => $Tanggal_Sekarang,
                        'Jam' => $Jam_Sekarang,
                        'UserID' => $userSPV
                    ]);

                    foreach($userData as $user){
                        //
                        $jenis = 'LEMBUR';
                        $Tanggal_lembur_saja = date('Y-m-d H:i:s',strtotime($user['tanggal']));
                        $Kode_Karyawan = Crypt::decryptString($user['id']);
                        // dd($Kode_Karyawan);

                        if(strtotime($user['waktu']) <= strtotime($user['Jam_MasukShift'])){
                            $tanggalKeluarLembur = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($user['tanggal'] . ' ' . $user['waktu'])));
                        }else{
                            $tanggalKeluarLembur = date('Y-m-d H:i:s', strtotime($user['tanggal'] . ' ' . $user['waktu']));
                        }

                        if(strtotime($user['masuk']) <= strtotime($user['Jam_MasukShift'])){
                            $tanggalMasukLembur = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($user['tanggal'] . ' ' . $user['masuk'])));
                        }else{
                            $tanggalMasukLembur = date('Y-m-d H:i:s', strtotime($user['tanggal'] . ' ' . $user['masuk']));
                        }
                        // dd($tanggalMasukLembur.' - ' .$tanggalKeluarLembur);
                        $alasan = str_replace(["\n", "\t"], ' ', $user['reason']);
                        $userInsert = Karyawan::where('Kode_Karyawan', $Kode_Karyawan)->first() ?? null;
                        // dd($userInsert->No_Hp);
                        $dataDet =  LemburDetail::insertGetId([
                            'Kode_Perusahaan' => $Kode_Perusahaan,
                            'No_Transaksi' => $no_faktur,
                            'Kode_Karyawan' => $Kode_Karyawan,
                            'Jenis' => $jenis,
                            'Tanggal_Lembur_Dari' => $tanggalMasukLembur,
                            'Tanggal_Lembur_Sampai' => $tanggalKeluarLembur,
                            'Alasan' => $alasan
                        ]);

                        // dd($dataDet);
                        $urutDetail = $dataDet;

                        $finalInsert = LemburDet::create([
                            'Kode_Perusahaan' => $Kode_Perusahaan,
                            'No_Transaksi' => $no_faktur,
                            'Kode_Karyawan' => $Kode_Karyawan,
                            'Urut_Detail' => $urutDetail,
                            'Tanggal_Lembur' => $Tanggal_lembur_saja,
                        ]);

                        $userInsertNoHp = Karyawan::where('Kode_Karyawan', $Kode_Karyawan)->first()->HP ?? null;




                    }
                DB::commit();
                Alert::success('Success', 'Lembur Berhasil Disimpan!');
                return response()->json([
                    'status' => 200,
                    'message' => 'Berhasil Menambahkan lembur!',
                ]);
            }
        }catch (\Throwable $e) {
            DB::rollBack();
            Log::channel('lemburLog')->error('Gagal simpan lembur: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to Save Lembur',
                'error' => 'An error occurred while saving the cross review, Please Try Again' . $e->getMessage(),
            ], 500);
        }

    }

    public function submit(Request $request){
        // dd($request->params['dataUsers']);
        $format_tanggal_sekarang = date('m') . '-' . date('y');
        // dd($format_tanggal_sekarang);


        try{
            if($request->params['dataUsers']){
                $userData = $request->params['dataUsers'];

                // dd($no_faktur);
                    DB::beginTransaction();

                    $userSPV= Auth()->user()->karyawan->Kode_Karyawan;
                    $namaSPV = Auth()->user()->karyawan->Nama;
                    // dd($userSPV);
                    $Kode_Perusahaan = '001';
                    $Tanggal_Sekarang = date('Y-m-d');
                    $Jam_Sekarang = date('H:i:s');

                    // no Transaksi
                    $string = "SELECT TOP 1 ";
                    $string .= "RIGHT(No_Transaksi, 4) AS angka ";
                    $string .= "FROM Transaksi_Lembur ";
                    $string .= "WHERE LEFT(No_Transaksi, 8) = 'LM" . $format_tanggal_sekarang . "-' ";
                    $string .= "ORDER BY RIGHT(No_Transaksi, 4) DESC";

                    $check_last_faktur = DB::select($string);
                    if (count($check_last_faktur) == 0) {
                        $angka_terakhir = 1;
                    } else {
                        $angka_terakhir = (int)$check_last_faktur[0]->angka + 1;
                    }
                    $init = "LM". date('m-y');
                    $no_faktur = $init . '-' . sprintf('%04d', $angka_terakhir);

                    Lembur::insert([
                        'Kode_Perusahaan' => $Kode_Perusahaan,
                        'No_Transaksi' => $no_faktur,
                        'Tanggal' => $Tanggal_Sekarang,
                        'Jam' => $Jam_Sekarang,
                        'UserID' => $userSPV
                    ]);

                    foreach($userData as $user){
                        //
                        $jenis = 'LEMBUR';
                        $Tanggal_lembur_saja = date('Y-m-d H:i:s',strtotime($user['tanggal']));
                        $Kode_Karyawan = Crypt::decryptString($user['id']);
                        // dd($Kode_Karyawan);

                        if(strtotime($user['waktu']) <= strtotime($user['Jam_MasukShift'])){
                            $tanggalKeluarLembur = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($user['tanggal'] . ' ' . $user['waktu'])));
                        }else{
                            $tanggalKeluarLembur = date('Y-m-d H:i:s', strtotime($user['tanggal'] . ' ' . $user['waktu']));
                        }

                        if(strtotime($user['masuk']) <= strtotime($user['Jam_MasukShift'])){
                            $tanggalMasukLembur = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($user['tanggal'] . ' ' . $user['masuk'])));
                        }else{
                            $tanggalMasukLembur = date('Y-m-d H:i:s', strtotime($user['tanggal'] . ' ' . $user['masuk']));
                        }
                        // dd($tanggalMasukLembur.' - ' .$tanggalKeluarLembur);
                        $alasan = str_replace(["\n", "\t"], ' ', $user['reason']);
                        $userInsert = Karyawan::where('Kode_Karyawan', $Kode_Karyawan)->first() ?? null;
                        // dd($userInsert->No_Hp);
                        $dataDet =  LemburDetail::insertGetId([
                            'Kode_Perusahaan' => $Kode_Perusahaan,
                            'No_Transaksi' => $no_faktur,
                            'Kode_Karyawan' => $Kode_Karyawan,
                            'Jenis' => $jenis,
                            'Tanggal_Lembur_Dari' => $tanggalMasukLembur,
                            'Tanggal_Lembur_Sampai' => $tanggalKeluarLembur,
                            'Alasan' => $alasan
                        ]);

                        // dd($dataDet);
                        $urutDetail = $dataDet;

                        $finalInsert = LemburDet::create([
                            'Kode_Perusahaan' => $Kode_Perusahaan,
                            'No_Transaksi' => $no_faktur,
                            'Kode_Karyawan' => $Kode_Karyawan,
                            'Urut_Detail' => $urutDetail,
                            'Tanggal_Lembur' => $Tanggal_lembur_saja,
                        ]);

                        $userInsertNoHp = Karyawan::where('Kode_Karyawan', $Kode_Karyawan)->first()->HP ?? null;
                        if($userInsert && $finalInsert && $userInsertNoHp && $userInsertNoHp != '-'){
                        $userInsertNama =  $userInsert->Nama;

                        $tangalMasukUser = date('H:i d M Y', strtotime($tanggalMasukLembur));
                        $tangalKeluarUser = date('H:i d M Y', strtotime($tanggalKeluarLembur));
                            // whatsapp message to user
                        $pesan = [
                                        "messaging_product" => "whatsapp",
                                        "to" => $userInsertNoHp,
                                        "type" => "template",
                                        "template" => [
                                            "name" => "notif_lembur",
                                            "language" => [
                                                "code" => "id",
                                                "policy" => "deterministic"
                                            ],
                                            "components" => [
                                                [
                                                    "type" => "body",
                                                    "parameters" => [
                                                        [
                                                            "type" => "text",
                                                            "text" => $userInsertNama
                                                        ],
                                                        [
                                                            "type" => "text",
                                                            "text" => $no_faktur
                                                        ],
                                                        [
                                                            "type" => "text",
                                                            "text" => $tangalMasukUser.' s.d '.$tangalKeluarUser
                                                        ],
                                                        [
                                                            "type" => "text",
                                                            "text" => $alasan
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ];


                            $response = Whatsapp::send_message($pesan);
                            Log::channel('waLemburLog')->warning('Pesan Error', [
                                "pesan" => $response
                            ]);
                        }



                    }
                DB::commit();
                Alert::success('Success', 'Lembur Berhasil Disimpan!');
                return response()->json([
                    'status' => 200,
                    'message' => 'Berhasil Menambahkan lembur!',
                ]);
            }
        }catch (\Throwable $e) {
            DB::rollBack();
            Log::channel('lemburLog')->error('Gagal simpan lembur: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to Save Lembur',
                'error' => 'An error occurred while saving the cross review, Please Try Again' . $e->getMessage(),
            ], 500);
        }

    }

    public function destroy(Request $request)
    {
        $Authen = User::where('Id_Users', Auth()->user()->Id_Users)->exists(); //true
        if (!$Authen) {
            return response()->json([
                'message' => 'Failed Destroy Item',
                'error'   => 'Pls Try again'
            ], 500);
        }

        try {
            DB::beginTransaction();

            // ✅ Ambil data transaksi
            $lembur = Lembur::where('No_Transaksi', $request['params']['No_Transaksi'])->first();

            if (!$lembur) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $lemburDetail = LemburDetail::where("No_Transaksi", $lembur->No_Transaksi)->first();
            // dd($lemburDetail);
            // ✅ Cek tanggal transaksi
            if ($lemburDetail->Tanggal_Lembur_Sampai < now()) {
                DB::rollBack();
                return response()->json([
                    'status'  => 400,
                    'message' => 'Transaksi sudah lewat dari hari ini, tidak bisa dihapus!'
                ], 400);
            }

            // ✅ Update status
            Lembur::where('No_Transaksi', $request['params']['No_Transaksi'])
                ->update(['Status' => 'Y']);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Berhasil menghapus data!'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::channel('lemburLog')->error('Gagal menghapus Item destroy ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan menghapus data destroy',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // public function destroyUser(Request $request){
    //     // dd($request['params']['urut_Oto']);
    //     try{
    //         DB::BeginTransaction();
    //         $detail = LemburDetail::where('Urut_Oto', $request['params']['urut_Oto'])->first();
    //         if(!$detail){
    //             DB::rollBack();
    //             return response()->json([
    //             'message' => 'Failed Destroy Item',
    //             'error' => 'Data tidak ditemukan'
    //             ],404);
    //         }

    //         $detail->delete();
    //         LemburDet::where('Urut_Detail', $request['params']['urut_Oto'])->delete();

    //         $cekIsiLemburDetail = LemburDetail::where('No_Transaksi', $detail->No_Transaksi)->count();
    //         $cekIsiLemburDet = LemburDet::where('No_Transaksi', $detail->No_Transaksi)->count();
    //         if($cekIsiLemburDetail == 0 || $cekIsiLemburDet == 0){
    //             Lembur::where('No_Transaksi', $detail->No_Transaksi)->update(['Status' => 'Y']);
    //         }

    //         //  LemburDet::where('No_Transaksi', $request['params']['No_Transaksi'])->update(['Status' => 'Y']);
    //         //  LemburDetail::where('No_Transaksi', $request['params']['No_Transaksi'])->update(['Status' => 'Y']);
    //         DB::commit();
    //         Alert::success('Success', 'User Berhasil DiHapus dari Transaksi!');
    //         return response()->json([
    //             'status' => 200,
    //             'message' => "Berhasi Menghapus data!"
    //         ]);

    //     }catch(\throwable $e){
    //         DB::rollBack();
    //         Log::channel('lemburLog')->error('Gagal menghapus Item destroy'. $e->getMessage());
    //         return response()->json([
    //             'message' => 'Terjadi kesalahan menghapus data destroy',
    //             'error' => 'Pls Try again'
    //         ],500);
    //     }

    //     // $item =  $re
    // }

    public function destroyUser(Request $request)
    {
        try {
            DB::beginTransaction();

            $detail = LemburDetail::where('Urut_Oto', $request['params']['urut_Oto'])->first();

            if (!$detail) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Failed Destroy Item',
                    'error'   => 'Data tidak ditemukan'
                ], 404);
            }

            // ✅ Ambil transaksi induk dari tabel Lembur
            // $lembur = Lembur::where('No_Transaksi', $detail->No_Transaksi)->first();

            // if (!$lembur) {
            //     DB::rollBack();
            //     return response()->json([
            //         'status'  => 404,
            //         'message' => 'Data lembur induk tidak ditemukan'
            //     ], 404);
            // }

            if ($detail->Tanggal_Lembur_Sampai < now()) {
                DB::rollBack();
                return response()->json([
                    'status'  => 400,
                    'message' => 'Transaksi sudah lewat dari waktu lembur, tidak bisa dihapus!'
                ], 400);
            }

            // // ✅ Cek tanggal lembur (berpaku pada tabel Lembur)
            // if ($lembur->Tanggal < now()->startOfDay()) {
            //     DB::rollBack();
            //     return response()->json([
            //         'status'  => 400,
            //         'message' => 'Transaksi sudah lewat dari hari ini, tidak bisa dihapus!'
            //     ], 400);
            // }

            // ✅ Hapus detail & det (langsung delete, tidak update status)
            $detail->delete();
            LemburDet::where('Urut_Detail', $request['params']['urut_Oto'])->delete();

            // ✅ Kalau detail/det habis, update status Lembur
            $cekIsiLemburDetail = LemburDetail::where('No_Transaksi', $detail->No_Transaksi)->count();
            $cekIsiLemburDet    = LemburDet::where('No_Transaksi', $detail->No_Transaksi)->count();

            if ($cekIsiLemburDetail == 0 || $cekIsiLemburDet == 0) {
                Lembur::where('No_Transaksi', $detail->No_Transaksi)
                    ->update(['Status' => 'Y']);
            }

            DB::commit();
            Alert::success('Success', 'User Berhasil Dihapus dari Transaksi!');
            return response()->json([
                'status'  => 200,
                'message' => 'Berhasil Menghapus data!'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::channel('lemburLog')->error('Gagal menghapus Item destroy ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan menghapus data destroy',
                'error'   => $e->getMessage()
            ], 500);
        }
    }



    public function getDetails(Request $request){
        try{
            $No_Transaksi = $request->No_Transaksi;


            $query ="
            SELECT
            COALESCE(
                ShiftSementara.CHECKOUT_Status,
                ShiftPerKaryawan.CHECKOUT_Status,
                CASE
                WHEN CAST(c.Tanggal_Lembur_Sampai AS DATETIME) < CAST(GETDATE() AS DATETIME) THEN 'EXPIRED'
                ELSE 'IN PROGRESS'
                END
            ) AS CHECKOUT,
            d.Nama AS name,
            c.Urut_Oto,
            d.UserID_Absen AS userId,
            c.Tanggal_Lembur_Dari AS start_time,
            c.Tanggal_Lembur_Sampai AS end_time,
            c.Alasan AS reason
            FROM
            Transaksi_Lembur_Detail c
            INNER JOIN Karyawan d ON c.Kode_Karyawan = d.Kode_Karyawan
            OUTER APPLY (
                SELECT
                TOP (1) CASE
                -- If CHECKTIME is not NULL, status 'COMPLETED' and display checkout time
                    WHEN z.CHECKTIME IS NOT NULL THEN CONVERT(VARCHAR, z.CHECKTIME, 120)
                    -- If CHECKTIME is NULL and Tanggal_Lembur_Sampai is already past (EXPIRED)
                    WHEN z.CHECKTIME IS NULL
                    AND CAST(c.Tanggal_Lembur_Sampai AS DATETIME) < CAST(GETDATE() AS DATETIME) THEN 'EXPIRED'
                    -- If CHECKTIME is NULL and Tanggal_Lembur_Sampai is still valid (IN PROGRESS)
                    WHEN z.CHECKTIME IS NULL
                    AND CAST(c.Tanggal_Lembur_Sampai AS DATETIME) >= CAST(GETDATE() AS DATETIME) THEN 'IN PROGRESS'
                    ELSE 'UNKNOWN'
                END AS CHECKOUT_Status
                FROM
                HRIS_Shift_Kerja v
                INNER JOIN HRIS_Shift_Kerja_Detail w ON v.ID_Shift = w.ID_Shift
                INNER JOIN HRIS_Waktu_Kerja x ON w.ID_Waktu_Kerja = x.ID_Waktu_Kerja
                INNER JOIN HRIS_Shift_Sementara y ON v.ID_Shift = y.ID_Shift
                INNER JOIN CHECKINOUT z ON z.USERID = d.UserID_Absen
                WHERE
                y.Kode_Perusahaan = d.Kode_Perusahaan
                AND y.Kode_Karyawan = d.Kode_Karyawan
                AND z.CHECKTIME BETWEEN CONVERT(
                    DATETIME,
                    CONVERT(VARCHAR(10), c.Tanggal_Lembur_Dari, 120) + ' ' + x.Jam_Mulai_Absen_Keluar
                ) AND CONVERT(
                    DATETIME,
                    CONVERT(VARCHAR(10), c.Tanggal_Lembur_Sampai, 120) + ' ' + x.Jam_Selesai_Absen_Keluar
                )
                ORDER BY
                y.Tanggal DESC,
                y.Urut DESC
            ) AS ShiftSementara
            OUTER APPLY (
                SELECT
                TOP (1) CASE
                -- If CHECKTIME is not NULL, status 'COMPLETED' and display checkout time
                    WHEN z.CHECKTIME IS NOT NULL THEN CONVERT(VARCHAR, z.CHECKTIME, 120)
                    -- If CHECKTIME is NULL and Tanggal_Lembur_Sampai is already past (EXPIRED)
                    WHEN z.CHECKTIME IS NULL
                    AND CAST(c.Tanggal_Lembur_Sampai AS DATETIME) < CAST(GETDATE() AS DATETIME) THEN 'EXPIRED'
                    -- If CHECKTIME is NULL and Tanggal_Lembur_Sampai is still valid (IN PROGRESS)
                    WHEN z.CHECKTIME IS NULL
                    AND CAST(c.Tanggal_Lembur_Sampai AS DATETIME) >= CAST(GETDATE() AS DATETIME) THEN 'IN PROGRESS'
                    ELSE 'UNKNOWN'
                END AS CHECKOUT_Status
                FROM
                HRIS_Shift_Kerja v
                INNER JOIN HRIS_Shift_Kerja_Detail w ON v.ID_Shift = w.ID_Shift
                INNER JOIN HRIS_Waktu_Kerja x ON w.ID_Waktu_Kerja = x.ID_Waktu_Kerja
                INNER JOIN HRIS_Shift_Per_Karyawan y ON v.ID_Shift = y.ID_Shift
                INNER JOIN CHECKINOUT z ON z.USERID = d.UserID_Absen
                WHERE
                y.Kode_Perusahaan = d.Kode_Perusahaan
                AND y.Kode_Karyawan = d.Kode_Karyawan
                AND z.CHECKTIME BETWEEN CONVERT(
                    DATETIME,
                    CONVERT(VARCHAR(10), c.Tanggal_Lembur_Dari, 120) + ' ' + x.Jam_Mulai_Absen_Keluar
                ) AND CONVERT(
                    DATETIME,
                    CONVERT(VARCHAR(10), c.Tanggal_Lembur_Sampai, 120) + ' ' + x.Jam_Selesai_Absen_Keluar
                )
                ORDER BY
                y.Periode DESC,
                y.Urut DESC
            ) AS ShiftPerKaryawan
            WHERE
            c.No_Transaksi = ?
            ORDER BY
            c.Tanggal_Lembur_Dari DESC;

            ";

                $resultData = DB::Select($query, [$No_Transaksi]);
                // dd($resultData);
            return response()->json([
                'status' => 200,
                'data' => $resultData
            ]);
        }catch(\Throwable $e){
            Log::channel('lemburLog')->error('Gagal Mengambil data getDetails'. $e->getMessage());
            return response()->json([
                'status' => 404,
                'error' => "Tidak bisa mengambil data getDetails"
            ]);
        }


    }

    public function getExportAdmin(Request $request){
        try{

            $start = $request->input('start');
            $end   = $request->input('end');
            $divisi =  Auth()->user()->divisionKaryawan->ID_Divisi;
            $karyawanAuth = Auth()->user()->karyawan->Kode_Karyawan;

            $queryMaster = "
                SELECT
                    a.No_Transaksi, a.Kode_Perusahaan, a.Tanggal, a.Jam, b.Nama
                FROM
                    Transaksi_Lembur a, Karyawan b, View_Divisi_Sub_Divisi c
                WHERE
                    a.UserID = b.Kode_Karyawan
                    AND b.ID_Divisi_Sub_Divisi = c.ID_DIVISI_SUB_DIVISI

                    AND a.status IS NULL
                    AND CAST(a.Tanggal AS DATE) BETWEEN ? AND ?
                ORDER BY
                    a.Tanggal DESC, a.Jam DESC
            ";
            $transaksiMaster = DB::select($queryMaster, [ $start, $end]);
            if (empty($transaksiMaster)) {
                return response()->json([
                    'status' => 200,
                    'data'   => []
                ]);
            }

            $nomorTransaksi = [];
            foreach ($transaksiMaster as $tran) {
                $nomorTransaksi[] = $tran->No_Transaksi;
            }

            $placeholders = implode(',', array_fill(0, count($nomorTransaksi), '?'));

            // 4. Query Kedua: Ambil SEMUA detail dalam satu kali jalan
            // Query ini mengambil semua detail yang No_Transaksi-nya ada di dalam daftar $nomorTransaksi
            $queryDetail = "
                SELECT
                    c.No_Transaksi,
                    ISNULL(
                        (
                            SELECT TOP (1) CASE
                                WHEN z.CHECKTIME IS NOT NULL THEN CONVERT(VARCHAR, z.CHECKTIME, 120)
                                WHEN z.CHECKTIME IS NULL AND CAST(c.Tanggal_Lembur_Sampai AS DATETIME) < GETDATE() THEN 'EXPIRED'
                                WHEN z.CHECKTIME IS NULL AND CAST(c.Tanggal_Lembur_Sampai AS DATETIME) >= GETDATE() THEN 'IN PROGRESS'
                                ELSE 'UNKNOWN'
                            END
                            FROM HRIS_Shift_Kerja v
                            INNER JOIN HRIS_Shift_Kerja_Detail w ON v.ID_Shift = w.ID_Shift
                            INNER JOIN HRIS_Waktu_Kerja x ON w.ID_Waktu_Kerja = x.ID_Waktu_Kerja
                            INNER JOIN HRIS_Shift_Per_Karyawan y ON v.ID_Shift = y.ID_Shift
                            INNER JOIN CHECKINOUT z ON z.USERID = d.UserID_Absen
                            WHERE y.Kode_Perusahaan = d.Kode_Perusahaan
                                AND y.Kode_Karyawan = d.Kode_Karyawan
                                AND z.CHECKTIME BETWEEN
                                    CONVERT(DATETIME, CONVERT(VARCHAR(10), c.Tanggal_Lembur_Dari, 120) + ' ' + x.Jam_Mulai_Absen_Keluar) AND
                                    CONVERT(DATETIME, CONVERT(VARCHAR(10), c.Tanggal_Lembur_Sampai, 120) + ' ' + x.Jam_Selesai_Absen_Keluar)
                        ),
                        CASE
                            WHEN CAST(c.Tanggal_Lembur_Sampai AS DATETIME) < GETDATE() THEN 'EXPIRED'
                            ELSE 'IN PROGRESS'
                        END
                    ) AS CHECKOUT,
                    d.Nama AS name, c.Urut_Oto, d.UserID_Absen As userId,
                    c.Tanggal_Lembur_Dari AS start_time, c.Tanggal_Lembur_Sampai AS end_time, c.Alasan AS reason, e.nama_divisi AS dapartment , e.nama_sub_divisi AS divisi
                FROM
                    Transaksi_Lembur_Detail c
                    INNER JOIN Karyawan d ON c.Kode_Karyawan = d.Kode_Karyawan
                    INNER JOIN View_Divisi_Sub_Divisi e ON e.ID_DIVISI_SUB_DIVISI = d.ID_Divisi_Sub_Divisi
                WHERE
                    c.No_Transaksi IN ({$placeholders})
                ORDER BY
                    c.Tanggal_Lembur_Dari DESC
            ";
            $allDetails = DB::select($queryDetail, $nomorTransaksi);

            $detailsGrouped = [];
            foreach ($allDetails as $detail) {
                $detailsGrouped[$detail->No_Transaksi][] = $detail;
            }

            $finalData = [];
            foreach ($transaksiMaster as $master) {
                $currentDate = date('Y-m-d', strtotime($master->Tanggal));
                $currentNoTrans = $master->No_Transaksi;

                $finalData[$currentDate][$currentNoTrans] = [
                    'No_Transaksi' => $master->No_Transaksi,
                    'Kode_Perusahaan' => $master->Kode_Perusahaan,
                    'Tanggal' => $master->Tanggal,
                    'Jam' => $master->Jam,
                    'Nama' => $master->Nama,
                    // Jika tidak ada detail , berikan array kosong.
                    'details' => $detailsGrouped[$currentNoTrans] ?? []
                ];
            }

            // dd($finalData);

            return response()->json([
                'status' => 200,
                'data'   => $finalData
            ]);

        }catch (\Throwable $e) {
            Log::channel('lemburLog')->error('Gagal megambil data exportAdmin: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'error' => 'Terjadi error saat mengambil data exportAdmin',
            ], 500);
        }
    }
    public function getExport(Request $request){
        try{


            $start = $request->input('start');
            $end   = $request->input('end');
            $divisi =  Auth()->user()->divisionKaryawan->ID_Divisi;
            $karyawanAuth = Auth()->user()->karyawan->Kode_Karyawan;

            $queryMaster = "
                SELECT
                    a.No_Transaksi, a.Kode_Perusahaan, a.Tanggal, a.Jam, b.Nama
                FROM
                    Transaksi_Lembur a, Karyawan b, View_Divisi_Sub_Divisi c
                WHERE
                    a.UserID = b.Kode_Karyawan
                    AND b.ID_Divisi_Sub_Divisi = c.ID_DIVISI_SUB_DIVISI
                    AND a.UserID = ?
                    AND a.status IS NULL
                    AND CAST(a.Tanggal AS DATE) BETWEEN ? AND ?
                ORDER BY
                    a.Tanggal DESC, a.Jam DESC
            ";
            $transaksiMaster = DB::select($queryMaster, [$karyawanAuth, $start, $end]);
            if (empty($transaksiMaster)) {
                return response()->json([
                    'status' => 200,
                    'data'   => []
                ]);
            }

            $nomorTransaksi = [];
            foreach ($transaksiMaster as $tran) {
                $nomorTransaksi[] = $tran->No_Transaksi;
            }

            $placeholders = implode(',', array_fill(0, count($nomorTransaksi), '?'));

            // 4. Query Kedua: Ambil SEMUA detail dalam satu kali jalan
            // Query ini mengambil semua detail yang No_Transaksi-nya ada di dalam daftar $nomorTransaksi
            $queryDetail = "
                SELECT
                    c.No_Transaksi,
                    ISNULL(
                        (
                            SELECT TOP (1) CASE
                                WHEN z.CHECKTIME IS NOT NULL THEN CONVERT(VARCHAR, z.CHECKTIME, 120)
                                WHEN z.CHECKTIME IS NULL AND CAST(c.Tanggal_Lembur_Sampai AS DATETIME) < GETDATE() THEN 'EXPIRED'
                                WHEN z.CHECKTIME IS NULL AND CAST(c.Tanggal_Lembur_Sampai AS DATETIME) >= GETDATE() THEN 'IN PROGRESS'
                                ELSE 'UNKNOWN'
                            END
                            FROM HRIS_Shift_Kerja v
                            INNER JOIN HRIS_Shift_Kerja_Detail w ON v.ID_Shift = w.ID_Shift
                            INNER JOIN HRIS_Waktu_Kerja x ON w.ID_Waktu_Kerja = x.ID_Waktu_Kerja
                            INNER JOIN HRIS_Shift_Per_Karyawan y ON v.ID_Shift = y.ID_Shift
                            INNER JOIN CHECKINOUT z ON z.USERID = d.UserID_Absen
                            WHERE y.Kode_Perusahaan = d.Kode_Perusahaan
                                AND y.Kode_Karyawan = d.Kode_Karyawan
                                AND z.CHECKTIME BETWEEN
                                    CONVERT(DATETIME, CONVERT(VARCHAR(10), c.Tanggal_Lembur_Dari, 120) + ' ' + x.Jam_Mulai_Absen_Keluar) AND
                                    CONVERT(DATETIME, CONVERT(VARCHAR(10), c.Tanggal_Lembur_Sampai, 120) + ' ' + x.Jam_Selesai_Absen_Keluar)
                        ),
                        CASE
                            WHEN CAST(c.Tanggal_Lembur_Sampai AS DATETIME) < GETDATE() THEN 'EXPIRED'
                            ELSE 'IN PROGRESS'
                        END
                    ) AS CHECKOUT,
                    d.Nama AS name, c.Urut_Oto, d.UserID_Absen As userId,
                    c.Tanggal_Lembur_Dari AS start_time, c.Tanggal_Lembur_Sampai AS end_time, c.Alasan AS reason
                FROM
                    Transaksi_Lembur_Detail c
                    INNER JOIN Karyawan d ON c.Kode_Karyawan = d.Kode_Karyawan
                WHERE
                    c.No_Transaksi IN ({$placeholders})
                ORDER BY
                    c.Tanggal_Lembur_Dari DESC
            ";
            $allDetails = DB::select($queryDetail, $nomorTransaksi);

            $detailsGrouped = [];
            foreach ($allDetails as $detail) {
                $detailsGrouped[$detail->No_Transaksi][] = $detail;
            }

            $finalData = [];
            foreach ($transaksiMaster as $master) {
                $currentDate = date('Y-m-d', strtotime($master->Tanggal));
                $currentNoTrans = $master->No_Transaksi;

                $finalData[$currentDate][$currentNoTrans] = [
                    'No_Transaksi' => $master->No_Transaksi,
                    'Kode_Perusahaan' => $master->Kode_Perusahaan,
                    'Tanggal' => $master->Tanggal,
                    'Jam' => $master->Jam,
                    'Nama' => $master->Nama,
                    // Jika tidak ada detail , berikan array kosong.
                    'details' => $detailsGrouped[$currentNoTrans] ?? []
                ];
            }

            // dd($finalData);

            return response()->json([
                'status' => 200,
                'data'   => $finalData
            ]);
        }catch (\Throwable $e) {
            Log::channel('lemburLog')->error('Gagal megambil data getExport: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'error' => 'Terjadi error saat mengambil data getExport',
            ], 500);
        }
    }

}
