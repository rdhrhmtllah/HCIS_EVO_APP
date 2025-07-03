<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use RealRashid\SweetAlert\Facades\Alert;


class AbsensiController extends Controller
{
    //
    public function indexAbsensi(){
        $Kode_Perusahaan = '001';
        $user = Auth::user();
        $karyawan = Auth::user()->karyawan;
        $Tanggal = date('Y-m-d');

      $hashedUser = (object) [
        'Kode_Karyawan' => isset($user->Kode_Users) ? Crypt::encryptString($user->Kode_Users) : null,
        'Id_Users'   => isset($user->Id_Users) ? Hashids::connection('custom')->encode($user->Id_Users) : null,
        'UserID_Absen'   => isset($karyawan->UserID_Absen) ? Crypt::encryptString($karyawan->UserID_Absen) : null,
        'nama'       => $karyawan->Nama ?? null,
        'email'      => $user->email ?? null,
        'HP' => $karyawan->HP ?? null,


    ];



        // $contoh = DB::table('Transaksi_Absensi_Detail')->get();

        // $solveFile = collect($contoh)->map(function ($item){
        //     if(isset($item->filePath)){
        //         $item->filePath = Storage::disk('gcs')->temporaryUrl($item->filePath, now()->addMinutes(10)) ??  null;
        //     }
        //     return $item;
        // });
        $query = "exec sp_getShiftAbsen ?, ?, ?, ?";
           $Hari = date('N', strtotime($Tanggal));
              if ($Hari <= 6 ) {
            $Hari += 1 ;
            } elseif ($Hari == 7) {
                $Hari = 1;
            }
        $data = DB::select($query, [
            $Tanggal, $karyawan->Kode_Karyawan, $Kode_Perusahaan, $Hari
        ]);
        $dataFinal =  collect($data)->map(function ($item) {
            if (!empty($item->Kode_Karyawan)) {
                $item->Kode_Karyawan = Crypt::encryptString($item->Kode_Karyawan);
            }
            if (!empty($item->UserID_Absen)) {
                $item->UserID_Absen = Crypt::encryptString($item->UserID_Absen);
            }
            return $item;
        })->first();

        return inertia('absensiSales', ['userLogin' => $hashedUser, 'TodayData' => $dataFinal]);
    }

    public function getDataToday(){
        try{
            $Kode_Perusahaan = '001';
            $Tanggal = date('Y-m-d');
            $karyawan = Auth::user()->karyawan;
            $query = "exec sp_getShiftAbsen ?, ?, ?, ?";
            $Hari = date('N', strtotime($Tanggal));
                if ($Hari <= 6 ) {
                $Hari += 1 ;
                } elseif ($Hari == 7) {
                    $Hari = 1;
                }
            $data = DB::select($query, [
                $Tanggal, $karyawan->Kode_Karyawan, $Kode_Perusahaan, $Hari
            ]);

            $dataFinal =  collect($data)->map(function ($item) {
                if (!empty($item->Kode_Karyawan)) {
                    $item->Kode_Karyawan = Crypt::encryptString($item->Kode_Karyawan);
                }
                if (!empty($item->UserID_Absen)) {
                    $item->UserID_Absen = Crypt::encryptString($item->UserID_Absen);
                }
                return $item;
            })->first();


            if(!$dataFinal){
                $dataFinal = ['Tanggal' => $Tanggal];
            }
          return response()->json([
                'status' => 200,
                'message' => 'Berhasil Mengambil data',
                'data' => $dataFinal
            ]);
        }catch(\Throwable $e){
            Log::channel('absensi_error')->error('Gagal Menambil data'.$e->getMessage());
            return response()->json([
                'status' => 404,
                'message' => 'Terjadi kesalahan coba lagi beberapa saat..'
            ], 404);
        }
    }

