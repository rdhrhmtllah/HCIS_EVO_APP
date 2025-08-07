<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Shift_Kerja;
use App\Models\LemburDetail;
use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Helpers\Whatsapp;
use Illuminate\Database\Eloquent\Builder;
use App\Jobs\SendWhatsappMessage;
use RealRashid\SweetAlert\Facades\Alert;


class ShiftController extends Controller
{
    //

public function index(){
    try{
        $title = "Change User Shift";
        $query = "select a.Kode_Karyawan as NAMA_KARYAWAN, b.Nama as SHIFT_PERMANEN ,
        isnull((select TOP 1 y.Nama + '(SHIFT SEMENTARA)' from HRIS_Shift_Sementara x, HRIS_Shift_Kerja y where x.ID_Shift = y.ID_Shift and x.Kode_Karyawan = a.Kode_karyawan and x.ID_Shift <> a.ID_Shift),b.Nama) as SHIFT_SEMENTARA
        from HRIS_Shift_Per_Karyawan a JOIN HRIS_Shift_Kerja b ON a.ID_Shift = b.ID_Shift   group by Kode_Karyawan, b.Nama, a.ID_Shift
        ";
        $result = DB::select($query);

        $divisi =  Auth()->user()->divisionKaryawan->nama_divisi;
        // dd($divisi);
        // Ambil tanggal hari ini
        $today = date('Y-m-d');
        $hPlus2 = date('Y-m-d', strtotime('+2 day', strtotime($today)));
        // Menghitung tanggal hari Minggu (awal minggu)
        $startOfWeek = date('Y-m-d', strtotime('last sunday', strtotime($today)));

        // Menghitung tanggal Sabtu (akhir minggu)
        $endOfWeek = date('Y-m-d', strtotime('next saturday', strtotime($startOfWeek)));

        // Menghasilkan array tanggal per minggu
        $weekDates = [];
        // $currentDate = $hPlus2;
       $currentDate = '2025-07-21';
        $today = date('Y-m-d');
        $weekDates = [];

        // Perulangan akan terus berjalan selama $currentDate kurang dari atau sama dengan $today
        while (strtotime($currentDate) <= strtotime($today)) {
            $weekDates[] = $currentDate;
            $currentDate = date('Y-m-d', strtotime("+1 day", strtotime($currentDate)));
        }

            // dd($weekDates);
        $querShift = "
            select
            a.ID_Shift,
            a.Nama,
            b.Hari,
            c.Jam_Masuk,
            c.Jam_Keluar,
            c.Flag_CO_Lintas_Hari,
            c.Jam_Selesai_Lintas_Hari
            from
            HRIS_Shift_Kerja a,
            HRIS_Shift_Kerja_Detail b,
            HRIS_Waktu_Kerja c
            where
            a.ID_Shift = b.ID_Shift
            and b.ID_Waktu_Kerja = c.ID_Waktu_Kerja

        ";
        $jenisShift = DB::select($querShift);
        // dd($jenisShift);



        return inertia('userShift', ['title' => $title, 'semuaShift' => $jenisShift, 'divisi' => $divisi, 'tanggalLoop' => $weekDates]);
    }catch (\Throwable $e) {
        Log::channel('shiftLog')->error('Gagal menampilkan page shift: ' . $e->getMessage());
        Alert::error('error', 'Terjadi Error saat menampilkan page shift!');
        return back();
    }
}
public function indexAdmin(){
    try{


        $title = "Change User Shift";
        $query = "select a.Kode_Karyawan as NAMA_KARYAWAN, b.Nama as SHIFT_PERMANEN ,
        isnull((select TOP 1 y.Nama + '(SHIFT SEMENTARA)' from HRIS_Shift_Sementara x, HRIS_Shift_Kerja y where x.ID_Shift = y.ID_Shift and x.Kode_Karyawan = a.Kode_karyawan and x.ID_Shift <> a.ID_Shift),b.Nama) as SHIFT_SEMENTARA
        from HRIS_Shift_Per_Karyawan a JOIN HRIS_Shift_Kerja b ON a.ID_Shift = b.ID_Shift   group by Kode_Karyawan, b.Nama, a.ID_Shift
        ";
        $result = DB::select($query);

        $divisi =  Auth()->user()->divisionKaryawan->nama_divisi;
        // dd($divisi);
        // Ambil tanggal hari ini
        $today = date('Y-m-d');
        $hPlus2 = date('Y-m-d', strtotime('+2 day', strtotime($today)));
        // Menghitung tanggal hari Minggu (awal minggu)
        $startOfWeek = date('Y-m-d', strtotime('last sunday', strtotime($today)));

        // Menghitung tanggal Sabtu (akhir minggu)
        $endOfWeek = date('Y-m-d', strtotime('next saturday', strtotime($startOfWeek)));

        // Menghasilkan array tanggal per minggu
        $weekDates = [];
        $currentDate = $hPlus2;

        for ($i = 0; $i < 14; $i++) {
            $weekDates[] = $currentDate;

            $currentDate = date('Y-m-d', strtotime("+1 day", strtotime($currentDate)));
        }
            // dd($weekDates);
        $querShift = "
            select
            a.ID_Shift,
            a.Nama,
            b.Hari,
            c.Jam_Masuk,
            c.Jam_Keluar,
            c.Flag_CO_Lintas_Hari,
            c.Jam_Selesai_Lintas_Hari
            from
            HRIS_Shift_Kerja a,
            HRIS_Shift_Kerja_Detail b,
            HRIS_Waktu_Kerja c
            where
            a.ID_Shift = b.ID_Shift
            and b.ID_Waktu_Kerja = c.ID_Waktu_Kerja

        ";
        $jenisShift = DB::select($querShift);
        // dd($jenisShift);



        return inertia('shiftAdmin', ['title' => $title, 'semuaShift' => $jenisShift, 'divisi' => $divisi, 'tanggalLoop' => $weekDates]);
    }catch (\Throwable $e) {
        Log::channel('shiftLog')->error('Gagal menampilkan page shiftAdmin: ' . $e->getMessage());
        Alert::error('error', 'Terjadi Error saat menampilkan page shiftAdmin!');
        return back();
    }
}

public function getShift(Request $request){
    try{
        if($request['type'] === 'temporary'){
            $tanggal = $request['tanggal'][0];
        }else{
            $tanggal = $request['tanggal'];
        }
        // dd($tanggal);
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
        and a.Non_Shift = 'T'
        AND c.Jam_Keluar >= ?

        ";
        $result =  DB::select($query, [$Hari, $time]);


        } elseif ($Hari == 7) {
            $Hari = 1;
        }

        }else{

            // dd($tanggal);
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
        and a.Non_Shift = 'T'
        ";
    $result =  DB::select($query, [$Hari]);
    }


    // dd($result);
    return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }catch(\Throwable $e){
        Log::channel('shiftLog')->error('Gagal Mengambil data getShift'. $e->getMessage());
        return response()->json([
            'status' => 500,
            'error' => "Tidak bisa mengambil data getShift"
        ], 500);
    }
}

public function getWeek(Request $request)
{
    try{


        $startDate = $request->input('start_date', date('Y-m-d'));
        $startOfWeek = date('Y-m-d', strtotime('last Sunday', strtotime($startDate)));

        $endOfWeek = date('Y-m-d', strtotime('Saturday', strtotime($startOfWeek)));
        $divisi =  Auth()->user()->divisionKaryawan->ID_Divisi;
        $karyawan_Auth =Auth()->user()->karyawan->Kode_Karyawan;
        $queryUser = "
                    SELECT
                        e.Kode_Karyawan,
                        a.Nama,
                        a.UserID_Absen,
                        c.ID_Divisi
                        FROM
                        HRIS_Karyawan_Team e,
                        Karyawan a,
                        View_Divisi_Sub_Divisi c,
                        View_Golongan_Sub_Golongan_Level_Jabatan d

                        WHERE
                        a.ID_Level_Jabatan = d.ID_Level_Jabatan
                        AND a.UserID_Absen IS NOT NULL
                        AND a.ID_Divisi_Sub_Divisi = c.ID_DIVISI_SUB_DIVISI
                        AND a.Kode_Karyawan = e.Kode_Karyawan
                        AND e.Kode_Karyawan_Team = ?
                        AND d.ID_Level IN (1,2,3,4)
                        group by
                        a.Nama,
                        a.UserID_Absen,
                        c.ID_Divisi,
                        e.Kode_Karyawan

        ";


        // $queryUser = "
        //                 SELECT
        //                 a.Nama,
        //                 a.UserID_Absen,
        //                 c.ID_Divisi
        //                 FROM
        //                 Karyawan a,
        //                 HRIS_Shift_Per_Karyawan b,
        //                 View_Divisi_Sub_Divisi c,
        //                 View_Golongan_Sub_Golongan_Level_Jabatan d
        //                 WHERE
        //                 a.Kode_Karyawan = b.Kode_Karyawan
        //                 AND a.ID_Level_Jabatan = d.ID_Level_Jabatan
        //                 AND a.UserID_Absen IS NOT NULL
        //                 AND a.ID_Divisi_Sub_Divisi = c.ID_DIVISI_SUB_DIVISI
        //                 AND c.ID_Divisi = ?
        //                 AND a.Kode_Karyawan <> ?
        //                 AND d.ID_Level IN (1,2,3,4)
        //                 group by
        //                 a.Nama,
        //                 a.UserID_Absen,
        //                 c.ID_Divisi

        //                 ";
        $users = DB::select($queryUser,[$karyawan_Auth]);
        if(!$users){
            return response()->json([
                'status' => 404,
                'message' => 'Tidak Menemukan User yang mempunyai shift'
            ]);

        }
        // Buat array tanggal minggu
        $userData = [];
        $weekDates = [];
        $dateRange = [];
        $currentDate = $startOfWeek;

        // Generate semua tanggal dalam minggu
        for ($i = 0; $i < 7; $i++) {
            $dateRange[] = $currentDate;
            array_Push($weekDates, $currentDate);
            $currentDate = date('Y-m-d', strtotime("+1 day", strtotime($currentDate)));
        }
        // dd($dateRange);

        // Buat placeholder untuk UserIDs
        $userIds = array_column($users, 'UserID_Absen');
        $userIdPlaceholders = implode(',', array_fill(0, count($userIds), '?'));
        $datePlaceholders = implode(',', array_fill(0, count($dateRange), '?'));
        $userQuery = implode(',', $userIds);

        $query = "exec HRIS_SP_GET_ABSEN_NEW '001','$startOfWeek','$endOfWeek','$userQuery', ''";
        $resultData =  DB::select($query);
        //     $resultData = collect($resultData);  // Mengonversi array of stdClass menjadi koleksi

        // $found = $resultData->where('Nama', 'Garix')->first();  // Mencari objek dengan Nama 'Garix'

        // dd($found);

        // dd($users);


            foreach ($resultData as $idx => $itemUser) {
            // Menyimpan nama dan struktur data kosong untuk setiap user

                $userData[$itemUser->Nama] = ['Nama' => $itemUser->Nama, 'data' => []];

            }


        foreach ($userData as $parent) {
            foreach ($resultData as $idx => $shiftIDX) {
                // Mengecek kecocokan Nama Shift dengan nama User

                if ($shiftIDX->Nama == $parent['Nama']) {
                    // Menambahkan data ke dalam array data berdasarkan Nama User
                    $userData[$parent['Nama']]['data'][$idx] = [
                        'Tanggal' => date('Y-m-d', strtotime($shiftIDX->Tanggal)), // Format tanggal
                        'Nama_Shift' => $shiftIDX->Nama_Shift,
                        'hari' => date('N', strtotime($shiftIDX->Tanggal)), // Hari dalam angka (1 = Senin, 7 = Minggu)
                        'UserID_Absen' => $shiftIDX->UserID,
                        'Kode_Karyawan' => $shiftIDX ->Kode_Karyawan
                    ];
                }
            }
        }




            // dd($resultData);

            // dd($userData);



            // dd($datePlaceholders);
            // Query bulk untuk shift permanen - ambil semua data sekaligus
            // $queryPermanen = "
            //     SELECT
            //         e.Nama,
            //         a.Nama AS SHIFT,
            //         e.UserID_Absen,
            //         b.Hari,
            //         ? as QueryDate,
            //         ROW_NUMBER() OVER (PARTITION BY e.UserID_Absen, b.Hari ORDER BY d.Periode DESC) as rn
            //     FROM HRIS_Shift_Kerja a
            //     INNER JOIN HRIS_Shift_Kerja_Detail b ON a.ID_Shift = b.ID_Shift
            //     INNER JOIN HRIS_Waktu_Kerja c ON b.ID_Waktu_Kerja = c.ID_Waktu_Kerja
            //     INNER JOIN HRIS_Shift_Per_Karyawan d ON a.ID_Shift = d.ID_Shift
            //     INNER JOIN Karyawan e ON d.Kode_Karyawan = e.Kode_Karyawan AND d.Kode_Perusahaan = e.Kode_Perusahaan
            //     WHERE e.Kode_Perusahaan = '001'
            //         AND e.UserID_Absen IN ($userIdPlaceholders)
            //         AND d.Periode <= ?
            // ";

            // // Query bulk untuk shift sementara
            // $querySementara = "
            //     SELECT
            //         e.Nama,
            //         a.Nama AS SHIFT,
            //         e.UserID_Absen,
            //         b.Hari,
            //         d.Tanggal as QueryDate
            //     FROM HRIS_Shift_Kerja a
            //     INNER JOIN HRIS_Shift_Kerja_Detail b ON a.ID_Shift = b.ID_Shift
            //     INNER JOIN HRIS_Waktu_Kerja c ON b.ID_Waktu_Kerja = c.ID_Waktu_Kerja
            //     INNER JOIN HRIS_Shift_Sementara d ON a.ID_Shift = d.ID_Shift
            //     INNER JOIN Karyawan e ON d.Kode_Karyawan = e.Kode_Karyawan AND d.Kode_Perusahaan = e.Kode_Perusahaan
            //     WHERE e.Kode_Perusahaan = '001'
            //         AND e.UserID_Absen IN ($userIdPlaceholders)
            //         AND d.Tanggal IN ($datePlaceholders)
            // ";

            // // Ambil semua data shift permanen dan sementara sekaligus
            // $allPermanentShifts = [];
            // $allTemporaryShifts = [];

            // foreach ($dateRange as $date) {
            //     $params = array_merge([$date], $userIds, [$date]);
            //     $permanentResults = DB::select($queryPermanen, $params);

            //     // Filter hanya yang ranking 1 (terbaru)
            //     foreach ($permanentResults as $result) {
            //         if ($result->rn == 1) {
            //             $key = $result->UserID_Absen . '_' . $result->Hari . '_' . $date;
            //             $allPermanentShifts[$key] = $result;
            //         }
            //     }

            //     $tempParams = array_merge($userIds, $dateRange);
            //     $temporaryResults = DB::select($querySementara, $tempParams);

            //     foreach ($temporaryResults as $result) {
            //         $key = $result->UserID_Absen . '_' . $result->Hari . '_' . $result->QueryDate;
            //         $allTemporaryShifts[$key] = $result;
            //     }
            // }

            // dd($allPermanentShifts);


            // // Susun data final
            // foreach ($weekDates as $dayIndex => &$dayData) {
            //     $currentDate = $dayData['date'];
            //     $hari = date('N', strtotime($currentDate)); // 1 = Senin, 7 = Minggu

            //     foreach ($users as $user) {
            //         $key = $user->UserID_Absen . '_' . $hari . '_' . $currentDate;

            //         // Cek shift sementara terlebih dahulu (prioritas lebih tinggi)

            //         if (isset($allTemporaryShifts[$key])) {
            //             $dayData['data'][] = $allTemporaryShifts[$key];
            //         }
            //         // Jika tidak ada shift sementara, cek shift permanen
            //         elseif (isset($allPermanentShifts[$key])) {
            //             $dayData['data'][] = $allPermanentShifts[$key];
            //         }
            //         // Jika tidak ada keduanya, set NOSHIFT
            //         else {
            //             $dayData['data'][] = (object)["SHIFT" => "NOSHIFT", "Nama" => $user->Nama];
            //         }
            //     }
            // }
            // dd($userData);



            // Mengembalikan data dalam format JSON
            return response()->json([
                'week_dates' => $userData,
                'Tanggal' => $weekDates,
                // 'userData' =>
                'start_of_week' => $startOfWeek,
                'end_of_week' => $endOfWeek
            ]);
    }catch(\Throwable $e){
        Log::channel('shiftLog')->error('Gagal Mengambil data getWeek'. $e->getMessage());
        return response()->json([
            'status' => 500,
            'error' => "Tidak bisa mengambil data getWeek"
        ], 500);
    }
}