   public function getUserShift(Request $request)
    {
        try {
            $Kode_Perusahaan = '001';
            $chooseDate = Carbon::parse($request->tanggal) ?? now()->toDateString();
            switch ($request->jenis) {
                case 'next':
                    $date = Carbon::parse($chooseDate)->copy()->addWeek()->format('Y-m-d');
                    break;
                case 'prev':
                    $date = Carbon::parse($chooseDate)->copy()->subWeek()->format('Y-m-d');
                    break;
                default:
                    $date = $chooseDate;
                    break;
            }



            $karyawan = Auth()->user()->Karyawan->Kode_Karyawan;

            $startOfWeek = Carbon::parse($date)->copy()->startOfWeek(Carbon::SUNDAY)->format('Y-m-d');
            $endOfWeek = Carbon::parse($date)->copy()->startOfWeek(Carbon::SATURDAY)->format('Y-m-d');

            $currentDate = $startOfWeek;
            $data = [];

                for ($i = 0; $i < 7; $i++) {
                    $Tanggal = date('Y-m-d', strtotime($currentDate));

                    $Hari = date('N', strtotime($Tanggal));
                    if ($Hari <= 6 ) {
                    $Hari += 1 ;
                    } elseif ($Hari == 7) {
                        $Hari = 1;
                    }


                    $query = "exec sp_getShiftAbsen ?, ?, ?, ?";
                    $result = DB::select($query, [
                        $Tanggal, $karyawan, $Kode_Perusahaan, $Hari
                    ]);

                   if ($result) {
                        $data[] = $result[0] ?? null;
                    } else {
                        $data[] = ['Tanggal' => $currentDate];
                    }


                $currentDate = date('Y-m-d', strtotime("+1 day", strtotime($currentDate)));
            }

            $dataFinal =  collect($data)->map(function ($item) {
                if (!empty($item->Kode_Karyawan)) {
                    $item->Kode_Karyawan = Crypt::encryptString($item->Kode_Karyawan);
                }
                if (!empty($item->UserID_Absen)) {
                    $item->UserID_Absen = Crypt::encryptString($item->UserID_Absen);
                }
                if (!empty($item->filePath_Masuk)) {
                    $item->filePath_Masuk = Storage::disk('gcs')->temporaryUrl($item->filePath_Masuk, now()->addMinutes(10)) ??  null;
                }
                if (!empty($item->filePath_Keluar)) {
                    $item->filePath_Keluar = Storage::disk('gcs')->temporaryUrl($item->filePath_Keluar, now()->addMinutes(10)) ??  null;
                }
                return $item;
            });


        return response()->json([
            'status' => 200,
            'message' => 'Berhasil Mendapatkan data',
            'data' => $dataFinal
        ]);
    } catch (\Exception $e) {
        Log::error('Error saat mengambil data shift absen: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'status' => 500,
            'message' => 'Gagal mengambil data. Silakan periksa koneksi Anda. ' . $e->getMessage()
        ], 500);
    }
}

    public function indexLembur(){
        return inertia('LemburFormModern');
    }

    public function addLembur(Request $request){
        $entries = $request->input('data');

        foreach ($entries as $entry) {
            $validator = Validator::make($entry, [
                'alasan' => 'required|string',
                'start' => 'required|date_format:H:i',
                'end' => 'required|date_format:H:i|after:start',
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Lembur::create([
            //     'alasan' => $entry['alasan'],
            //     'start' => $entry['start'],
            //     'end' => $entry['end'],
            //     'date' => $entry['date'],
            // ]);
        }

        return response()->json($entries);
    }


    public function submitAbsen(Request $request)
    {
        $image = $request->input('image');
        $randomHash = $request->input('watermark_hash');
        $imageHash = $request->input('image_hash');

        $calculatedHash = hash('sha256', $image . '|' . $randomHash);

        if ($calculatedHash !== $imageHash) {
            return response()->json(['error' => 'Hash tidak valid. Foto mungkin dimanipulasi.'], 400);
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120',

        ]);

        if (!$request->hasFile('photo')) {
            return response()->json([
                'status' => 400,
                'message' => 'Tidak ada foto yang diupload.'
            ], 400);
        }
        $lang = $request->input('location_longitude');
        $lat = $request->input('location_latitude');
        $accuracy = $request->input('accuracy');
        if(!$lang || !$lat || !$accuracy){
            return response()->json([
                'status' => 400,
                'message' => 'Harap Nyalakan Lokasi Anda!'
            ], 400);
        }
        $file = $request->file('photo');
        $photoPath = null; // Inisialisasi variabel untuk path foto di GCS

        try {

            // Path di GCS: absensi/tahun/bulan/uuid.ext
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $folderPath = 'absensi/' . date('Y/m');
            $photoPath = $folderPath . '/' . $fileName;

            // Menggunakan metode putFileAs() yang lebih disarankan untuk upload file di Laravel
            // Ini akan secara otomatis menangani streaming file ke GCS
            Storage::disk('gcs')->putFileAs($folderPath, $file, $fileName);


            DB::beginTransaction();
            try {
                $format_tanggal_sekarang = date('m') . '-' . date('y');

                // no Transaksi
                $string = "SELECT TOP 1 ";
                $string .= "RIGHT(No_Transaksi, 4) AS angka ";
                $string .= "FROM Transaksi_Absensi ";
                $string .= "WHERE LEFT(No_Transaksi, 8) = 'AB" . $format_tanggal_sekarang . "-' ";
                $string .= "ORDER BY RIGHT(No_Transaksi, 4) DESC";


                $check_last_faktur = DB::select($string);
                if (count($check_last_faktur) == 0) {
                    $angka_terakhir = 1;
                } else {
                    $angka_terakhir = (int)$check_last_faktur[0]->angka + 1;
                }
                $init = "AB". date('m-y');
                $no_faktur = $init . '-' . sprintf('%04d', $angka_terakhir);

                $Tanggal = date('Y-m-d');
                $Jam = date('H:i:s');

                $UserID = Hashids::connection('custom')->decode($request->UserID);
                $UserID_Absen = (int) Crypt::decryptString($request->UserID_Absen);
                $Kode_Karyawan = Crypt::decryptString($request->Kode_Karyawan);

                if(!$UserID || !$UserID_Absen || !$Kode_Karyawan){
                    return response()->json([
                    'status' => 500,
                    'message' => 'Todal ada Data User'
                    ], 400);
                }
                $tanggal_absen = $request->frontend_timestamp;
                DB::table('Transaksi_Absensi')->insert([
                    'Kode_Perusahaan' => '001',
                    'No_Transaksi' => $no_faktur,
                    'Tanggal' => $Tanggal,
                    'Jam' => $Jam,
                    'UserID' => $UserID[0]
                ]);

                DB::table('Transaksi_Absensi_Detail')->insert([
                    'Kode_Perusahaan' => '001',
                    'No_Transaksi' => $no_faktur,
                    'Kode_Karyawan' => $Kode_Karyawan,
                    'Jenis' => $request->Jenis,
                    'Tanggal_Absensi' => $tanggal_absen,
                    'Alasan' => preg_replace('/\s+/', ' ', $request->alasan),
                    'filePath' => $photoPath,
                    'image_hash' => $imageHash,
                    'watermark_hash' => $randomHash,
                    'lat' => $lat,
                    'lang' => $lang,
                    'accuracy' => $accuracy
                ]);

                DB::table('CHECKINOUT ')->insert([
                    'USERID' => $UserID_Absen,
                    'CHECKTIME' => $tanggal_absen,
                    'VERIFYCODE' => '255',
                    'SENSORID' => '180',
                    'sn' => '1',
                ]);




                DB::commit();
                Alert::success('Success', 'Absen Berhasil Disimpan!');
                return response()->json([
                    'status' => 200,
                    'message' => 'Absensi berhasil disimpan!',
                    'photo_url' => $photoPath
                ]);

            } catch (\Exception $dbException) {
                DB::rollback();

                Log::error('Error menyimpan data absensi ke DB: ' . $dbException->getMessage(), [
                    'file' => $dbException->getFile(),
                    'line' => $dbException->getLine(),
                ]);

                // Jika foto sudah terupload tapi data DB gagal, kita perlu menghapus fotonya
                if ($photoPath && Storage::disk('gcs')->exists($photoPath)) {
                    Storage::disk('gcs')->delete($photoPath);
                    Log::warning('Foto GCS dihapus karena kegagalan DB: ' . $photoPath);
                }

                return response()->json([
                    'status' => 500,
                    'message' => 'Terjadi kesalahan saat menyimpan data absensi. Silakan coba lagi.'. $dbException->getMessage()
                ], 500);
            }

        } catch (\Exception $e) {
            // Ini akan menangkap error dari proses upload ke GCS (atau error lain sebelum DB transaction)
            Log::error('Error saat upload foto absensi ke GCS: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(), // Tambahkan trace untuk debugging lebih lanjut
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Gagal mengupload foto absensi. Silakan periksa kredensial GCS atau koneksi Anda.'
            ], 500);
        }
    }
}