public function getWeekAdmin(Request $request)
{
    try{


        $startDate = $request->input('start_date', date('Y-m-d'));
        $startOfWeek = date('Y-m-d', strtotime('last Sunday', strtotime($startDate)));

        $endOfWeek = date('Y-m-d', strtotime('Saturday', strtotime($startOfWeek)));
        $divisi =  Auth()->user()->divisionKaryawan->ID_Divisi;
        $karyawan_Auth =Auth()->user()->karyawan->Kode_Karyawan;
        $queryUser = "
                    SELECT
                        a.Kode_Karyawan,
                        a.Nama,
                        a.UserID_Absen,
                        c.ID_Divisi,
                        c.nama_divisi
                        FROM

                        Karyawan a,
                        View_Divisi_Sub_Divisi c,
                        View_Golongan_Sub_Golongan_Level_Jabatan d

                        WHERE
                        a.ID_Level_Jabatan = d.ID_Level_Jabatan
                        AND a.UserID_Absen IS NOT NULL
                        AND a.ID_Divisi_Sub_Divisi = c.ID_DIVISI_SUB_DIVISI
                        AND d.ID_Level IN (1,2,3,4)
                        group by
                        a.Nama,
                        a.UserID_Absen,
                        c.ID_Divisi,
                        a.Kode_Karyawan,
                        c.nama_divisi

        ";


        $users = DB::select($queryUser);
        if(!$users){
            return response()->json([
                'status' => 404,
                'message' => 'Tidak Menemukan User yang mempunyai shift'
            ]);

        }
        // Buat array tanggal minggu
        $userData = [];
        $weekDates = [];
        $dateRange = [];
        $currentDate = $startOfWeek;

        // Generate semua tanggal dalam minggu
        for ($i = 0; $i < 7; $i++) {
            $dateRange[] = $currentDate;
            array_Push($weekDates, $currentDate);
            $currentDate = date('Y-m-d', strtotime("+1 day", strtotime($currentDate)));
        }
        // dd($dateRange);

        // Buat placeholder untuk UserIDs
        $userIds = array_column($users, 'UserID_Absen');
        $userIdPlaceholders = implode(',', array_fill(0, count($userIds), '?'));
        $datePlaceholders = implode(',', array_fill(0, count($dateRange), '?'));
        $userQuery = implode(',', $userIds);

        $query = "exec HRIS_SP_GET_ABSEN_NEW '001','$startOfWeek','$endOfWeek','$userQuery', ''";
        $resultData =  DB::select($query);
        //     $resultData = collect($resultData);  // Mengonversi array of stdClass menjadi koleksi

        // $found = $resultData->where('Nama', 'Garix')->first();  // Mencari objek dengan Nama 'Garix'

        // dd($found);

        // dd($users);


            foreach ($resultData as $idx => $itemUser) {
            // Menyimpan nama dan struktur data kosong untuk setiap user

                $userData[$itemUser->Nama] = ['Nama' => $itemUser->Nama, 'data' => []];

            }


        foreach ($userData as $parent) {
            foreach ($resultData as $idx => $shiftIDX) {

                // Mengecek kecocokan Nama Shift dengan nama User

                if ($shiftIDX->Nama == $parent['Nama']) {
                    // Menambahkan data ke dalam array data berdasarkan Nama User
                    $userData[$parent['Nama']]['data'][$idx] = [
                        'Tanggal' => date('Y-m-d', strtotime($shiftIDX->Tanggal)), // Format tanggal
                        'Nama_Shift' => $shiftIDX->Nama_Shift,
                        'hari' => date('N', strtotime($shiftIDX->Tanggal)), // Hari dalam angka (1 = Senin, 7 = Minggu)
                        'UserID_Absen' => $shiftIDX->UserID,
                        'Kode_Karyawan' => $shiftIDX ->Kode_Karyawan
                    ];
                }
            }
        }






            // Mengembalikan data dalam format JSON
            return response()->json([
                'week_dates' => $userData,
                'Tanggal' => $weekDates,
                'dataExtend' => $users,
                'start_of_week' => $startOfWeek,
                'end_of_week' => $endOfWeek
            ]);
        }catch(\Throwable $e){
        Log::channel('shiftLog')->error('Gagal Mengambil data getWeekAdmin'. $e->getMessage());
        return response()->json([
            'status' => 500,
            'error' => "Tidak bisa mengambil data getWeekAdmin"
        ], 500);
    }
    }

    public function getExport(Request $request)
    {
        try{


            $divisi = Auth()->user()->divisionKaryawan->ID_Divisi;
            $karyawan_Auth = Auth()->user()->karyawan->Kode_Karyawan;
            $startOfWeek = date('Y-m-d', strtotime($request->start));
            $endOfWeek = date('Y-m-d', strtotime($request->end));

            $queryUser = "
                SELECT a.Nama, a.UserID_Absen, c.ID_Divisi
                FROM Karyawan a
                JOIN HRIS_Shift_Per_Karyawan b ON a.Kode_Karyawan = b.Kode_Karyawan
                JOIN View_Divisi_Sub_Divisi c ON a.ID_Divisi_Sub_Divisi = c.ID_DIVISI_SUB_DIVISI
                JOIN HRIS_Karyawan_Team d ON a.Kode_Karyawan = d.Kode_Karyawan
                WHERE a.UserID_Absen IS NOT NULL
                AND a.Kode_Karyawan <> ?
                AND d.Kode_Karyawan_Team = ?
                GROUP BY a.Nama, a.UserID_Absen, c.ID_Divisi
            ";
            $users = DB::select($queryUser, [$karyawan_Auth, $karyawan_Auth]);

            if (empty($users)) {
                return response()->json(['status' => 200, 'data' => []]);
            }

            $userIds = array_column($users, 'UserID_Absen');
            $userListString = implode(',', $userIds);

            $query = "EXEC HRIS_SP_GET_ABSEN_NEW ?, ?, ?, ?, ?";
            $resultData = DB::select($query, ['001', $startOfWeek, $endOfWeek, $userListString, '']);

            $groupedData = [];
            foreach ($resultData as $item) {
                $date = date('Y-m-d', strtotime($item->Tanggal));
                $groupedData[$date][] = $item;
            }

            $finalData = [];
            $currentDate = $startOfWeek;
            while ($currentDate <= $endOfWeek) {
                $finalData[$currentDate] = $groupedData[$currentDate] ?? [];
                $currentDate = date('Y-m-d', strtotime("+1 day", strtotime($currentDate)));
            }

            return response()->json([
                'status' => 200,
                'data' => $finalData
            ]);
    }catch(\Throwable $e){
        Log::channel('shiftLog')->error('Gagal Mengambil data getExport'. $e->getMessage());
        return response()->json([
            'status' => 500,
            'error' => "Tidak bisa mengambil data getExport"
        ], 500);
    }
}


public function isLembur(Request $request){
    try{
        $employees = $request->employees;
        $dates = $request->dates;
        // dd($dates);

        $data = LemburDetail::select('Karyawan.Nama','Transaksi_Lembur.No_Transaksi', 'Tanggal_Lembur_Dari', 'Tanggal_Lembur_Sampai')->whereIn('Transaksi_Lembur_Detail.Kode_Karyawan', $employees)
        ->join('Transaksi_Lembur', 'Transaksi_Lembur_Detail.No_Transaksi', '=', 'Transaksi_Lembur.No_Transaksi')
        ->join('Karyawan', 'Transaksi_Lembur_Detail.Kode_Karyawan', '=', 'Karyawan.Kode_Karyawan')
        ->where('Transaksi_Lembur.Status', null)
        ->where(function (Builder $query) use ($dates) {

            foreach ($dates as $date) {
                $startOfDay = $date . ' 00:00:00';
                $endOfDay = $date . ' 23:59:59';
                $query->orWhere(function (Builder $q) use ($startOfDay, $endOfDay) {
                    $q->where('Tanggal_Lembur_Dari', '<=', $endOfDay)
                    ->where('Tanggal_Lembur_Sampai', '>=', $startOfDay);
                });
            }
        })->get();
        // dd($data);
            return response()->json([
                'status' => 200,
                'data' => $data
            ]);

    }catch(\Throwable $e){
        Log::channel('shiftLog')->error('Terjadi kesalahan saat mencek data isLembur'. $e->getMessage());
        return response()->json([
            'status' => 500,
            'Message' => 'Terjadi kesalahan saat mencek data isLembur'
        ], 500);

    }

}


public function submit(Request $request){
    $data = $request->All();
    $employees = $data['params']['AssignShift']['employees'];
    $Kode_Perusahaan = '001';
    $Id_Shift = $data['params']['AssignShift']['shift'];

    DB::beginTransaction();
    try{
        // checking Lembur
        // dd($request->all());
        if($data['params']['AssignShift']['shiftType'] == 'permanent'){

            $Tanggal = date('Y-m-d H:i:s',strtotime($data['params']['AssignShift']['dates']['start']));

            $shiftKerja = Shift_Kerja::select('Nama')->Where('ID_Shift', $Id_Shift)->first()->Nama;
            // dd($shiftKerja);
            foreach($employees as $item){
                $Kode_Karyawan = $item;
                $lembur = LemburDetail::whereDate('Tanggal_Lembur_Dari', $Tanggal)
                      ->orWhereDate('Tanggal_Lembur_Sampai', $Tanggal)
                      ->where('Kode_Karyawan', $Kode_Karyawan)
                      ->exists();
                // dd($lembur);

                $finalInsert = DB::table('HRIS_Shift_Per_Karyawan')->insert([
                    'Kode_Perusahaan' => $Kode_Perusahaan,
                    'Kode_Karyawan' => $Kode_Karyawan ,
                    'ID_Shift' => $Id_Shift,
                    'Periode' => $Tanggal
                ]);
                // whatsapp message to user
                  $userInsert = Karyawan::where('Kode_Karyawan', $Kode_Karyawan)->first() ?? null;
                $userInsertNoHp = Karyawan::where('Kode_Karyawan', $Kode_Karyawan)->first()->HP ?? null;

                if($userInsert && $finalInsert && $userInsertNoHp && $userInsertNoHp != '-'){

                    $TanggalKirim = date('D, d M Y ',strtotime($data['params']['AssignShift']['dates']['start']));

                    $pesan = [
                                "messaging_product" => "whatsapp",
                                "to" => $userInsertNoHp,
                                "type" => "template",
                                "template" => [
                                    "name" => "notif_ganti_shift",
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
                                                    "text" => $userInsert->Nama
                                                ],
                                                [
                                                    "type" => "text",
                                                    "text" => 'Permanen'
                                                ],

                                                [
                                                    "type" => "text",
                                                    "text" => $shiftKerja
                                                ],
                                                [
                                                    "type" => "text",
                                                    "text" => Auth()->user()->Nama
                                                ],
                                                [
                                                    "type" => "text",
                                                    "text" => $TanggalKirim.' s/d Seterusnya'
                                                ],




                                            ]
                                        ]
                                    ]
                                ]
                            ];

                        //    SendWhatsappMessage::dispatch($item, $pesan)->onQueue('default')->delay(now()->addSeconds(30));
                        //   SendWhatsappMessage::dispatch($item, $pesan)
                        //         ->onConnection('database')
                        //         ->onQueue('default')
                        //         ->delay(now()->addSeconds(10));
                        $response = Whatsapp::send_message($pesan);
                        Log::channel('waShiftLog')->warning('Pesan Error', [
                            "pesan" => $response
                        ]);


                }
            }





        }elseif($data['params']['AssignShift']['shiftType'] == 'temporary'){
            $Dates = $data['params']['AssignShift']['dates'];

            foreach($employees as $item){
                foreach($Dates as $date){
                   $finalInsert =   DB::table('HRIS_Shift_Sementara')->insert([
                                        'Kode_Perusahaan' => $Kode_Perusahaan,
                                        'Kode_Karyawan' => $item,
                                        'ID_Shift' => $Id_Shift,
                                        'Tanggal' => $date
                                    ]);
                }
                // whatsapp message to user
                $Kode_Karyawan = $item;
                $userInsert = Karyawan::where('Kode_Karyawan', $Kode_Karyawan)->first() ?? null;
                $shiftKerja = Shift_Kerja::select('Nama')->Where('ID_Shift', $Id_Shift)->first()->Nama;
                $userInsertNoHp = Karyawan::where('Kode_Karyawan', $Kode_Karyawan)->first()->HP ?? null;
                // dd($userInsertNoHp);
                // dd($userInsert);
                if($userInsert && $finalInsert && $userInsertNoHp && $userInsertNoHp != '-'){

                        if(count($Dates) > 1){
                            $temp = [];
                            foreach($Dates as $date){
                                $temp[] = date('d', strtotime($date));
                            }
                            $hari =  implode(',', $temp);
                            $bulanTahun = date('M Y', strtotime($Dates[0]));
                            $parseDates = $hari.' '.$bulanTahun;
                        }else{
                            foreach($Dates as $date){
                            $parseDates = date('D, d M Y', strtotime($date));
                            }
                        }
                        $pesan = [
                                    "messaging_product" => "whatsapp",
                                    "to" => $userInsertNoHp,
                                    "type" => "template",
                                    "template" => [
                                        "name" => "notif_ganti_shift",
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
                                                    "text" => $userInsert->Nama
                                                    ],
                                                    [
                                                        "type" => "text",
                                                        "text" => 'Sementara'
                                                    ],

                                                    [
                                                        "type" => "text",
                                                        "text" => $shiftKerja
                                                    ],
                                                    [
                                                        "type" => "text",
                                                        "text" => Auth()->user()->Nama
                                                    ],
                                                     [
                                                        "type" => "text",
                                                        "text" => $parseDates
                                                    ],




                                                    ]
                                                ]
                                            ]
                                        ]
                                    ];


                            $response = Whatsapp::send_message($pesan);
                            Log::channel('whatsapp_error')->warning('Pesan Error', [
                                "pesan" => $response
                            ]);
                    }


            }

        }
         DB::commit();
        Alert::success('Success', 'Shift Berhasil Disimpan!');
        return response()->json([
            'status' => 200,
            'Message' => 'Berhasil Menyimpan Pergantian Shift!'
        ]);

    }catch(\Throwable $e){
        DB::rollback();

        Log::channel('shiftLog')->error('Error simpan Lembur submit'. $e->getMessage());
        return response()->json([
            'status' => 500,
            'Message' => 'Terjadi Kesalahan Menyimpan, Silahkan Coba beberapa saat lagi! submit'
        ]);

    }



}


public function update(Request $request){
    DB::BeginTransaction();

    try{
        $employees = $request->params['employee']['Kode_Karyawan'];
        // dd($request->all());
        $Tanggal = date('Y-m-d H:i:s',strtotime($request->params['date']));
        $finalInsert = DB::table('HRIS_Shift_Sementara')->insert([
                'Kode_Perusahaan' => '001',
                'Kode_Karyawan' => $employees,
                'ID_Shift' => $request->params['shift'],
                'Tanggal' => $Tanggal
            ]);

        // wa
        $Kode_Karyawan = $employees;
        $userInsert = Karyawan::where('Kode_Karyawan', $Kode_Karyawan)->first() ?? null;
        $shiftKerja = Shift_Kerja::select('Nama')->Where('ID_Shift', $request->params['shift'])->first()->Nama;
        $userInsertNoHp = Karyawan::where('Kode_Karyawan', $Kode_Karyawan)->first()->HP ?? null;

        if($userInsert && $finalInsert && $userInsertNoHp && $userInsertNoHp != '-'){
        $TanggalKirim = date('D, d M Y ',strtotime($request->params['date']));


                    $pesan = [
                                "messaging_product" => "whatsapp",
                                "to" => $userInsertNoHp,
                                "type" => "template",
                                "template" => [
                                    "name" => "notif_ganti_shift",
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
                                                "text" => $userInsert->Nama
                                                ],
                                                [
                                                    "type" => "text",
                                                    "text" => 'Sementara'
                                                ],

                                                [
                                                    "type" => "text",
                                                    "text" => $shiftKerja
                                                ],
                                                [
                                                    "type" => "text",
                                                    "text" => Auth()->user()->Nama
                                                ],
                                                    [
                                                    "type" => "text",
                                                    "text" => $TanggalKirim
                                                ],




                                                ]
                                            ]
                                        ]
                                    ]
                                ];


                        $response = Whatsapp::send_message($pesan);
                        Log::channel('whatsapp_error')->warning('Pesan Error', [
                            "pesan" => $response
                        ]);

            }

        DB::commit();
        Alert::success('Success', 'Shift Berhasil Disimpan!');
        return response()->json([
        'status' => 200,
        'Message' => 'Berhasil Menyimpan Shift'
        ]);
    }catch(\Throwable $e){
        DB::rollback();
        Log::channel('shiftLog')->error('Terjadi kesalahan saat mengupdate shift update'. $e->getMessage());
        return response()->json([
            'status' => 500,
            'Message' => 'Terjadi kesalahan saat mengupdate shift update!'
        ]);

    }



}

}
