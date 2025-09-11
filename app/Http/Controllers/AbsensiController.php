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
use Illuminate\Support\Facades\Validator;
use App\Helpers\Whatsapp;

use InvalidArgumentException;


class AbsensiController extends Controller
{
    //
    public function indexAbsensi(){
        try{


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
                if(!empty($item->ID_Shift)){
                    $item->Jam_Mulai_Absen_Masuk = DB::table('HRIS_Shift_Kerja as a')
                        ->select("Jam_Mulai_Absen_Masuk")
                        ->join("HRIS_Shift_Kerja_Detail as b", 'a.ID_Shift', '=', 'b.ID_Shift')
                        ->join("HRIS_Waktu_Kerja as c", 'c.ID_Waktu_Kerja', '=','c.ID_Waktu_Kerja')
                        ->where("a.ID_Shift", $item->ID_Shift)
                        ->first()->Jam_Mulai_Absen_Masuk;
                }
                if(!empty($item->ID_Shift)){
                    $item->Jam_Selesai_Absen_Masuk = DB::table('HRIS_Shift_Kerja as a')
                        ->select("Jam_Selesai_Absen_Masuk")
                        ->join("HRIS_Shift_Kerja_Detail as b", 'a.ID_Shift', '=', 'b.ID_Shift')
                        ->join("HRIS_Waktu_Kerja as c", 'c.ID_Waktu_Kerja', '=','c.ID_Waktu_Kerja')
                        ->where("a.ID_Shift", $item->ID_Shift)
                        ->first()->Jam_Selesai_Absen_Masuk;
                }
                if(!empty($item->ID_Shift)){
                    $item->Jam_Mulai_Absen_Keluar = DB::table('HRIS_Shift_Kerja as a')
                        ->select("Jam_Mulai_Absen_Keluar")
                        ->join("HRIS_Shift_Kerja_Detail as b", 'a.ID_Shift', '=', 'b.ID_Shift')
                        ->join("HRIS_Waktu_Kerja as c", 'c.ID_Waktu_Kerja', '=','c.ID_Waktu_Kerja')
                        ->where("a.ID_Shift", $item->ID_Shift)
                        ->first()->Jam_Mulai_Absen_Keluar;
                }
                if(!empty($item->Jam_selesai_absen)){
                    $item->Jam_Selesai_Absen_Keluar = $item->Jam_selesai_absen;
                }
                return $item;
            })->first();
            // dd($hashedUser);
            return inertia('absensiSales', ['userLogin' => $hashedUser, 'TodayData' => $dataFinal]);

        }catch (\Throwable $e) {
            Log::channel('absensiLog')->error('Gagal menampilkan page absensi: ' . $e->getMessage());
            Alert::error('error', 'Terjadi Error saat menampilkan page absensi!');
            return back();
        }
    }

    public function confirmIzin(Request $request)
    {
        // dd($request->all());
        try {
            DB::beginTransaction();
            $No_Transaksi = $request->No_Transaksi;
            $requester = Crypt::decryptString($request->requester);
            $approver = Crypt::decryptString($request->approver);
            $tipeConfirm = $request->tipeConfirm;

            $query = DB::table('HRIS_Approval_Request as a')
                ->join('HRIS_Approval_Flow as b', 'a.Flow_Id', '=', 'b.id')
                ->whereNull("b.Status")
                ->where('a.No_Transaksi', $No_Transaksi)
                ->where('a.Kode_Karyawan', $requester)
                ->where('b.Kode_Karyawan', $approver);
            // dd($query);
            if (!$query->exists()) {
                DB::rollBack();
                Log::channel('izinLog')->error('Data transaksi izin tidak ditemukan atau tidak sesuai');

                return response()->json([
                    'status' => 404,
                    'message' => 'Data transaksi izin tidak ditemukan atau tidak sesuai.'
                ], 404);
            }

            $updateValue = ($tipeConfirm == 'Terima') ? 'Y' : 'T';
            $successMessage = ($tipeConfirm == 'Terima') ? 'Berhasil Menerima Izin!' : 'Berhasil Menolak Izin!';

            $insertRequest = $query->update(['Flag_Approval' => $updateValue]);


            if($insertRequest){
                    // dd($updateValue);
                    if($updateValue == 'T'){
                        $prefix = substr($No_Transaksi, 0, 2);
                        if($prefix == 'IS'){
                            $Izin = DB::table('Transaksi_Sakit_Izin_Detail')->where('No_Transaksi', $No_Transaksi);
                            $Izin->update(['Flag_Approval' => 'T']);

                            $spekIzin = DB::table('Transaksi_Sakit_Izin_Detail')->select("Kode_Karyawan","Jenis", "Tanggal_Sakit_Izin_Dari as dari", "Tanggal_Sakit_Izin_Sampai as sampai")->where('No_Transaksi', $No_Transaksi)->first();

                            $user = DB::table('Karyawan as a')
                            ->join('Transaksi_Sakit_Izin_Detail as b', 'a.Kode_Karyawan', '=', 'b.Kode_Karyawan')
                            ->where('b.No_Transaksi', $No_Transaksi)->first();
                            $userInsertNoHp = $user->HP;
                        }else if($prefix == 'TP'){
                            $Izin = DB::table('Transaksi_Terlambat_Pulang_Cepat_Detail')->where('No_Transaksi', $No_Transaksi);
                            $Izin->update(['Flag_Approval' => 'T']);

                            $spekIzin = DB::table('Transaksi_Terlambat_Pulang_Cepat_Detail')->select("Kode_Karyawan","Jenis", "Tanggal_Masuk_Pulang as dari", DB::raw('null as sampai') )->where('No_Transaksi', $No_Transaksi)->first();

                            $user = DB::table('Karyawan as a')
                            ->join('Transaksi_Terlambat_Pulang_Cepat_Detail as b', 'a.Kode_Karyawan', '=', 'b.Kode_Karyawan')
                            ->where('b.No_Transaksi', $No_Transaksi)->first();
                            $userInsertNoHp = $user->HP;
                        }else if($prefix == 'CT'){
                            $Izin = DB::table('Transaksi_Cuti_Detail')->where('No_Transaksi', $No_Transaksi);
                            $Izin->update(['Flag_Approval' => 'T']);

                            $spekIzin = DB::table('Transaksi_Cuti_Detail')->select("Kode_Karyawan","Jenis", "Tanggal_Cuti_Dari as dari", "Tanggal_Cuti_Sampai as sampai")->where('No_Transaksi', $No_Transaksi)->first();

                            $user = DB::table('Karyawan as a')
                            ->join('Transaksi_Cuti_Detail as b', 'a.Kode_Karyawan', '=', 'b.Kode_Karyawan')
                            ->where('b.No_Transaksi', $No_Transaksi)->first();
                            $userInsertNoHp = $user->HP;
                        }
                        // dd($user);

                        if($user != null && $userInsertNoHp != '-' && $userInsertNoHp != ' '){
                            // whatsapp message to user
                            $pesan = [
                                            "messaging_product" => "whatsapp",
                                            "to" => $userInsertNoHp,
                                            "type" => "template",
                                            "template" => [
                                                "name" => "confirm_izin_tolak",
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
                                                                "text" => $user->Nama
                                                            ],
                                                            [
                                                                "type" => "text",
                                                                "text" => $No_Transaksi. ' Pengajuan ' .$this->parseIzin($spekIzin->Jenis)
                                                            ],
                                                            [
                                                                "type" => "text",
                                                                "text" => Auth::user()->karyawan->Nama
                                                            ]
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ];


                                $response = Whatsapp::send_message($pesan);
                                Log::channel('waConfirmIzin')->warning('Pesan Error', [
                                    "pesan" => $response
                                ]);





                        }

                    }else if($updateValue == 'Y'){


                        // dd("hidup jokowi");
                        $flowSekarang =  DB::table('HRIS_Approval_Request as a')
                        ->join('HRIS_Approval_Flow as b', 'a.Flow_Id', '=', 'b.id')
                        ->whereNull("b.Status")
                        ->where('a.No_Transaksi', $No_Transaksi)
                        ->where('a.Kode_Karyawan', $requester)
                        ->where('b.Kode_Karyawan', $approver)->first();
                        // dd($flowSekarang);

                        $angkaFlowSekarang =  $flowSekarang ? $flowSekarang->order_flow : null;
                        if(!$angkaFlowSekarang){
                            Log::channel('izinLog')->error('Gagal Meyimpan keran angka flow tidak ada');

                            return response()->json([
                                'status' => 500,
                                'message' => 'Gagal Meyimpan keran angka flow tidak ada'
                            ], 500);
                        }
                        if($angkaFlowSekarang){






                            $maxAngkaOrder = DB::table('HRIS_Approval_Request as a')
                            ->join('HRIS_Approval_Flow as b', 'a.Flow_Id', '=', 'b.id')
                            ->whereNull("b.Status")
                            ->where('a.No_Transaksi', $No_Transaksi)
                            ->where('a.Kode_Karyawan', $requester)
                            ->max('order_flow');

                            if($angkaFlowSekarang == $maxAngkaOrder){
                                $prefix = substr($No_Transaksi, 0, 2);
                                if($prefix == 'IS'){
                                    $Izin = DB::table('Transaksi_Sakit_Izin_Detail')->where('No_Transaksi', $No_Transaksi);
                                    $Izin->update(['Flag_Approval' => 'Y']);

                                    $user = DB::table('Karyawan as a')
                                    ->join('Transaksi_Sakit_Izin_Detail as b', 'a.Kode_Karyawan', '=', 'b.Kode_Karyawan')
                                    ->where('b.No_Transaksi', $No_Transaksi)->first();
                                    $userInsertNoHp = $user->HP;
                                }else if($prefix == 'TP'){
                                    $Izin = DB::table('Transaksi_Terlambat_Pulang_Cepat_Detail')->where('No_Transaksi', $No_Transaksi);
                                    $Izin->update(['Flag_Approval' => 'Y']);

                                    $user = DB::table('Karyawan as a')
                                    ->join('Transaksi_Terlambat_Pulang_Cepat_Detail as b', 'a.Kode_Karyawan', '=', 'b.Kode_Karyawan')
                                    ->where('b.No_Transaksi', $No_Transaksi)->first();
                                    $userInsertNoHp = $user->HP;
                                }else if($prefix == 'CT'){
                                    $Izin = DB::table('Transaksi_Cuti_Detail')->where('No_Transaksi', $No_Transaksi)->first();
                                    $tanggalHutang = Carbon::parse($Izin->Tanggal_Cuti_Dari);
                                    $JumlahCuti = Karyawan::where("Kode_Karyawan", $Izin->Kode_Karyawan)->first()->sisa_cuti();
                                    $JumlahHariIzin =  Carbon::parse($Izin->Tanggal_Cuti_Dari)->diffInDays(Carbon::parse($Izin->Tanggal_Cuti_Sampai))+1;
                                    if($JumlahHariIzin > $JumlahCuti){
                                        $Cutis = DB::table('HRIS_Buku_Cuti')
                                                    ->where("sisa_cuti", '>', 0)
                                                    ->where("tanggal_expired",">=", Carbon::now()->toDateString())
                                                    ->get();

                                        if($Cutis){
                                            foreach($Cutis as $Cuti){
                                                DB::table("HRIS_Buku_Cuti")->where("Id_Buku_Cuti", $Cuti->Id_Buku_Cuti)->update(['sisa_cuti' => 0]);
                                            }
                                        }

                                        $hutang = $JumlahHariIzin - $JumlahCuti;
                                        if($hutang > 0){
                                            DB::table('HRIS_Buku_Cuti')->insert([
                                                'Kode_Karyawan' => $Izin->Kode_Karyawan,
                                                'tipe_cuti' => 'Tahunan',
                                                'tanggal_diberikan' => Carbon::now()->toDateString(),
                                                'tanggal_expired' => Carbon::now()->addYears(20)->toDateString(),
                                                'jumlah_awal_cuti' => -$hutang,
                                                'sisa_cuti' => -$hutang,
                                                'notes' => "Hutang dari pengajuan {$tanggalHutang}, Transaksi {$Izin->No_Transaksi}"
                                            ]);
                                        }

                                    }else{
                                        $Cutis = DB::table('HRIS_Buku_Cuti')
                                                    ->where("sisa_cuti", '>', 0)
                                                    ->where("tanggal_expired", ">=" ,Carbon::now()->toDateString())
                                                    ->orderBy("tanggal_expired", "asc")->get();

                                        if(!$Cutis){
                                            DB::rollBack();
                                            Log::channel('izinLog')->error("Gagal menyimpan data: Tidak ada saldo cuti tersedia untuk user {$Izin->Kode_Karyawan}" );

                                            return response()->json([
                                                'status' => 500,
                                                'message' => "Gagal menyimpan data: Tidak ada saldo cuti tersedia untuk user {$Izin->Kode_Karyawan}"
                                            ], 500);
                                        }

                                        foreach($Cutis as $Cuti){
                                            if($JumlahHariIzin <= 0){
                                                break;
                                            }

                                            $deductible = min($JumlahHariIzin, $Cuti->sisa_cuti); //Nilai yang bisa di potong dari cuti ini

                                            $newRemainingDays = $Cuti->sisa_cuti - $deductible;
                                            DB::table('HRIS_Buku_Cuti')
                                                ->where('Id_Buku_Cuti', $Cuti->Id_Buku_Cuti)
                                                ->update(['sisa_cuti' => $newRemainingDays]);
                                            }

                                            $JumlahHariIzin -= $deductible;


                                    }
                                    DB::table('Transaksi_Cuti_Detail')->where('No_Transaksi', $No_Transaksi)->update(['Flag_Approval' => 'Y']);

                                    $user = DB::table('Karyawan as a')
                                    ->join('Transaksi_Cuti_Detail as b', 'a.Kode_Karyawan', '=', 'b.Kode_Karyawan')
                                    ->where('b.No_Transaksi', $No_Transaksi)->first();
                                    $userInsertNoHp = $user->HP;
                                }

                                if($user != null && $userInsertNoHp != '-' && $userInsertNoHp != ' '){
                                    // dd($user);

                                    // whatsapp message to user
                                    $pesan = [
                                                    "messaging_product" => "whatsapp",
                                                    "to" => $userInsertNoHp,
                                                    "type" => "template",
                                                    "template" => [
                                                        "name" => "confirm_izin_diterima",
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
                                                                        "text" => $user->Nama
                                                                    ],
                                                                    [
                                                                        "type" => "text",
                                                                        "text" => $this->parseIzin($Izin->first()->Jenis).' anda No. '.$No_Transaksi
                                                                    ]
                                                                ]
                                                            ]
                                                        ]
                                                    ]
                                                ];


                                        $response = Whatsapp::send_message($pesan);
                                        Log::channel('waIzinLog')->warning('Pesan Error', [
                                            "pesan" => $response
                                        ]);
                                }





                            }else{
                                        $prefix = substr($No_Transaksi, 0, 2);
                                if($prefix == 'IS'){


                                    $spekIzin = DB::table('Transaksi_Sakit_Izin_Detail')->select("Kode_Karyawan","Jenis", "Tanggal_Sakit_Izin_Dari as dari",DB::raw('null as Jam') ,"Tanggal_Sakit_Izin_Sampai as sampai")->where('No_Transaksi', $No_Transaksi)->first();

                                    $user = DB::table('Karyawan as a')
                                    ->join('Transaksi_Sakit_Izin_Detail as b', 'a.Kode_Karyawan', '=', 'b.Kode_Karyawan')
                                    ->where('b.No_Transaksi', $No_Transaksi)->first();
                                    $userInsertNoHp = $user->HP;
                                }else if($prefix == 'TP'){


                                    $spekIzin = DB::table('Transaksi_Terlambat_Pulang_Cepat_Detail')->select("Kode_Karyawan","Jenis", "Tanggal_Masuk_Pulang as dari", "Jam", DB::raw('null as sampai') )->where('No_Transaksi', $No_Transaksi)->first();

                                    $user = DB::table('Karyawan as a')
                                    ->join('Transaksi_Terlambat_Pulang_Cepat_Detail as b', 'a.Kode_Karyawan', '=', 'b.Kode_Karyawan')
                                    ->where('b.No_Transaksi', $No_Transaksi)->first();
                                    $userInsertNoHp = $user->HP;
                                }else if($prefix == 'CT'){


                                    $spekIzin = DB::table('Transaksi_Cuti_Detail')->select("Kode_Karyawan","Jenis", "Tanggal_Cuti_Dari as dari", DB::raw('null as Jam'),"Tanggal_Cuti_Sampai as sampai")->where('No_Transaksi', $No_Transaksi)->first();

                                    $user = DB::table('Karyawan as a')
                                    ->join('Transaksi_Cuti_Detail as b', 'a.Kode_Karyawan', '=', 'b.Kode_Karyawan')
                                    ->where('b.No_Transaksi', $No_Transaksi)->first();
                                    $userInsertNoHp = $user->HP;
                                }


                                $nextFlow =  DB::table('HRIS_Approval_Request as a')
                                ->select("b.Kode_Karyawan")
                                ->join('HRIS_Approval_Flow as b', 'a.Flow_Id', '=', 'b.id')
                                ->whereNull("b.Status")
                                ->where('a.No_Transaksi', $No_Transaksi)
                                ->where('a.Kode_Karyawan', $requester)
                                ->where("b.order_flow", $flowSekarang->order_flow + 1)->first();

                                $nextApprover = DB::table('Karyawan')
                                                ->where("Kode_Karyawan", $nextFlow->Kode_Karyawan)
                                                ->first();
                                // dd($nextApprover);
                                    if (!$nextApprover || ($nextApprover->HP == '' || $nextApprover->HP == ' ' || $nextApprover->HP == '-')) {
                                        Log::channel('waIzinPage')->warning('Pesan Error', [
                                            "pesan" => "Karyawan {$nextApprover->Nama} Tidak ada Nomor Hp untuk noitifikasi Transaksi {$No_Transaksi}"
                                        ]);
                                    }else{
                                    $TanggalWa = $spekIzin->Jam != null ? $this->formatTanggal($spekIzin->dari, null, $spekIzin->Jam) : $this->formatTanggal($spekIzin->dari, $spekIzin->sampai);
                                    $namaRequest = DB::table('Karyawan')->where("Kode_Karyawan", $spekIzin->Kode_Karyawan)->first();
                                    // dd($nextApprover);
                                    // whatsapp message to user
                                    $pesan = [
                                                    "messaging_product" => "whatsapp",
                                                    "to" => $nextApprover->HP,
                                                    "type" => "template",
                                                    "template" => [
                                                        "name" => "notif_approver_hcis",
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
                                                                        "text" => $nextApprover->Nama
                                                                    ],
                                                                    [
                                                                        "type" => "text",
                                                                        "text" => $No_Transaksi
                                                                    ],
                                                                    [
                                                                        "type" => "text",
                                                                        "text" => $this->parseIzin($spekIzin->Jenis)
                                                                    ],
                                                                    [
                                                                        "type" => "text",
                                                                        "text" => $TanggalWa
                                                                    ],
                                                                    [
                                                                        "type" => "text",
                                                                        "text" => $namaRequest->Nama
                                                                    ]
                                                                ]
                                                            ]
                                                        ]
                                                    ]
                                                ];


                                        $response = Whatsapp::send_message($pesan);
                                        Log::channel('waIzinLog')->warning('Pesan Error', [
                                            "pesan" => $response
                                        ]);


                                        if($response){
                                            DB::table('HRIS_Approval_Request as a')
                                                ->join('HRIS_Approval_Flow as b', 'a.Flow_Id', '=', 'b.id')
                                                ->whereNull("b.Status")
                                                ->where("a.No_Transaksi", $No_Transaksi)
                                                ->where("b.Kode_Karyawan", $nextApprover->Kode_Karyawan)
                                                ->update(['a.angka_wa' => DB::raw('COALESCE(angka_wa, 0) + 1')]);
                                        }

                                    }



                            }
                        }
                    }

            }


            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => $successMessage
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::channel('izinLog')->error('Gagal menyimpan data confirmIzin: ' . $e->getMessage() . ' - Transaksi: ' . ($No_Transaksi ?? 'N/A'));

            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan internal server, silakan coba lagi beberapa saat. Detail: '.$e->getMessage()
            ], 500);
        }
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
            Log::channel('absensiLog')->error('Gagal Menambil data getDataToday'.$e->getMessage());
            return response()->json([
                'status' => 404,
                'message' => 'Terjadi kesalahan coba lagi beberapa saat..'
            ], 404);
        }
    }

    public function getDataIzinAdmin(){
        try{
            $Kode_Karyawan = Auth::user()->karyawan->Kode_Karyawan;
            $query ="
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
							(select Nama from Karyawan z where z.Kode_Karyawan = ct.Kode_Karyawan) as nama_requester,
                            ISNULL(
                                (
                                SELECT
                                    TOP 1 af.order_flow
                                FROM
                                    HRIS_Approval_Request ar
                                    JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                WHERE
                                    ar.No_Transaksi = ct.No_Transaksi
                                    AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NOT NULL
                                    --AND af.Status is NULL
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
                                    and a.Kode_Karyawan_Requester = b.Kode_Karyawan
                                    --and a.Status is NULL
                                order by
                                    a.order_flow desc
                                ),
                                0
                            ) as MaxFlow,
                            CASE
                                WHEN ct.Flag_Approval = 'Y' THEN 'Selesai'
                                WHEN ct.Flag_Approval = 'T' THEN 'Ditolak'
                                WHEN ct.Flag_Approval = 'P' THEN 'Ditolak'
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    --AND af.Status is NULL
                                    ORDER BY
                                    af.order_flow
                                ),
                                'NO'
                                )
                            END as status,
                            CASE
                                WHEN ct.Flag_Approval = 'Y' THEN 'DiSetujui'
                                WHEN ct.Flag_Approval = 'P' THEN 'Sistem'
                                WHEN ct.Flag_Approval = 'T' THEN (
                                select
                                    top 1 af.Kode_Karyawan
                                FROM
                                    HRIS_Approval_Request ar
                                    JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                WHERE
                                    ar.No_Transaksi = ct.No_Transaksi

									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval = 'T'
                                    --AND af.Status is NULL
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    --AND af.Status is NULL
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
								and a.Kode_Karyawan = b.Kode_Karyawan_Requester
                                --and b.Status is NULL
                            ) AS STATUSLIST
                            FROM
                            Karyawan AS k
                            INNER JOIN Transaksi_Cuti_Detail AS ct ON k.Kode_Karyawan = ct.Kode_Karyawan
                            INNER JOIN Transaksi_Cuti AS cti ON ct.No_Transaksi = cti.No_Transaksi
                            WHERE
                             cti.Status is null
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
							(select Nama from Karyawan z where z.Kode_Karyawan = tsi.Kode_Karyawan) as nama_requester,
                            ISNULL(
                                (
                                SELECT
                                    TOP 1 af.order_flow
                                FROM
                                    HRIS_Approval_Request ar
                                    JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                WHERE
                                    ar.No_Transaksi = tsi.No_Transaksi
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND af.Status is null
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
									and a.Kode_Karyawan_Requester = b.Kode_Karyawan
                                    --and a.Status is Null
                                order by
                                    a.order_flow desc
                                ),
                                0
                            ) as MaxFlow,
                            CASE
                                WHEN tsi.Flag_Approval = 'Y' THEN 'Selesai'
                                WHEN tsi.Flag_Approval = 'T' THEN 'Ditolak'
                                WHEN tsi.Flag_Approval = 'P' THEN 'Ditolak'
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    --AND af.Status is NULL
                                    ORDER BY
                                    af.order_flow
                                ),
                                'NO'
                                )
                            END as status,
                            CASE
                                WHEN tsi.Flag_Approval = 'Y' THEN 'DiSetujui'
                                WHEN tsi.Flag_Approval = 'P' THEN 'Sistem'
                                WHEN tsi.Flag_Approval = 'T' THEN (
                                select
                                    top 1 af.Kode_Karyawan
                                FROM
                                    HRIS_Approval_Request ar
                                    JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                WHERE
                                    ar.No_Transaksi = tsi.No_Transaksi

									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval = 'T'
                                    --AND af.Status is NULL
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    --AND af.Status is NULL
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
								and a.Kode_Karyawan = b.Kode_Karyawan_Requester
                               -- and b.Status is NULL
                            ) AS STATUSLIST
                            FROM
                            Karyawan AS k
                            INNER JOIN Transaksi_Sakit_Izin_Detail AS tsi ON k.Kode_Karyawan = tsi.Kode_Karyawan
                            INNER JOIN Transaksi_Sakit_Izin AS tsip ON tsi.No_Transaksi = tsip.No_Transaksi
                            WHERE
                             tsip.Status is null
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
							(select Nama from Karyawan z where z.Kode_Karyawan = ttp.Kode_Karyawan) as nama_requester,

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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NOT NULL
                                    --AND af.Status is NULL
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
									and a.Kode_Karyawan_Requester = b.Kode_Karyawan
                                    --and a.Status is NULL
                                order by
                                    a.order_flow desc
                                ),
                                0
                            ) as MaxFlow,
                            CASE
                                WHEN ttp.Flag_Approval = 'Y' THEN 'Selesai'
                                WHEN ttp.Flag_Approval = 'T' THEN 'Ditolak'
                                WHEN ttp.Flag_Approval = 'P' THEN 'Ditolak'
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    --AND af.Status is NULL
                                    ORDER BY
                                    af.order_flow
                                ),
                                'NO'
                                )
                            END as status,
                            CASE
                                WHEN ttp.Flag_Approval = 'Y' THEN 'DiSetujui'
                                WHEN ttp.Flag_Approval = 'P' THEN 'Sistem'
                                WHEN ttp.Flag_Approval = 'T' THEN (
                                select
                                    top 1 af.Kode_Karyawan
                                FROM
                                    HRIS_Approval_Request ar
                                    JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                WHERE
                                    ar.No_Transaksi = ttp.No_Transaksi

									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval = 'T'
                                    --AND af.Status is NULL
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    ---AND af.Status is NULL
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
								and a.Kode_Karyawan = b.Kode_Karyawan_Requester
                                --and b.Status is NULL
                            ) AS STATUSLIST
                            FROM
                            Karyawan AS k
                            INNER JOIN Transaksi_Terlambat_Pulang_Cepat_Detail AS ttp ON k.Kode_Karyawan = ttp.Kode_Karyawan
                            INNER JOIN Transaksi_Terlambat_Pulang_Cepat AS ttpp ON ttp.No_Transaksi = ttpp.No_Transaksi
                            WHERE
                           ttpp.Status is null
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
            $result = DB::select($query);
            $dataFinal =  collect($result)->map(function ($item) {
                if (!empty($item->Kode_Karyawan)) {
                    $item->Kode_Karyawan = Crypt::encryptString($item->Kode_Karyawan);

                }

                return $item;
            });



            $dataCuti= (object) [
                'Sisa_Cuti' => (int) Auth::user()->karyawan->sisa_cuti(),
                'Hutang_Cuti'=>(int) Auth::user()->karyawan->hutang_cuti()

            ];
            // dd($dataCuti);
            $TanggalCutiSudahDipakai = DB::table('Transaksi_Cuti_Detail as a')
                                        ->join("Transaksi_Cuti as b", "a.No_Transaksi", '=', 'b.No_Transaksi')
                                        ->whereNull("b.Status")
                                        ->where("a.Kode_Karyawan", $Kode_Karyawan)
                                        ->where(function($query) {
                                            $query->where("a.Flag_Approval", "<>", "T")
                                            ->orWhereNull('a.Flag_Approval');

                                        })
                                        ->get();


            $TanggalSakitIzinSudahDipakai =  DB::table('Transaksi_Sakit_Izin_Detail as a')
                                    ->join("Transaksi_Sakit_Izin as b", "a.No_Transaksi", '=', 'b.No_Transaksi')
                                    ->whereNull("b.Status")
                                    ->where("Kode_Karyawan", $Kode_Karyawan)
                                    ->where(function($query) {
                                            $query->where("a.Flag_Approval", "<>", "T")
                                            ->orWhereNull('a.Flag_Approval');

                                        })
                                    ->get();
            // dd($TanggalSakitIzinSudahDipakai);
            $TanggalPulangCepatSudahDipakai =  DB::table('Transaksi_Terlambat_Pulang_Cepat_Detail as a')
                                    ->join("Transaksi_Terlambat_Pulang_Cepat as b", "a.No_Transaksi", '=', 'b.No_Transaksi')
                                    ->whereNull("b.Status")
                                    ->where("Kode_Karyawan", $Kode_Karyawan)
                                    ->where(function($query) {
                                        $query->where("a.Flag_Approval", "<>", "T")
                                        ->orWhereNull('a.Flag_Approval');

                                    })
                                    ->get();


            $HariLibur = collect(DB::table('HRIS_Hari_Libur')->get())->map(function ($item){
                if(!empty($item->Tanggal)){
                    $item->date = Carbon::parse($item->Tanggal)->format('Y-m-d');
                }
                if(!empty($item->Nama_Hari_Libur)){
                    // Assuming Nama_Hari_Libur is also a date string that needs formatting.
                    // If it's not a date, this line will cause an error.
                    $item->description = $item->Nama_Hari_Libur;
                }
                return $item; // It is important to return the modified item
            });

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil mengambil data',
                'data' => $dataFinal,
                'dataCuti' => $dataCuti,
                'TanggalCuti' => $TanggalCutiSudahDipakai,
                'TanggalSakitIzin' => $TanggalSakitIzinSudahDipakai,
                'TanggalPulangTerlambat' => $TanggalPulangCepatSudahDipakai,
                'HariLibur' => $HariLibur
            ]);
        }catch(\Throwable $e){
            Log::channel('izinLog')->error('Gagal Menambil data getDataIzin'.$e->getMessage());
            return response()->json([
                'status' => 404,
                'message' => 'Terjadi kesalahan coba lagi beberapa saat..'
            ], 404);
        }
    }

    public function getDataIzin(){
        try{
            $Kode_Karyawan = Auth::user()->karyawan->Kode_Karyawan;
            $query ="
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
                                    AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NOT NULL
                                    --AND af.Status is NULL
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
                                    and a.Kode_Karyawan_Requester = b.Kode_Karyawan
                                    --and a.Status is NULL
                                order by
                                    a.order_flow desc
                                ),
                                0
                            ) as MaxFlow,
                            CASE
                                WHEN ct.Flag_Approval = 'Y' THEN 'Selesai'
                                WHEN ct.Flag_Approval = 'T' THEN 'Ditolak'
                                WHEN ct.Flag_Approval = 'P' THEN 'Ditolak'
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    --AND af.Status is NULL
                                    ORDER BY
                                    af.order_flow
                                ),
                                'NO'
                                )
                            END as status,
                            CASE
                                WHEN ct.Flag_Approval = 'Y' THEN 'DiSetujui'
                                WHEN ct.Flag_Approval = 'P' THEN 'Sistem'
                                WHEN ct.Flag_Approval = 'T' THEN (
                                select
                                    top 1 af.Kode_Karyawan
                                FROM
                                    HRIS_Approval_Request ar
                                    JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                WHERE
                                    ar.No_Transaksi = ct.No_Transaksi

									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval = 'T'
                                    --AND af.Status is NULL
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    --AND af.Status is NULL
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
								and a.Kode_Karyawan = b.Kode_Karyawan_Requester
                                --and b.Status is NULL
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND af.Status is null
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
									and a.Kode_Karyawan_Requester = b.Kode_Karyawan
                                    --and a.Status is Null
                                order by
                                    a.order_flow desc
                                ),
                                0
                            ) as MaxFlow,
                            CASE
                                WHEN tsi.Flag_Approval = 'Y' THEN 'Selesai'
                                WHEN tsi.Flag_Approval = 'T' THEN 'Ditolak'
                                WHEN tsi.Flag_Approval = 'P' THEN 'Ditolak'
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    --AND af.Status is NULL
                                    ORDER BY
                                    af.order_flow
                                ),
                                'NO'
                                )
                            END as status,
                            CASE
                                WHEN tsi.Flag_Approval = 'Y' THEN 'DiSetujui'
                                WHEN tsi.Flag_Approval = 'P' THEN 'Sistem'
                                WHEN tsi.Flag_Approval = 'T' THEN (
                                select
                                    top 1 af.Kode_Karyawan
                                FROM
                                    HRIS_Approval_Request ar
                                    JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                WHERE
                                    ar.No_Transaksi = tsi.No_Transaksi

									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval = 'T'
                                    --AND af.Status is NULL
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    --AND af.Status is NULL
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
								and a.Kode_Karyawan = b.Kode_Karyawan_Requester
                               -- and b.Status is NULL
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NOT NULL
                                    --AND af.Status is NULL
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
									and a.Kode_Karyawan_Requester = b.Kode_Karyawan
                                    --and a.Status is NULL
                                order by
                                    a.order_flow desc
                                ),
                                0
                            ) as MaxFlow,
                            CASE
                                WHEN ttp.Flag_Approval = 'Y' THEN 'Selesai'
                                WHEN ttp.Flag_Approval = 'T' THEN 'Ditolak'
                                WHEN ttp.Flag_Approval = 'P' THEN 'Ditolak'
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    --AND af.Status is NULL
                                    ORDER BY
                                    af.order_flow
                                ),
                                'NO'
                                )
                            END as status,
                            CASE
                                WHEN ttp.Flag_Approval = 'Y' THEN 'DiSetujui'
                                WHEN ttp.Flag_Approval = 'P' THEN 'Sistem'
                                WHEN ttp.Flag_Approval = 'T' THEN (
                                select
                                    top 1 af.Kode_Karyawan
                                FROM
                                    HRIS_Approval_Request ar
                                    JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                WHERE
                                    ar.No_Transaksi = ttp.No_Transaksi

									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval = 'T'
                                    --AND af.Status is NULL
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    ---AND af.Status is NULL
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
								and a.Kode_Karyawan = b.Kode_Karyawan_Requester
                                --and b.Status is NULL
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
            $dataFinal =  collect($result)->map(function ($item) {
                if (!empty($item->Kode_Karyawan)) {
                    $item->Kode_Karyawan = Crypt::encryptString($item->Kode_Karyawan);

                }

                return $item;
            });



            $dataCuti= (object) [
                'Sisa_Cuti' => (int) Auth::user()->karyawan->sisa_cuti(),
                'Hutang_Cuti'=>(int) Auth::user()->karyawan->hutang_cuti()

            ];
            // dd($dataCuti);
            $TanggalCutiSudahDipakai = DB::table('Transaksi_Cuti_Detail as a')
                                        ->join("Transaksi_Cuti as b", "a.No_Transaksi", '=', 'b.No_Transaksi')
                                        ->whereNull("b.Status")
                                        ->where("a.Kode_Karyawan", $Kode_Karyawan)
                                        ->where(function($query) {
                                            $query->where("a.Flag_Approval", "<>", "T")
                                            ->orWhereNull('a.Flag_Approval');

                                        })
                                        ->get();


            $TanggalSakitIzinSudahDipakai =  DB::table('Transaksi_Sakit_Izin_Detail as a')
                                    ->join("Transaksi_Sakit_Izin as b", "a.No_Transaksi", '=', 'b.No_Transaksi')
                                    ->whereNull("b.Status")
                                    ->where("Kode_Karyawan", $Kode_Karyawan)
                                    ->where(function($query) {
                                            $query->where("a.Flag_Approval", "<>", "T")
                                            ->orWhereNull('a.Flag_Approval');

                                        })
                                    ->get();
            // dd($TanggalSakitIzinSudahDipakai);
            $TanggalPulangCepatSudahDipakai =  DB::table('Transaksi_Terlambat_Pulang_Cepat_Detail as a')
                                    ->join("Transaksi_Terlambat_Pulang_Cepat as b", "a.No_Transaksi", '=', 'b.No_Transaksi')
                                    ->whereNull("b.Status")
                                    ->where("Kode_Karyawan", $Kode_Karyawan)
                                    ->where(function($query) {
                                        $query->where("a.Flag_Approval", "<>", "T")
                                        ->orWhereNull('a.Flag_Approval');

                                    })
                                    ->get();


            $HariLibur = collect(DB::table('HRIS_Hari_Libur')->get())->map(function ($item){
                if(!empty($item->Tanggal)){
                    $item->date = Carbon::parse($item->Tanggal)->format('Y-m-d');
                }
                if(!empty($item->Nama_Hari_Libur)){
                    // Assuming Nama_Hari_Libur is also a date string that needs formatting.
                    // If it's not a date, this line will cause an error.
                    $item->description = $item->Nama_Hari_Libur;
                }
                return $item; // It is important to return the modified item
            });

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil mengambil data',
                'data' => $dataFinal,
                'dataCuti' => $dataCuti,
                'TanggalCuti' => $TanggalCutiSudahDipakai,
                'TanggalSakitIzin' => $TanggalSakitIzinSudahDipakai,
                'TanggalPulangTerlambat' => $TanggalPulangCepatSudahDipakai,
                'HariLibur' => $HariLibur
            ]);
        }catch(\Throwable $e){
            Log::channel('izinLog')->error('Gagal Menambil data getDataIzin'.$e->getMessage());
            return response()->json([
                'status' => 404,
                'message' => 'Terjadi kesalahan coba lagi beberapa saat..'
            ], 404);
        }
    }

    public function getExportDH(Request $request){
          try{
            $start = Carbon::parse($request->start)->startOfDay(); // 2025-08-19 00:00:00
            $end   = Carbon::parse($request->end)->endOfDay();     // 2025-08-19 23:59:59

            $Kode_Karyawan = Auth::user()->karyawan->Kode_Karyawan;

           $query = "
                WITH BaseData_Terpulang AS (
                    SELECT
                        CAST(CONCAT(CAST(fa.Tanggal AS DATE),' ', fa.Jam) AS DATETIME) AS tanggal_create,
                        d.Kode_Karyawan AS Approver_Kode_Karyawan,
                        (select Nama from Karyawan where Kode_Karyawan = f.Kode_Karyawan) as requester_Nama,
                        g.ID_Divisi AS Division_ID_From_View,
                        k.ID_Level AS Level_ID_From_View,
                        d.id AS Flow_Id,
                        d.order_flow,
                        e.No_Transaksi,
                        e.Flag_Approval AS Current_Flag_Approval,
                        f.Kode_Karyawan AS Requester_Kode_Karyawan,
                        f.Jenis AS Request_Jenis,
                        CASE
                            WHEN f.Flag_Approval = 'Y' THEN 'Disetujui'
                            WHEN f.Flag_Approval = 'T' THEN 'Ditolak'
                            WHEN f.Flag_Approval = 'P' THEN 'Ditolak'
                            ELSE ISNULL(
                                (
                                    SELECT TOP 1 'Diproses'
                                    FROM HRIS_Approval_Request ar
                                    JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                    WHERE ar.No_Transaksi = f.No_Transaksi
                                    AND ar.Kode_Karyawan  = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    ORDER BY af.order_flow
                                ),
                                'NO'
                            )
                        END as status,
                        CASE
                            WHEN f.Flag_Approval = 'Y' THEN 'Disetujui'
                            WHEN f.Flag_Approval = 'P' THEN 'Sistem'
                            WHEN f.Flag_Approval = 'T' THEN (
                                select top 1 a.Nama
                                FROM HRIS_Approval_Request ar
                                JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                WHERE ar.No_Transaksi = f.No_Transaksi
                                AND ar.Kode_Karyawan  = af.Kode_Karyawan_Requester
                                AND ar.Flag_Approval = 'T'
                                ORDER BY af.order_flow
                            )
                            ELSE ISNULL(
                                (
                                    SELECT TOP 1 ka.Nama
                                    FROM HRIS_Approval_Request ar
                                    JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                    JOIN Karyawan ka ON af.Kode_Karyawan = ka.Kode_Karyawan
                                    WHERE ar.No_Transaksi = f.No_Transaksi
                                    AND ar.Kode_Karyawan  = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    ORDER BY af.order_flow
                                ),
                                null
                            )
                        END AS Approver,
                        format(f.Tanggal_Masuk_Pulang, 'dd MMMM yyyy') + ' ' + LEFT(CONVERT(varchar, CAST(f.Jam AS time), 108), 5) AS TanggalIzin,
                        f.filePath,
                        f.Alasan AS Request_Alasan,
                        ISNULL(
                            (
                                SELECT TOP 1 af.order_flow
                                FROM HRIS_Approval_Request ar
                                JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                WHERE ar.No_Transaksi = f.No_Transaksi
                                AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                AND ar.Flag_Approval IS NOT NULL
                                ORDER BY af.order_flow DESC
                            ),
                            0
                        ) as currentFlow,
                        ISNULL(
                            (
                                select top 1 order_flow
                                from HRIS_Approval_Flow a,
                                    HRIS_Approval_Request b
                                where a.id = b.Flow_Id
                                and b.No_Transaksi = f.No_Transaksi
                                order by a.order_flow desc
                            ),
                            0
                        ) as MaxFlow,
                        (
                            select STRING_AGG(
                                CONCAT(b.Kode_Karyawan, ': ',
                                    CASE WHEN a.Flag_Approval IS NOT NULL THEN a.Flag_Approval ELSE 'N' END),
                                ', '
                            ) WITHIN GROUP (ORDER BY b.order_flow) AS StatusList
                            from HRIS_Approval_Request a
                            JOIN HRIS_Approval_Flow b ON a.Flow_Id = b.id
                            where a.No_Transaksi = f.No_Transaksi
                            AND a.Kode_Karyawan = b.Kode_Karyawan_Requester
                        ) AS STATUSLIST,

                        -- START / END sebagai DATETIME (untuk overlap)
                        CAST(f.Tanggal_Masuk_Pulang AS DATETIME) AS TanggalIzin_Start,
                        CAST(f.Tanggal_Masuk_Pulang AS DATETIME) AS TanggalIzin_End
                    FROM
                        Karyawan AS a
                        INNER JOIN HRIS_Approval_Flow AS d ON d.Kode_Karyawan = a.Kode_Karyawan
                        INNER JOIN View_Divisi_Sub_Divisi AS g ON g.ID_DIVISI_SUB_DIVISI  = a.ID_Divisi_Sub_Divisi
                        INNER JOIN View_Golongan_Sub_Golongan_Level_Jabatan AS k ON k.ID_Level_Jabatan = a.ID_Level_Jabatan
                        INNER JOIN HRIS_Approval_Request AS e ON d.id = e.Flow_Id
                        INNER JOIN Transaksi_Terlambat_Pulang_Cepat_Detail AS f ON e.No_Transaksi = f.No_Transaksi and d.Kode_Karyawan = a.Kode_Karyawan
                        INNER JOIN Transaksi_Terlambat_Pulang_Cepat AS fa ON fa.No_Transaksi = f.No_Transaksi
                    WHERE
                        d.Kode_Karyawan = ?
                        and fa.Status is null
                ),
                BaseData_Cuti AS (
                    SELECT
                        CAST(CONCAT(CAST(fa.Tanggal AS DATE),' ', fa.Jam) AS DATETIME) AS tanggal_create,
                        d.Kode_Karyawan AS Approver_Kode_Karyawan,
                        (select Nama from Karyawan where Kode_Karyawan = f.Kode_Karyawan) as requester_Nama,
                        g.ID_Divisi AS Division_ID_From_View,
                        k.ID_Level AS Level_ID_From_View,
                        d.id AS Flow_Id,
                        d.order_flow,
                        e.No_Transaksi,
                        e.Flag_Approval AS Current_Flag_Approval,
                        f.Kode_Karyawan AS Requester_Kode_Karyawan,
                        f.Jenis AS Request_Jenis,
                        CASE
                            WHEN f.Flag_Approval = 'Y' THEN 'Selesai'
                            WHEN f.Flag_Approval = 'T' THEN 'Ditolak'
                            WHEN f.Flag_Approval = 'P' THEN 'Ditolak'
                            ELSE ISNULL(
                                (
                                    SELECT TOP 1 'Diproses'
                                    FROM HRIS_Approval_Request ar
                                    JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                    WHERE ar.No_Transaksi = f.No_Transaksi
                                    AND ar.Kode_Karyawan  = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    ORDER BY af.order_flow
                                ),
                                'NO'
                            )
                        END as status,
                        CASE
                            WHEN f.Flag_Approval = 'Y' THEN 'DiSetujui'
                            WHEN f.Flag_Approval = 'P' THEN 'Sistem'
                            WHEN f.Flag_Approval = 'T' THEN (
                                select top 1 a.Nama
                                FROM HRIS_Approval_Request ar
                                JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                WHERE ar.No_Transaksi = f.No_Transaksi
                                AND ar.Kode_Karyawan  = af.Kode_Karyawan_Requester
                                AND ar.Flag_Approval = 'T'
                                ORDER BY af.order_flow
                            )
                            ELSE ISNULL(
                                (
                                    SELECT TOP 1 ka.Nama
                                    FROM HRIS_Approval_Request ar
                                    JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                    JOIN Karyawan ka ON af.Kode_Karyawan = ka.Kode_Karyawan
                                    WHERE ar.No_Transaksi = f.No_Transaksi
                                    AND ar.Kode_Karyawan  = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    ORDER BY af.order_flow
                                ),
                                null
                            )
                        END AS Approver,
                        CASE
                            WHEN FORMAT(f.Tanggal_Cuti_Dari, 'dd MMMM yyyy') = FORMAT(f.Tanggal_Cuti_Sampai, 'dd MMMM yyyy')
                            THEN FORMAT(f.Tanggal_Cuti_Dari, 'dd MMMM yyyy')
                            ELSE FORMAT(f.Tanggal_Cuti_Dari, 'dd') + '-' + FORMAT(f.Tanggal_Cuti_Sampai, 'dd MMMM yyyy')
                        END AS TanggalIzin,
                        f.filePath,
                        f.Alasan AS Request_Alasan,
                        ISNULL(
                            (
                                SELECT TOP 1 af.order_flow
                                FROM HRIS_Approval_Request ar
                                JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                WHERE ar.No_Transaksi = f.No_Transaksi
                                AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                AND ar.Flag_Approval IS NOT NULL
                                ORDER BY af.order_flow DESC
                            ),
                            0
                        ) as currentFlow,
                        ISNULL(
                            (
                                select top 1 order_flow
                                from HRIS_Approval_Flow a,
                                    HRIS_Approval_Request b
                                where a.id = b.Flow_Id
                                and b.No_Transaksi = f.No_Transaksi
                                and a.Kode_Karyawan_Requester = b.Kode_Karyawan
                                order by a.order_flow desc
                            ),
                            0
                        ) as MaxFlow,
                        (
                            select STRING_AGG(
                                CONCAT(b.Kode_Karyawan, ': ',
                                    CASE WHEN a.Flag_Approval IS NOT NULL THEN a.Flag_Approval ELSE 'N' END),
                                ', '
                            ) WITHIN GROUP (ORDER BY b.order_flow) AS StatusList
                            from HRIS_Approval_Request a
                            JOIN HRIS_Approval_Flow b ON a.Flow_Id = b.id
                            where a.No_Transaksi = f.No_Transaksi
                            and a.Kode_Karyawan = b.Kode_Karyawan_Requester
                        ) AS STATUSLIST,

                        -- START/END cuti sebagai DATETIME
                        CAST(f.Tanggal_Cuti_Dari AS DATETIME) AS TanggalIzin_Start,
                        CAST(ISNULL(f.Tanggal_Cuti_Sampai, f.Tanggal_Cuti_Dari) AS DATETIME) AS TanggalIzin_End
                    FROM
                        Karyawan AS a
                        INNER JOIN HRIS_Approval_Flow AS d ON d.Kode_Karyawan = a.Kode_Karyawan
                        INNER JOIN View_Divisi_Sub_Divisi AS g ON g.ID_DIVISI_SUB_DIVISI  = a.ID_Divisi_Sub_Divisi
                        INNER JOIN View_Golongan_Sub_Golongan_Level_Jabatan AS k ON k.ID_Level_Jabatan = a.ID_Level_Jabatan
                        INNER JOIN HRIS_Approval_Request AS e ON d.id = e.Flow_Id
                        INNER JOIN Transaksi_Cuti_Detail AS f ON e.No_Transaksi = f.No_Transaksi
                        INNER JOIN Transaksi_Cuti AS fa ON fa.No_Transaksi = f.No_Transaksi
                    WHERE
                        d.Kode_Karyawan = ?
                        and fa.Status is null
                ),
                BaseData_Sakit AS (
                    SELECT
                        CAST(CONCAT(CAST(fa.Tanggal AS DATE),' ', fa.Jam) AS DATETIME) AS tanggal_create,
                        d.Kode_Karyawan AS Approver_Kode_Karyawan,
                        (select Nama from Karyawan where Kode_Karyawan = f.Kode_Karyawan) as requester_Nama,
                        g.ID_Divisi AS Division_ID_From_View,
                        k.ID_Level AS Level_ID_From_View,
                        d.id AS Flow_Id,
                        d.order_flow,
                        e.No_Transaksi,
                        e.Flag_Approval AS Current_Flag_Approval,
                        f.Kode_Karyawan AS Requester_Kode_Karyawan,
                        f.Jenis AS Request_Jenis,
                        CASE
                            WHEN f.Flag_Approval = 'Y' THEN 'Selesai'
                            WHEN f.Flag_Approval = 'T' THEN 'Ditolak'
                            WHEN f.Flag_Approval = 'P' THEN 'Ditolak'
                            ELSE ISNULL(
                                (
                                    SELECT TOP 1 'Diproses'
                                    FROM HRIS_Approval_Request ar
                                    JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                    WHERE ar.No_Transaksi = f.No_Transaksi
                                    AND ar.Kode_Karyawan  = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    ORDER BY af.order_flow
                                ),
                                'NO'
                            )
                        END as status,
                        CASE
                            WHEN f.Flag_Approval = 'Y' THEN 'DiSetujui'
                            WHEN f.Flag_Approval = 'P' THEN 'Sistem'
                            WHEN f.Flag_Approval = 'T' THEN (
                                select top 1 a.Nama
                                FROM HRIS_Approval_Request ar
                                JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                WHERE ar.No_Transaksi = f.No_Transaksi
                                AND ar.Kode_Karyawan  = af.Kode_Karyawan_Requester
                                AND ar.Flag_Approval = 'T'
                                ORDER BY af.order_flow
                            )
                            ELSE ISNULL(
                                (
                                    SELECT TOP 1 ka.Nama
                                    FROM HRIS_Approval_Request ar
                                    JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                    JOIN Karyawan ka ON af.Kode_Karyawan = ka.Kode_Karyawan
                                    WHERE ar.No_Transaksi = f.No_Transaksi
                                    AND ar.Kode_Karyawan  = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    ORDER BY af.order_flow
                                ),
                                null
                            )
                        END AS Approver,
                        CASE
                            WHEN FORMAT(f.Tanggal_Sakit_Izin_Dari, 'dd MMMM yyyy') = FORMAT(f.Tanggal_Sakit_Izin_Sampai, 'dd MMMM yyyy')
                            THEN FORMAT(f.Tanggal_Sakit_Izin_Dari, 'dd MMMM yyyy')
                            ELSE FORMAT(f.Tanggal_Sakit_Izin_Dari, 'dd') + '-' + FORMAT(f.Tanggal_Sakit_Izin_Sampai, 'dd MMMM yyyy')
                        END AS TanggalIzin,
                        f.filePath,
                        f.Alasan AS Request_Alasan,
                        ISNULL(
                            (
                                SELECT TOP 1 af.order_flow
                                FROM HRIS_Approval_Request ar
                                JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                WHERE ar.No_Transaksi = f.No_Transaksi
                                AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                AND ar.Flag_Approval IS NOT NULL
                                ORDER BY af.order_flow DESC
                            ),
                            0
                        ) as currentFlow,
                        ISNULL(
                            (
                                select top 1 order_flow
                                from HRIS_Approval_Flow a,
                                    HRIS_Approval_Request b
                                where a.id = b.Flow_Id
                                and b.No_Transaksi = f.No_Transaksi
                                and a.Kode_Karyawan_Requester = b.Kode_Karyawan
                                order by a.order_flow desc
                            ),
                            0
                        ) as MaxFlow,
                        (
                            select STRING_AGG(
                                CONCAT(b.Kode_Karyawan, ': ',
                                    CASE WHEN a.Flag_Approval IS NOT NULL THEN a.Flag_Approval ELSE 'N' END),
                                ', '
                            ) WITHIN GROUP (ORDER BY b.order_flow) AS StatusList
                            from HRIS_Approval_Request a
                            JOIN HRIS_Approval_Flow b ON a.Flow_Id = b.id
                            where a.No_Transaksi = f.No_Transaksi
                            AND a.Kode_Karyawan = b.Kode_Karyawan_Requester
                        ) AS STATUSLIST,

                        -- START/END sakit sebagai DATETIME
                        CAST(f.Tanggal_Sakit_Izin_Dari AS DATETIME) AS TanggalIzin_Start,
                        CAST(ISNULL(f.Tanggal_Sakit_Izin_Sampai, f.Tanggal_Sakit_Izin_Dari) AS DATETIME) AS TanggalIzin_End
                    FROM
                        Karyawan AS a
                        INNER JOIN HRIS_Approval_Flow AS d ON d.Kode_Karyawan = a.Kode_Karyawan
                        INNER JOIN View_Divisi_Sub_Divisi AS g ON g.ID_DIVISI_SUB_DIVISI  = a.ID_Divisi_Sub_Divisi
                        INNER JOIN View_Golongan_Sub_Golongan_Level_Jabatan AS k ON k.ID_Level_Jabatan = a.ID_Level_Jabatan
                        INNER JOIN HRIS_Approval_Request AS e ON d.id = e.Flow_Id
                        INNER JOIN Transaksi_Sakit_Izin_Detail AS f ON e.No_Transaksi = f.No_Transaksi and d.Kode_Karyawan = a.Kode_Karyawan
                        INNER JOIN Transaksi_Sakit_Izin AS fa ON fa.No_Transaksi = f.No_Transaksi
                    WHERE
                        d.Kode_Karyawan = ?
                        and fa.Status is null
                ),
                ApprovalStepValidation AS (
                    SELECT
                        bd.*,
                        TRY_CAST(bd.order_flow AS INT) AS Numeric_Order_Flow,
                        PrevApproval.Flag_Approval AS Previous_Step_Flag_Approval
                    FROM
                        (SELECT * FROM BaseData_Terpulang
                        UNION ALL
                        SELECT * FROM BaseData_Sakit
                        UNION ALL
                        SELECT * FROM BaseData_Cuti
                        ) AS bd
                        LEFT JOIN HRIS_Approval_Flow AS prev_flow
                            ON prev_flow.Kode_Karyawan_Requester = bd.Requester_Kode_Karyawan
                            AND prev_flow.order_flow = TRY_CAST(bd.order_flow AS INT) - 1
                            AND TRY_CAST(bd.order_flow AS INT) > 1
                        LEFT JOIN HRIS_Approval_Request AS PrevApproval
                            ON PrevApproval.Flow_Id = prev_flow.id
                            AND PrevApproval.No_Transaksi = bd.No_Transaksi
                )
                SELECT
                    apv.tanggal_create,
                    apv.No_Transaksi,
                    apv.Approver_Kode_Karyawan,
                    apv.requester_Nama,
                    apv.Requester_Kode_Karyawan,
                    apv.Request_Jenis as Tipe_Izin,
                    apv.Request_Alasan as Alasan,
                    apv.TanggalIzin,
                    apv.filePath as Lampiran,
                    apv.STATUSLIST,
                    apv.MaxFlow,
                    apv.currentFlow,
                    apv.status,
                    apv.Approver,
                    apv.Current_Flag_Approval AS Status_Approval_Saat_Ini,
                    apv.Previous_Step_Flag_Approval AS Status_Approval_Langkah_Sebelumnya,
                    apv.order_flow
                FROM ApprovalStepValidation AS apv
                WHERE
                    apv.Approver_Kode_Karyawan = ?
                    AND (
                        apv.Numeric_Order_Flow = 1
                        OR (
                            apv.Numeric_Order_Flow > 1
                            AND apv.Previous_Step_Flag_Approval = 'Y'
                        )
                    )

                    -- overlap check yang lebih robust:
                   AND (
    CAST(apv.TanggalIzin_Start AS DATETIME) <= CAST(DATEADD(SECOND, 86399, ? ) AS DATETIME) -- filter_end + 23:59:59
    AND CAST(apv.TanggalIzin_End   AS DATETIME) >= CAST(? AS DATETIME)  -- filter_start
)

                ORDER BY apv.tanggal_create desc
            ";



     $params = [
    $Kode_Karyawan, // BaseData_Terpulang WHERE d.Kode_Karyawan = ?
    $Kode_Karyawan, // BaseData_Cuti   WHERE d.Kode_Karyawan = ?
    $Kode_Karyawan, // BaseData_Sakit  WHERE d.Kode_Karyawan = ?
    $Kode_Karyawan, // main WHERE apv.Approver_Kode_Karyawan = ?

    $end,  // untuk CAST(? AS DATETIME) di kondisi pertama (apv.TanggalIzin_Start <= ?)
    $start // untuk CAST(? AS DATETIME) di kondisi kedua (apv.TanggalIzin_End >= ?)
];

$result = DB::select($query, $params);
            $dataFinal = collect($result)->map(function ($item) {
                if (!empty($item->Approver_Kode_Karyawan)) {
                    $item->Approver_Kode_Karyawan = Crypt::encryptString($item->Approver_Kode_Karyawan);
                }
                if (!empty($item->Requester_Kode_Karyawan)) {
                    $item->Requester_Kode_Karyawan = Crypt::encryptString($item->Requester_Kode_Karyawan);
                }
                return $item;
            });

            // dd($dataFinal);
            return response()->json([
                'status' => 200,
                'message' => 'Berhasil mengambil data',
                'data' => $dataFinal
            ]);
        }catch(\Throwable $e){
            Log::channel('izinLog')->error('Gagal Menambil data getDataIzinDH'.$e->getMessage());
            return response()->json([
                'status' => 404,
                'message' => 'Terjadi kesalahan coba lagi beberapa saat..'
            ], 404);
        }
    }

    public function getExport(Request $request){
        try{
            $start = Carbon::parse($request->start);
            $end = Carbon::parse($request->end);
            $Kode_Karyawan = Auth::user()->karyawan->Kode_Karyawan;
            $query ="
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
                                    AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NOT NULL
                                    --AND af.Status is NULL
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
                                    and a.Kode_Karyawan_Requester = b.Kode_Karyawan
                                    --and a.Status is NULL
                                order by
                                    a.order_flow desc
                                ),
                                0
                            ) as MaxFlow,
                            CASE
                                WHEN ct.Flag_Approval = 'Y' THEN 'Selesai'
                                WHEN ct.Flag_Approval = 'T' THEN 'Ditolak'
                                WHEN ct.Flag_Approval = 'P' THEN 'Ditolak'
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    --AND af.Status is NULL
                                    ORDER BY
                                    af.order_flow
                                ),
                                'NO'
                                )
                            END as status,
                            CASE
                                WHEN ct.Flag_Approval = 'Y' THEN 'DiSetujui'
                                WHEN ct.Flag_Approval = 'P' THEN 'Sistem'
                                WHEN ct.Flag_Approval = 'T' THEN (
                                select
                                    top 1 af.Kode_Karyawan
                                FROM
                                    HRIS_Approval_Request ar
                                    JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                WHERE
                                    ar.No_Transaksi = ct.No_Transaksi

									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval = 'T'
                                    --AND af.Status is NULL
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    --AND af.Status is NULL
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
								and a.Kode_Karyawan = b.Kode_Karyawan_Requester
                                --and b.Status is NULL
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND af.Status is null
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
									and a.Kode_Karyawan_Requester = b.Kode_Karyawan
                                    --and a.Status is Null
                                order by
                                    a.order_flow desc
                                ),
                                0
                            ) as MaxFlow,
                            CASE
                                WHEN tsi.Flag_Approval = 'Y' THEN 'Selesai'
                                WHEN tsi.Flag_Approval = 'T' THEN 'Ditolak'
                                WHEN tsi.Flag_Approval = 'P' THEN 'Ditolak'
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    --AND af.Status is NULL
                                    ORDER BY
                                    af.order_flow
                                ),
                                'NO'
                                )
                            END as status,
                            CASE
                                WHEN tsi.Flag_Approval = 'Y' THEN 'DiSetujui'
                                WHEN tsi.Flag_Approval = 'P' THEN 'Sistem'
                                WHEN tsi.Flag_Approval = 'T' THEN (
                                select
                                    top 1 af.Kode_Karyawan
                                FROM
                                    HRIS_Approval_Request ar
                                    JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                WHERE
                                    ar.No_Transaksi = tsi.No_Transaksi

									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval = 'T'
                                    --AND af.Status is NULL
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    --AND af.Status is NULL
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
								and a.Kode_Karyawan = b.Kode_Karyawan_Requester
                               -- and b.Status is NULL
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NOT NULL
                                    --AND af.Status is NULL
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
									and a.Kode_Karyawan_Requester = b.Kode_Karyawan
                                    --and a.Status is NULL
                                order by
                                    a.order_flow desc
                                ),
                                0
                            ) as MaxFlow,
                            CASE
                                WHEN ttp.Flag_Approval = 'Y' THEN 'Selesai'
                                WHEN ttp.Flag_Approval = 'T' THEN 'Ditolak'
                                WHEN ttp.Flag_Approval = 'P' THEN 'Ditolak'
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    --AND af.Status is NULL
                                    ORDER BY
                                    af.order_flow
                                ),
                                'NO'
                                )
                            END as status,
                            CASE
                                WHEN ttp.Flag_Approval = 'Y' THEN 'DiSetujui'
                                WHEN ttp.Flag_Approval = 'P' THEN 'Sistem'
                                WHEN ttp.Flag_Approval = 'T' THEN (
                                select
                                    top 1 af.Kode_Karyawan
                                FROM
                                    HRIS_Approval_Request ar
                                    JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                WHERE
                                    ar.No_Transaksi = ttp.No_Transaksi

									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval = 'T'
                                    --AND af.Status is NULL
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    ---AND af.Status is NULL
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
								and a.Kode_Karyawan = b.Kode_Karyawan_Requester
                                --and b.Status is NULL
                            ) AS STATUSLIST
                            FROM
                            Karyawan AS k
                            INNER JOIN Transaksi_Terlambat_Pulang_Cepat_Detail AS ttp ON k.Kode_Karyawan = ttp.Kode_Karyawan
                            INNER JOIN Transaksi_Terlambat_Pulang_Cepat AS ttpp ON ttp.No_Transaksi = ttpp.No_Transaksi
                            WHERE
                            k.Kode_Karyawan = ? and ttpp.Status is null
                        )
                       SELECT *
                        FROM cte
                        WHERE MaxFlow <> 0
                        AND (
                                -- Kalau Tanggal_Selesai NULL, cek hanya di tanggal mulai
                                (CAST(Tanggal_Selesai AS DATETIME) IS NULL
                                    AND CAST(Tanggal_Mulai AS DATETIME) BETWEEN ? AND ?)

                                -- Kalau ada tanggal selesai, cek overlap interval
                                OR (Tanggal_Selesai IS NOT NULL
                                    AND CAST(Tanggal_Mulai AS DATETIME) <= ?
                                    AND CAST(Tanggal_Selesai AS DATETIME) >= ?)
                            )
                        ORDER BY Tanggal_Mulai;
            ";
            $result = DB::select($query, [$Kode_Karyawan, $Kode_Karyawan, $Kode_Karyawan, $start, $end, $end, $start]);
            $dataFinal =  collect($result)->map(function ($item) {
                if (!empty($item->Kode_Karyawan)) {
                    $item->Kode_Karyawan = Crypt::encryptString($item->Kode_Karyawan);

                }

                return $item;
            });



            return response()->json([
                'status' => 200,
                'message' => 'Berhasil mengambil data',
                'data' => $dataFinal

            ]);
        }catch(\Throwable $e){
            Log::channel('izinLog')->error('Gagal Menambil data getDataIzin'.$e->getMessage());
            return response()->json([
                'status' => 404,
                'message' => 'Terjadi kesalahan coba lagi beberapa saat..'
            ], 404);
        }
    }


    public function getExportAdmin(Request $request){
         try{
            $start = Carbon::parse($request->start);
            $end = Carbon::parse($request->end);
            $Kode_Karyawan = Auth::user()->karyawan->Kode_Karyawan;
            $query ="
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
							(select Nama from Karyawan z where z.Kode_Karyawan = ct.Kode_Karyawan) as nama_requester,
                            ISNULL(
                                (
                                SELECT
                                    TOP 1 af.order_flow
                                FROM
                                    HRIS_Approval_Request ar
                                    JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                WHERE
                                    ar.No_Transaksi = ct.No_Transaksi
                                    AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NOT NULL
                                    --AND af.Status is NULL
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
                                    and a.Kode_Karyawan_Requester = b.Kode_Karyawan
                                    --and a.Status is NULL
                                order by
                                    a.order_flow desc
                                ),
                                0
                            ) as MaxFlow,
                            CASE
                                WHEN ct.Flag_Approval = 'Y' THEN 'Selesai'
                                WHEN ct.Flag_Approval = 'T' THEN 'Ditolak'
                                WHEN ct.Flag_Approval = 'P' THEN 'Ditolak'
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    --AND af.Status is NULL
                                    ORDER BY
                                    af.order_flow
                                ),
                                'NO'
                                )
                            END as status,
                            CASE
                                WHEN ct.Flag_Approval = 'Y' THEN 'DiSetujui'
                                WHEN ct.Flag_Approval = 'P' THEN 'Sistem'
                                WHEN ct.Flag_Approval = 'T' THEN (
                                select
                                    top 1 af.Kode_Karyawan
                                FROM
                                    HRIS_Approval_Request ar
                                    JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                WHERE
                                    ar.No_Transaksi = ct.No_Transaksi

									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval = 'T'
                                    --AND af.Status is NULL
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    --AND af.Status is NULL
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
								and a.Kode_Karyawan = b.Kode_Karyawan_Requester
                                --and b.Status is NULL
                            ) AS STATUSLIST
                            FROM
                            Karyawan AS k
                            INNER JOIN Transaksi_Cuti_Detail AS ct ON k.Kode_Karyawan = ct.Kode_Karyawan
                            INNER JOIN Transaksi_Cuti AS cti ON ct.No_Transaksi = cti.No_Transaksi
                            WHERE
                             cti.Status is null
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
							(select Nama from Karyawan z where z.Kode_Karyawan = tsi.Kode_Karyawan) as nama_requester,
                            ISNULL(
                                (
                                SELECT
                                    TOP 1 af.order_flow
                                FROM
                                    HRIS_Approval_Request ar
                                    JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                WHERE
                                    ar.No_Transaksi = tsi.No_Transaksi
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND af.Status is null
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
									and a.Kode_Karyawan_Requester = b.Kode_Karyawan
                                    --and a.Status is Null
                                order by
                                    a.order_flow desc
                                ),
                                0
                            ) as MaxFlow,
                            CASE
                                WHEN tsi.Flag_Approval = 'Y' THEN 'Selesai'
                                WHEN tsi.Flag_Approval = 'T' THEN 'Ditolak'
                                WHEN tsi.Flag_Approval = 'P' THEN 'Ditolak'
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    --AND af.Status is NULL
                                    ORDER BY
                                    af.order_flow
                                ),
                                'NO'
                                )
                            END as status,
                            CASE
                                WHEN tsi.Flag_Approval = 'Y' THEN 'DiSetujui'
                                WHEN tsi.Flag_Approval = 'P' THEN 'Sistem'
                                WHEN tsi.Flag_Approval = 'T' THEN (
                                select
                                    top 1 af.Kode_Karyawan
                                FROM
                                    HRIS_Approval_Request ar
                                    JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                WHERE
                                    ar.No_Transaksi = tsi.No_Transaksi

									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval = 'T'
                                    --AND af.Status is NULL
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    --AND af.Status is NULL
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
								and a.Kode_Karyawan = b.Kode_Karyawan_Requester
                               -- and b.Status is NULL
                            ) AS STATUSLIST
                            FROM
                            Karyawan AS k
                            INNER JOIN Transaksi_Sakit_Izin_Detail AS tsi ON k.Kode_Karyawan = tsi.Kode_Karyawan
                            INNER JOIN Transaksi_Sakit_Izin AS tsip ON tsi.No_Transaksi = tsip.No_Transaksi
                            WHERE
                             tsip.Status is null
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
							(select Nama from Karyawan z where z.Kode_Karyawan = ttp.Kode_Karyawan) as nama_requester,

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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NOT NULL
                                    --AND af.Status is NULL
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
									and a.Kode_Karyawan_Requester = b.Kode_Karyawan
                                    --and a.Status is NULL
                                order by
                                    a.order_flow desc
                                ),
                                0
                            ) as MaxFlow,
                            CASE
                                WHEN ttp.Flag_Approval = 'Y' THEN 'Selesai'
                                WHEN ttp.Flag_Approval = 'T' THEN 'Ditolak'
                                WHEN ttp.Flag_Approval = 'P' THEN 'Ditolak'
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    --AND af.Status is NULL
                                    ORDER BY
                                    af.order_flow
                                ),
                                'NO'
                                )
                            END as status,
                            CASE
                                WHEN ttp.Flag_Approval = 'Y' THEN 'DiSetujui'
                                WHEN ttp.Flag_Approval = 'P' THEN 'Sistem'
                                WHEN ttp.Flag_Approval = 'T' THEN (
                                select
                                    top 1 af.Kode_Karyawan
                                FROM
                                    HRIS_Approval_Request ar
                                    JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                WHERE
                                    ar.No_Transaksi = ttp.No_Transaksi

									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval = 'T'
                                    --AND af.Status is NULL
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
									AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                    AND ar.Flag_Approval IS NULL
                                    ---AND af.Status is NULL
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
								and a.Kode_Karyawan = b.Kode_Karyawan_Requester
                                --and b.Status is NULL
                            ) AS STATUSLIST
                            FROM
                            Karyawan AS k
                            INNER JOIN Transaksi_Terlambat_Pulang_Cepat_Detail AS ttp ON k.Kode_Karyawan = ttp.Kode_Karyawan
                            INNER JOIN Transaksi_Terlambat_Pulang_Cepat AS ttpp ON ttp.No_Transaksi = ttpp.No_Transaksi
                            WHERE
                           ttpp.Status is null
                        )
                       SELECT *
                        FROM cte
                        WHERE MaxFlow <> 0
                        AND (
                                -- Kalau Tanggal_Selesai NULL, cek hanya di tanggal mulai
                                (CAST(Tanggal_Selesai AS DATETIME) IS NULL
                                    AND CAST(Tanggal_Mulai AS DATETIME) BETWEEN ? AND ?)

                                -- Kalau ada tanggal selesai, cek overlap interval
                                OR (Tanggal_Selesai IS NOT NULL
                                    AND CAST(Tanggal_Mulai AS DATETIME) <= ?
                                    AND CAST(Tanggal_Selesai AS DATETIME) >= ?)
                            )
                        ORDER BY Tanggal_Mulai;
            ";
            $result = DB::select($query, [$start, $end, $end, $start ]);
            $dataFinal =  collect($result)->map(function ($item) {
                if (!empty($item->Kode_Karyawan)) {
                    $item->Kode_Karyawan = Crypt::encryptString($item->Kode_Karyawan);

                }

                return $item;
            });

            // dd($dataFinal);
            return response()->json([
                'status' => 200,
                'message' => 'Berhasil mengambil data',
                'data' => $dataFinal,

            ]);
        }catch(\Throwable $e){
            Log::channel('izinLog')->error('Gagal Menambil data getDataIzin'.$e->getMessage());
            return response()->json([
                'status' => 404,
                'message' => 'Terjadi kesalahan coba lagi beberapa saat..'
            ], 404);
        }
    }

    public function getDataIzinDH(){
        try{

            $Kode_Karyawan = Auth::user()->karyawan->Kode_Karyawan;

            $query = "
                WITH BaseData_Terpulang AS (
                SELECT
                    CAST(CONCAT(CAST(fa.Tanggal AS DATE),' ', fa.Jam) AS DATETIME) AS tanggal_create,
                    d.Kode_Karyawan AS Approver_Kode_Karyawan,
                    (select Nama from Karyawan where Kode_Karyawan = f.Kode_Karyawan) as requester_Nama,
                    g.ID_Divisi AS Division_ID_From_View,
                    k.ID_Level AS Level_ID_From_View,
                    d.id AS Flow_Id,
                    d.order_flow,
                    e.No_Transaksi,
                    e.Flag_Approval AS Current_Flag_Approval,
                    f.Kode_Karyawan AS Requester_Kode_Karyawan,
                    f.Jenis AS Request_Jenis,
                    CASE
                                    WHEN f.Flag_Approval = 'Y' THEN 'Disetujui'
                                    WHEN f.Flag_Approval = 'T' THEN 'Ditolak'
                                    WHEN f.Flag_Approval = 'P' THEN 'Ditolak'
                                    ELSE ISNULL(
                                    (
                                        SELECT
                                        TOP 1 'Diproses'
                                        FROM
                                        HRIS_Approval_Request ar
                                        JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                        WHERE
                                        ar.No_Transaksi = f.No_Transaksi
                                        AND ar.Kode_Karyawan  = af.Kode_Karyawan_Requester
                                        --AND af.Status is Null
                                        AND ar.Flag_Approval IS NULL
                                        ORDER BY
                                        af.order_flow
                                    ),
                                    'NO'
                                    )
                                END as status,
                                CASE
                                    WHEN f.Flag_Approval = 'Y' THEN 'Disetujui'
                                    WHEN f.Flag_Approval = 'P' THEN 'Sistem'
                                    WHEN f.Flag_Approval = 'T' THEN (
                                    select
                                        top 1 a.Nama
                                    FROM
                                        HRIS_Approval_Request ar
                                        JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                    WHERE
                                        ar.No_Transaksi = f.No_Transaksi
                                        AND ar.Kode_Karyawan  = af.Kode_Karyawan_Requester
                                        --AND af.Status is NULL
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
                                        ar.No_Transaksi = f.No_Transaksi
                                        AND ar.Kode_Karyawan  = af.Kode_Karyawan_Requester
                                        AND ar.Flag_Approval IS NULL
                                        --AND af.Status is NULL
                                        ORDER BY
                                        af.order_flow
                                    ),
                                    null
                                    )
                                END AS Approver,
                    format(f.Tanggal_Masuk_Pulang, 'dd MMMM yyyy') + ' ' +  LEFT(CONVERT(varchar, CAST(f.Jam AS time), 108), 5) AS TanggalIzin,
                    f.filePath,

                    f.Alasan AS Request_Alasan,
                    ISNULL(
                                    (
                                    SELECT
                                        TOP 1 af.order_flow
                                    FROM
                                        HRIS_Approval_Request ar
                                        JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                    WHERE
                                        ar.No_Transaksi = f.No_Transaksi

                                        AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                        --AND af.Status is NULL

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
                                        and b.No_Transaksi = f.No_Transaksi
                                        --and a.Status is NULL
                                    order by
                                        a.order_flow desc
                                    ),
                                    0
                                ) as MaxFlow,
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
                                    a.No_Transaksi = f.No_Transaksi
                                    AND a.Kode_Karyawan = b.Kode_Karyawan_Requester
                                    --AND b.Status is NULL
                                ) AS STATUSLIST
                FROM
                    Karyawan AS a

                    INNER JOIN HRIS_Approval_Flow AS d ON d.Kode_Karyawan = a.Kode_Karyawan
                    INNER JOIN View_Divisi_Sub_Divisi AS g ON g.ID_DIVISI_SUB_DIVISI  = a.ID_Divisi_Sub_Divisi
                    INNER JOIN View_Golongan_Sub_Golongan_Level_Jabatan AS k ON k.ID_Level_Jabatan = a.ID_Level_Jabatan
                    INNER JOIN HRIS_Approval_Request AS e ON d.id = e.Flow_Id
                    INNER JOIN Transaksi_Terlambat_Pulang_Cepat_Detail AS f ON e.No_Transaksi = f.No_Transaksi and d.Kode_Karyawan = a.Kode_Karyawan
                    INNER JOIN Transaksi_Terlambat_Pulang_Cepat AS fa ON fa.No_Transaksi = f.No_Transaksi
                    WHERE
                    d.Kode_Karyawan = ?
                    and fa.Status is null
                    --and d.Status is NULL
            ),
            BaseData_Cuti AS (
                SELECT
                    CAST(CONCAT(CAST(fa.Tanggal AS DATE),' ', fa.Jam) AS DATETIME) AS tanggal_create,
                    d.Kode_Karyawan AS Approver_Kode_Karyawan,
                    (select Nama from Karyawan where Kode_Karyawan = f.Kode_Karyawan) as requester_Nama,
                    g.ID_Divisi AS Division_ID_From_View,
                    k.ID_Level AS Level_ID_From_View,
                    d.id AS Flow_Id,
                    d.order_flow,
                    e.No_Transaksi,
                    e.Flag_Approval AS Current_Flag_Approval,
                    f.Kode_Karyawan AS Requester_Kode_Karyawan,

                    f.Jenis AS Request_Jenis,
                    CASE
                                    WHEN f.Flag_Approval = 'Y' THEN 'Selesai'
                                    WHEN f.Flag_Approval = 'T' THEN 'Ditolak'
                                    WHEN f.Flag_Approval = 'P' THEN 'Ditolak'
                                    ELSE ISNULL(
                                    (
                                        SELECT
                                        TOP 1 'Diproses'
                                        FROM
                                        HRIS_Approval_Request ar
                                        JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                        WHERE
                                        ar.No_Transaksi = f.No_Transaksi
                                        AND ar.Kode_Karyawan  = af.Kode_Karyawan_Requester
                                        --AND af.Status is NULL
                                        AND ar.Flag_Approval IS NULL
                                        ORDER BY
                                        af.order_flow
                                    ),
                                    'NO'
                                    )
                                END as status,
                                CASE
                                    WHEN f.Flag_Approval = 'Y' THEN 'DiSetujui'
                                    WHEN f.Flag_Approval = 'P' THEN 'Sistem'
                                    WHEN f.Flag_Approval = 'T' THEN (
                                    select
                                        top 1 a.Nama
                                    FROM
                                        HRIS_Approval_Request ar
                                        JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                    WHERE
                                        ar.No_Transaksi = f.No_Transaksi
                                        AND ar.Kode_Karyawan  = af.Kode_Karyawan_Requester
                                        --AND af.Status is NULL
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
                                        ar.No_Transaksi = f.No_Transaksi
                                        AND ar.Kode_Karyawan  = af.Kode_Karyawan_Requester
                                        AND ar.Flag_Approval IS NULL
                                        --AND af.Status is NULL
                                        ORDER BY
                                        af.order_flow
                                    ),
                                    null
                                    )
                                END AS Approver,
                    CASE
                        WHEN FORMAT(f.Tanggal_Cuti_Dari, 'dd MMMM yyyy') = FORMAT(f.Tanggal_Cuti_Sampai, 'dd MMMM yyyy')
                        THEN FORMAT(f.Tanggal_Cuti_Dari, 'dd MMMM yyyy')
                        ELSE
                            FORMAT(f.Tanggal_Cuti_Dari, 'dd') + '-' + FORMAT(f.Tanggal_Cuti_Sampai, 'dd MMMM yyyy')
                    END AS TanggalIzin,
                    f.filePath,
                    f.Alasan AS Request_Alasan,
                    ISNULL(
                                    (
                                    SELECT
                                        TOP 1 af.order_flow
                                    FROM
                                        HRIS_Approval_Request ar
                                        JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                    WHERE
                                        ar.No_Transaksi = f.No_Transaksi
                                        AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester
                                        --AND af.Status is NULL
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
                                        and b.No_Transaksi = f.No_Transaksi
                                        and a.Kode_Karyawan_Requester = b.Kode_Karyawan
                                        --and a.Status is NULL
                                    order by
                                        a.order_flow desc
                                    ),
                                    0
                                ) as MaxFlow,
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
                                    a.No_Transaksi = f.No_Transaksi
                                    and a.Kode_Karyawan = b.Kode_Karyawan_Requester
                                    --and b.Status is NULL
                                ) AS STATUSLIST
                FROM
                    Karyawan AS a

                    INNER JOIN HRIS_Approval_Flow AS d ON d.Kode_Karyawan = a.Kode_Karyawan
                    INNER JOIN View_Divisi_Sub_Divisi AS g ON g.ID_DIVISI_SUB_DIVISI  = a.ID_Divisi_Sub_Divisi
                    INNER JOIN View_Golongan_Sub_Golongan_Level_Jabatan AS k ON k.ID_Level_Jabatan = a.ID_Level_Jabatan
                    INNER JOIN HRIS_Approval_Request AS e ON d.id = e.Flow_Id
                    INNER JOIN Transaksi_Cuti_Detail AS f ON e.No_Transaksi = f.No_Transaksi
                    INNER JOIN Transaksi_Cuti AS fa ON fa.No_Transaksi = f.No_Transaksi
                WHERE
                    d.Kode_Karyawan = ?
                    and fa.Status is null
                --and d.Status is NULL

            ),
            BaseData_Sakit AS (
                SELECT
                    CAST(CONCAT(CAST(fa.Tanggal AS DATE),' ', fa.Jam) AS DATETIME) AS tanggal_create,
                    d.Kode_Karyawan AS Approver_Kode_Karyawan,
                    (select Nama from Karyawan where Kode_Karyawan = f.Kode_Karyawan) as requester_Nama,
                    g.ID_Divisi AS Division_ID_From_View,
                    k.ID_Level AS Level_ID_From_View,
                    d.id AS Flow_Id,
                    d.order_flow,
                    e.No_Transaksi,
                    e.Flag_Approval AS Current_Flag_Approval,
                    f.Kode_Karyawan AS Requester_Kode_Karyawan,

                    f.Jenis AS Request_Jenis,
                    CASE
                                    WHEN f.Flag_Approval = 'Y' THEN 'Selesai'
                                    WHEN f.Flag_Approval = 'T' THEN 'Ditolak'
                                    WHEN f.Flag_Approval = 'P' THEN 'Ditolak'
                                    ELSE ISNULL(
                                    (
                                        SELECT
                                        TOP 1 'Diproses'
                                        FROM
                                        HRIS_Approval_Request ar
                                        JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                        WHERE
                                        ar.No_Transaksi = f.No_Transaksi
                                        AND ar.Kode_Karyawan  = af.Kode_Karyawan_Requester
                                        --AND af.Status is NULL
                                        AND ar.Flag_Approval IS NULL
                                        ORDER BY
                                        af.order_flow
                                    ),
                                    'NO'
                                    )
                                END as status,
                                CASE
                                    WHEN f.Flag_Approval = 'Y' THEN 'DiSetujui'
                                    WHEN f.Flag_Approval = 'P' THEN 'Sistem'
                                    WHEN f.Flag_Approval = 'T' THEN (
                                    select
                                        top 1 a.Nama
                                    FROM
                                        HRIS_Approval_Request ar
                                        JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                    WHERE
                                        ar.No_Transaksi = f.No_Transaksi
                                        AND ar.Kode_Karyawan  = af.Kode_Karyawan_Requester
                                        --AND af.Status is NULL
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
                                        ar.No_Transaksi = f.No_Transaksi
                                        AND ar.Kode_Karyawan  = af.Kode_Karyawan_Requester
                                        AND ar.Flag_Approval IS NULL
                                        --AND af.Status is NULL
                                        ORDER BY
                                        af.order_flow
                                    ),
                                    null
                                    )
                                END AS Approver,
                    CASE
                        WHEN FORMAT(f.Tanggal_Sakit_Izin_Dari, 'dd MMMM yyyy') = FORMAT(f.Tanggal_Sakit_Izin_Sampai, 'dd MMMM yyyy')
                        THEN FORMAT(f.Tanggal_Sakit_Izin_Dari, 'dd MMMM yyyy')
                        ELSE
                            FORMAT(f.Tanggal_Sakit_Izin_Dari, 'dd') + '-' + FORMAT(f.Tanggal_Sakit_Izin_Sampai, 'dd MMMM yyyy')
                    END AS TanggalIzin,
                    f.filePath,
                    f.Alasan AS Request_Alasan,
                    ISNULL(
                                    (
                                    SELECT
                                        TOP 1 af.order_flow
                                    FROM
                                        HRIS_Approval_Request ar
                                        JOIN HRIS_Approval_Flow af ON ar.Flow_Id = af.id
                                    WHERE
                                        ar.No_Transaksi = f.No_Transaksi
                                        AND ar.Kode_Karyawan = af.Kode_Karyawan_Requester

                                        AND ar.Flag_Approval IS NOT NULL
                                        --AND af.Status is NULL
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
                                        and b.No_Transaksi = f.No_Transaksi
                                        and a.Kode_Karyawan_Requester = b.Kode_Karyawan
                                        --and a.Status is NULL
                                    order by
                                        a.order_flow desc
                                    ),
                                    0
                                ) as MaxFlow,
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
                                    a.No_Transaksi = f.No_Transaksi
                                    AND a.Kode_Karyawan = b.Kode_Karyawan_Requester
                                    --AND b.Status is NULL
                                ) AS STATUSLIST
                FROM
                    Karyawan AS a

                    INNER JOIN HRIS_Approval_Flow AS d ON d.Kode_Karyawan = a.Kode_Karyawan
                    INNER JOIN View_Divisi_Sub_Divisi AS g ON g.ID_DIVISI_SUB_DIVISI  = a.ID_Divisi_Sub_Divisi
                    INNER JOIN View_Golongan_Sub_Golongan_Level_Jabatan AS k ON k.ID_Level_Jabatan = a.ID_Level_Jabatan
                    INNER JOIN HRIS_Approval_Request AS e ON d.id = e.Flow_Id
                    INNER JOIN Transaksi_Sakit_Izin_Detail AS f ON e.No_Transaksi = f.No_Transaksi and d.Kode_Karyawan = a.Kode_Karyawan
                    INNER JOIN Transaksi_Sakit_Izin AS fa ON fa.No_Transaksi = f.No_Transaksi
                WHERE
                    d.Kode_Karyawan = ?
                    and fa.Status is null
                    --and d.Status is null

            ),
            ApprovalStepValidation AS (
                SELECT
                    bd.*,
                    TRY_CAST(bd.order_flow AS INT) AS Numeric_Order_Flow,
                    PrevApproval.Flag_Approval AS Previous_Step_Flag_Approval
                FROM
                    (SELECT * FROM BaseData_Terpulang
                    UNION ALL
                    SELECT * FROM BaseData_Sakit
                    UNION ALL
                    SELECT * FROM BaseData_Cuti
                    ) AS bd
                    LEFT JOIN HRIS_Approval_Flow AS prev_flow
                        ON prev_flow.Kode_Karyawan_Requester = bd.Requester_Kode_Karyawan AND
                        prev_flow.order_flow = TRY_CAST(bd.order_flow AS INT) - 1
                        AND TRY_CAST(bd.order_flow AS INT) > 1
                    LEFT JOIN HRIS_Approval_Request AS PrevApproval
                        ON PrevApproval.Flow_Id = prev_flow.id
                        AND PrevApproval.No_Transaksi = bd.No_Transaksi
                        --AND prev_flow.Status is NULL
            )
            -- Query utama untuk menampilkan hasil
            SELECT
                apv.tanggal_create,
                apv.No_Transaksi,
                apv.Approver_Kode_Karyawan,
                apv.requester_Nama,
                apv.Requester_Kode_Karyawan,
                apv.Request_Jenis as Tipe_Izin,
                apv.Request_Alasan as Alasan,
                apv.TanggalIzin,
                apv.filePath as Lampiran,
                apv.STATUSLIST,
                apv.MaxFlow,
                apv.currentFlow,
                apv.status,
                apv.Approver,
                apv.Current_Flag_Approval AS Status_Approval_Saat_Ini,
                apv.Previous_Step_Flag_Approval AS Status_Approval_Langkah_Sebelumnya,
                apv.order_flow
            FROM
                ApprovalStepValidation AS apv
            WHERE
                apv.Approver_Kode_Karyawan = ?

                AND (
                    apv.Numeric_Order_Flow = 1
                    OR (
                        apv.Numeric_Order_Flow > 1
                        AND  apv.Previous_Step_Flag_Approval = 'Y'

                    )
                )
            ORDER BY apv.tanggal_create desc
            ";

            $result = DB::select($query, [$Kode_Karyawan, $Kode_Karyawan, $Kode_Karyawan, $Kode_Karyawan]);

            $dataFinal = collect($result)->map(function ($item) {
                if (!empty($item->Approver_Kode_Karyawan)) {
                    $item->Approver_Kode_Karyawan = Crypt::encryptString($item->Approver_Kode_Karyawan);
                }
                if (!empty($item->Requester_Kode_Karyawan)) {
                    $item->Requester_Kode_Karyawan = Crypt::encryptString($item->Requester_Kode_Karyawan);
                }
                return $item;
            });


            return response()->json([
                'status' => 200,
                'message' => 'Berhasil mengambil data',
                'data' => $dataFinal
            ]);
        }catch(\Throwable $e){
            Log::channel('izinLog')->error('Gagal Menambil data getDataIzinDH'.$e->getMessage());
            return response()->json([
                'status' => 404,
                'message' => 'Terjadi kesalahan coba lagi beberapa saat..'
            ], 404);
        }
    }


    public function getAttachmentUrl(Request $request)
    {
        try {
            $filePath = $request->input('path');

            if (!$filePath) {
                return response()->json(['message' => 'Path lampiran tidak diberikan.'], 400);
            }

            // Get file content dari GCS
            $fileContent = Storage::disk('gcs')->get($filePath);
            $fileName = basename($filePath);

            // Tentukan mime type
            $mimeType = Storage::disk('gcs')->mimeType($filePath);

            return response($fileContent, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Content-Length' => strlen($fileContent),
            ]);

        } catch (\Exception $e) {
            Log::channel('izinLog')->error('Error downloading file getAttachmentUrl: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mendownload file.'], 500);
        }
    }

    public function parseIzin($jenis){
        // dd($jenis);
        if($jenis == 'izinFull'){
            return 'Izin Pribadi';
        }else if($jenis == 'sakit'){
            return 'Sakit';
        }else if($jenis == 'pulangCepat'){
            return 'Izin Pulang Cepat';
        }else if($jenis == 'terlambat'){
            return 'Izin Terlambat';
        }else if($jenis == 'CUTI'){
            return 'Izin Cuti';
        }else if($jenis == 'cuti'){
            return 'Izin Cuti';
        }else if($jenis == 'cutiHutang'){
            return 'Izin Cuti';
        }else{
            return "Undefined!";
        }
    }

    public function parseJam($time) {
        $formats = ['H:i:s', 'H:i']; // coba dua-duanya
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $time);
            } catch (InvalidArgumentException $e) {
                // lanjut ke format berikutnya
            }
        }
        throw new InvalidArgumentException("Format jam tidak dikenali: $time");
    }

    public function submitIzin(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'jenisIzin' => 'required|string',
            'tanggal' => 'required|array',
            'tanggal.*' => 'required',
            'waktu' => 'required|string',
            'Alasan' => 'required|string|max:300',
            'file' => 'sometimes|nullable|mimes:jpeg,png,jpg,gif,pdf',
        ]);


        if ($validator->fails()) {
            Log::channel('izinLog')->error('data Izin tidka valid'.$validator->errors());

            return response()->json([
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();
        // $approver = DB::table('HRIS_Approval_Flow as a')
        //     ->join('Karyawan as b', 'a.Kode_Karyawan', '=', 'b.Kode_Karyawan')
        //     ->whereNull('a.Status')
        //     ->where('b.Aktif', 'Y')
        //     ->whereNull('b.Tanggal_Resign')
        //     ->where('a.Kode_Karyawan_Requester', Auth::user()->karyawan->Kode_Karyawan)
        //     ->get();

        // dd($approver);

        // if ($approver->isEmpty()) {
        //     Log::channel('uDashLog')->error('Tidak ada approver. Hubungi admin untuk menugaskan approver divisi anda!');

        //     return response()->json([
        //         'message' => 'Tidak ada approver. Hubungi admin untuk menugaskan approver divisi anda!.',
        //         'errors' => 'Tidak ada approver. Hubungi admin untuk menugaskan approver divisi anda!.'
        //     ], 404);
        // }
        $approverAll = DB::table('HRIS_Approval_Flow as a')
            ->join('Karyawan as b', 'a.Kode_Karyawan', '=', 'b.Kode_Karyawan')
            ->whereNull('a.Status')
            ->where('a.Kode_Karyawan_Requester', Auth::user()->karyawan->Kode_Karyawan)
            ->select(
                'a.*',

                'b.Aktif',
                'b.Tanggal_Resign'
            )
            ->get();

        // jika tidak ada approver sama sekali
        if ($approverAll->isEmpty()) {
            Log::channel('uDashLog')->error('Tidak ada approver sama sekali!');

            return response()->json([
                'message' => 'Tidak ada approver. Hubungi admin untuk menugaskan approver izin anda!.',
                'errors'  => 'Tidak ada approver. Hubungi admin untuk menugaskan approver izin anda!.'
            ], 404);
        }

        // cek apakah semua approver aktif
        $allActive = $approverAll->every(function ($item) {
            return $item->Aktif === 'Y' && is_null($item->Tanggal_Resign);
        });

        // kalau ada 1 saja yang nonaktif/resign → blok
        if (! $allActive) {
            Log::channel('uDashLog')->error('Terdapat approver nonaktif/resign!');

            return response()->json([
                'message' => 'Terdapat approver nonaktif. Hubungi admin untuk memperbarui approver izin anda!.',
                'errors'  => 'Terdapat approver nonaktif. Hubungi admin untuk memperbarui approver izin anda!.'
            ], 404);
        }

        // lanjut kalau semua approver valid
        $approver = $approverAll;

        // dd($approver);


        try {
            DB::beginTransaction();

            if($validatedData['jenisIzin'] == 'izinFull' || $validatedData['jenisIzin'] == 'sakit' ||  $validatedData['jenisIzin'] == 'sakitTibaTiba'){
                 // Logika pembuatan nomor faktur langsung di sini
                $formatTanggalSekarang = Carbon::now()->format('m-y');
                $prefix = "IS" . $formatTanggalSekarang . "-";

                $checkLastFaktur = DB::table('Transaksi_Sakit_Izin')
                                        ->select(DB::raw('RIGHT(No_Transaksi, 4) AS angka'))
                                        ->where('No_Transaksi', 'like', $prefix . '%')
                                        ->orderByDesc(DB::raw('RIGHT(No_Transaksi, 4)'))
                                        ->first();

                $angkaTerakhir = $checkLastFaktur ? (int)$checkLastFaktur->angka + 1 : 1;
                $noFaktur = $prefix . sprintf('%04d', $angkaTerakhir);
                // Akhir logika pembuatan nomor faktur

                $kodePerusahaan = '001';
                $tanggalSaatIni = date('Y-m-d');
                $jamSaatIni = date('H:i:s');
                $userID = Auth::id();
                $kodeKaryawan = Auth::user()->karyawan->Kode_Karyawan;
                $jenisIzin = $validatedData['jenisIzin'];
                $tanggalSakitIzinDari = $validatedData['tanggal'][0] . ' ' . $validatedData['waktu'];
                $tanggalSakitIzinSampai = $validatedData['tanggal'][1] . ' ' . $validatedData['waktu'];
                $alasan = $validatedData['Alasan'];

                $filePath = null;
                if ($request->hasFile('file')) {
                    $file = $request->file('file');
                    $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $folderPath = 'izinSakitCuti/' . date('Y/m');
                    $filePath = $file->storeAs($folderPath, $fileName, 'public');
                }
                if($filePath !== null){
                    Storage::disk('gcs')->putFileAs($folderPath, $file, $fileName);
                }


                $transaksi = [
                    'Kode_Perusahaan' => $kodePerusahaan,
                    'No_Transaksi' => $noFaktur,
                    'Tanggal' => $tanggalSaatIni,
                    'Jam' => $jamSaatIni,
                    'UserID' => $userID,

                ];

                $transaksi_detail = [
                    'Kode_Perusahaan' => $kodePerusahaan,
                    'No_Transaksi' => $noFaktur,
                    'Kode_Karyawan' => $kodeKaryawan,
                    'Jenis' => $jenisIzin,
                    'Tanggal_Sakit_Izin_Dari' => $tanggalSakitIzinDari,
                    'Tanggal_Sakit_Izin_Sampai' => $tanggalSakitIzinSampai,
                    'Alasan' => $alasan,
                    'filePath' => $filePath != "" ? $filePath : null,
                ];
                $Division_Id =  Auth::user()->divisionKaryawan;
                $flow = DB::table('HRIS_Approval_flow')
                ->whereNull("Status")
                ->where("Kode_Karyawan_Requester", $kodeKaryawan)
                ->get()
                ->map(function ($item) use ($kodeKaryawan, $noFaktur) {
                    return [
                        'Kode_Karyawan' => $kodeKaryawan,
                        'update_at' => date('Y-m-d H:i:s'),
                        'Flow_Id' => $item->id,
                        'No_Transaksi' => $noFaktur,
                    ];
                });

                $inserFlowtoRequest = DB::table('HRIS_Approval_Request')->insert($flow->toArray());

                if($inserFlowtoRequest){
                    $HPflow = DB::table('HRIS_Approval_flow as a')
                    ->select("b.HP", "b.Nama", "b.Kode_Karyawan")
                    ->join('Karyawan as b', 'a.Kode_Karyawan' , '=','b.Kode_Karyawan')
                    ->where('a.Kode_Karyawan_Requester', $kodeKaryawan)
                    ->whereNull("a.Status")
                    ->orderBy("a.order_flow")
                    ->first();


                            $user = DB::table('Karyawan')
                                ->where('Kode_Karyawan', $HPflow->Kode_Karyawan)
                                ->first();
                            $userInsertNoHp = $user->HP ?? null;
                            if(!$userInsertNoHp || $userInsertNoHp == ' ' || $userInsertNoHp == '-' ){
                                Log::channel('waIzinPage')->warning('Pesan Error', [
                                    "pesan" => "Karyawan {$user->Nama} Tidak ada Nomor Hp untuk noitifikasi Transaksi {$noFaktur}"
                                ]);
                            }else{


                            // whatsapp message to user
                            $pesan = [
                                            "messaging_product" => "whatsapp",
                                            "to" => $userInsertNoHp,
                                            "type" => "template",
                                            "template" => [
                                                "name" => "notif_approver_hcis",
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
                                                                "text" => $user->Nama
                                                            ],
                                                            [
                                                                "type" => "text",
                                                                "text" => $noFaktur
                                                            ],
                                                            [
                                                                "type" => "text",
                                                                "text" => $this->parseIzin($jenisIzin)
                                                            ],
                                                            [
                                                                "type" => "text",
                                                                "text" => $this->formatTanggal($validatedData['tanggal'][0], $validatedData['tanggal'][1])

                                                            ],
                                                            [
                                                                "type" => "text",
                                                                "text" => Auth::user()->karyawan->Nama
                                                            ]
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ];


                                $response = Whatsapp::send_message($pesan);
                                Log::channel('waIzinLog')->warning('Pesan Error', [
                                    "pesan" => $response
                                ]);


                                if($response){
                                    DB::table('HRIS_Approval_Request as a')
                                        ->join('HRIS_Approval_Flow as b', 'a.Flow_Id', '=', 'b.id')
                                        ->where("a.No_Transaksi", $noFaktur)
                                        ->whereNull("b.Status")
                                        ->where("b.Kode_Karyawan", $user->Kode_Karyawan)
                                        ->update(['a.angka_wa' => DB::raw('COALESCE(angka_wa, 0) + 1')]);
                                }

                            }
                }
                DB::table('Transaksi_Sakit_Izin')->insert($transaksi);
                $Detail_Id = DB::table('Transaksi_Sakit_Izin_Detail')->insertGetId($transaksi_detail);

                if($tanggalSakitIzinDari != $tanggalSakitIzinSampai){
                    $start = Carbon::createFromFormat('Y-m-d H:i', $tanggalSakitIzinDari);
                    $end = Carbon::createFromFormat('Y-m-d H:i', $tanggalSakitIzinSampai);

                    $currentDate = $start->copy();
                    while ($currentDate->lte($end)) {
                         $transaksi_det = [
                            'Kode_Perusahaan' => $kodePerusahaan,
                            'No_Transaksi' => $noFaktur,
                            'Kode_Karyawan' => $kodeKaryawan,
                            'Urut_Detail' => $Detail_Id,
                            'Tanggal_Sakit_Izin' => $currentDate,
                        ];
                    DB::table('Transaksi_Sakit_Izin_Det')->insert($transaksi_det);
                        $currentDate->addDay();
                    }
                }else{
                    $transaksi_det = [
                            'Kode_Perusahaan' => $kodePerusahaan,
                            'No_Transaksi' => $noFaktur,
                            'Kode_Karyawan' => $kodeKaryawan,
                            'Urut_Detail' => $Detail_Id,
                            'Tanggal_Sakit_Izin' => $tanggalSakitIzinDari,
                        ];
                    DB::table('Transaksi_Sakit_Izin_Det')->insert($transaksi_det);
                }



            }else if($validatedData['jenisIzin'] == 'cuti' || $validatedData['jenisIzin'] == 'cutiHutang'){
                $formatTanggalSekarang = Carbon::now()->format('m-y');
                $prefix = "CT" . $formatTanggalSekarang . "-";

                $checkLastFaktur = DB::table('Transaksi_Cuti')
                                        ->select(DB::raw('RIGHT(No_Transaksi, 4) AS angka'))
                                        ->where('No_Transaksi', 'like', $prefix . '%')
                                        ->orderByDesc(DB::raw('RIGHT(No_Transaksi, 4)'))
                                        ->first();

                $angkaTerakhir = $checkLastFaktur ? (int)$checkLastFaktur->angka + 1 : 1;
                $noFaktur = $prefix . sprintf('%04d', $angkaTerakhir);
                // Akhir logika pembuatan nomor faktur

                $kodePerusahaan = '001';
                $tanggalSaatIni = date('Y-m-d');
                $jamSaatIni = date('H:i:s');
                $userID = Auth::id();
                $kodeKaryawan = Auth::user()->karyawan->Kode_Karyawan;
                $jenisIzin = $validatedData['jenisIzin'];
                $tanggalSakitIzinDari = $validatedData['tanggal'][0] . ' ' . $validatedData['waktu'];
                $tanggalSakitIzinSampai = $validatedData['tanggal'][1] . ' ' . $validatedData['waktu'];
                $alasan = $validatedData['Alasan'];

                $filePath = null;
                if ($request->hasFile('file')) {
                    $file = $request->file('file');
                    $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $folderPath = 'izinSakitCuti/' . date('Y/m');
                    $filePath = $file->storeAs($folderPath, $fileName, 'public');
                }
                if($filePath !== null){
                    Storage::disk('gcs')->putFileAs($folderPath, $file, $fileName);
                }


                $transaksi = [
                    'Kode_Perusahaan' => $kodePerusahaan,
                    'No_Transaksi' => $noFaktur,
                    'Tanggal' => $tanggalSaatIni,
                    'Jam' => $jamSaatIni,
                    'UserID' => $userID,

                ];

                $transaksi_detail = [
                    'Kode_Perusahaan' => $kodePerusahaan,
                    'No_Transaksi' => $noFaktur,
                    'Kode_Karyawan' => $kodeKaryawan,
                    'Jenis' => $jenisIzin,
                    'Tanggal_Cuti_Dari' => $tanggalSakitIzinDari,
                    'Tanggal_Cuti_Sampai' => $tanggalSakitIzinSampai,
                    'Alasan' => $alasan,
                    'filePath' => $filePath != "" ? $filePath : null,
                ];
                $Division_Id =  Auth::user()->divisionKaryawan;
                $flow = DB::table('HRIS_Approval_flow')
                ->whereNull("Status")
                ->where("Kode_Karyawan_Requester", $kodeKaryawan)
                ->get()
                ->map(function ($item) use ($kodeKaryawan, $noFaktur) {
                    return [
                        'Kode_Karyawan' => $kodeKaryawan,
                        'update_at' => date('Y-m-d H:i:s'),
                        'Flow_Id' => $item->id,
                        'No_Transaksi' => $noFaktur,
                    ];
                });

                $inserFlowtoRequest = DB::table('HRIS_Approval_Request')->insert($flow->toArray());
                if($inserFlowtoRequest){
                    $HPflow = DB::table('HRIS_Approval_flow as a')
                    ->select("b.HP", "b.Nama", "b.Kode_Karyawan")
                    ->join('Karyawan as b', 'a.Kode_Karyawan' , '=','b.Kode_Karyawan')
                    ->where('a.Kode_Karyawan_Requester', $kodeKaryawan)
                    ->whereNull("a.Status")
                    ->orderBy("a.order_flow")
                    ->first();

                    // dd($HPflow);
                    //    dd($item->Kode_Karyawan);
                            $user = DB::table('Karyawan')
                                ->where('Kode_Karyawan', $HPflow->Kode_Karyawan)
                                ->first();
                            $userInsertNoHp = $user->HP ?? null;
                                // dd($userInsertNoHp);
                        if(!$userInsertNoHp || $userInsertNoHp == ' ' || $userInsertNoHp == '-' ){
                            Log::channel('waIzinPage')->warning('Pesan Error', [
                                    "pesan" => "Karyawan {$user->Nama} Tidak ada Nomor Hp untuk noitifikasi Transaksi {$noFaktur}"
                                ]);

                            }
                            // whatsapp message to user
                            $pesan = [
                                            "messaging_product" => "whatsapp",
                                            "to" => $userInsertNoHp,
                                            "type" => "template",
                                            "template" => [
                                                "name" => "notif_approver_hcis",
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
                                                                "text" => $user->Nama
                                                            ],
                                                            [
                                                                "type" => "text",
                                                                "text" => $noFaktur
                                                            ],
                                                            [
                                                                "type" => "text",
                                                                "text" => $this->parseIzin($jenisIzin)
                                                            ],
                                                            [
                                                                "type" => "text",
                                                                "text" => $this->formatTanggal($validatedData['tanggal'][0], $validatedData['tanggal'][1])
                                                            ],
                                                            [
                                                                "type" => "text",
                                                                "text" => Auth::user()->karyawan->Nama
                                                            ]
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ];


                                $response = Whatsapp::send_message($pesan);
                                Log::channel('waIzinPage')->warning('Pesan Error', [
                                    "pesan" => $response
                                ]);
                                if($response){
                                    DB::table('HRIS_Approval_Request as a')
                                        ->join('HRIS_Approval_Flow as b', 'a.Flow_Id', '=', 'b.id')
                                        ->where("a.No_Transaksi", $noFaktur)
                                        ->whereNull("b.Status")
                                        ->where("b.Kode_Karyawan", $user->Kode_Karyawan)
                                        ->update(['a.angka_wa' => DB::raw('COALESCE(angka_wa, 0) + 1')]);
                                }

                }
                DB::table('Transaksi_Cuti')->insert($transaksi);
                $Detail_Id = DB::table('Transaksi_Cuti_Detail')->insertGetId($transaksi_detail);

                if($tanggalSakitIzinDari != $tanggalSakitIzinSampai){
                    $start = Carbon::createFromFormat('Y-m-d H:i:s', $tanggalSakitIzinDari);
                    $end = Carbon::createFromFormat('Y-m-d H:i:s', $tanggalSakitIzinSampai);

                    $currentDate = $start->copy();
                    while ($currentDate->lte($end)) {
                         $transaksi_det = [
                            'Kode_Perusahaan' => $kodePerusahaan,
                            'No_Transaksi' => $noFaktur,
                            'Kode_Karyawan' => $kodeKaryawan,
                            'Urut_Detail' => $Detail_Id,
                            'Tanggal_Cuti' => $currentDate,
                        ];
                    DB::table('Transaksi_Cuti_Det')->insert($transaksi_det);
                        $currentDate->addDay();
                    }
                }else{
                    $transaksi_det = [
                        'Kode_Perusahaan' => $kodePerusahaan,
                        'No_Transaksi' => $noFaktur,
                        'Kode_Karyawan' => $kodeKaryawan,
                        'Urut_Detail' => $Detail_Id,
                        'Tanggal_Cuti' => $tanggalSakitIzinDari,
                    ];
                    DB::table('Transaksi_Cuti_Det')->insert($transaksi_det);
                }

            }else{
                 // Logika pembuatan nomor faktur langsung di sini

                $Jam_Masuk_Real = $request->jam_masuk ?? null;
                $Jam_Keluar_Real = $request->jam_keluar ?? null;
                // dd($Jam_Masuk_Real, $Jam_Keluar_Real);
                $formatTanggalSekarang = Carbon::now()->format('m-y');
                $prefix = "TP" . $formatTanggalSekarang . "-";

                $checkLastFaktur = DB::table('Transaksi_Terlambat_Pulang_Cepat')
                                        ->select(DB::raw('RIGHT(No_Transaksi, 4) AS angka'))
                                        ->where('No_Transaksi', 'like', $prefix . '%')
                                        ->orderByDesc(DB::raw('RIGHT(No_Transaksi, 4)'))
                                        ->first();

                $angkaTerakhir = $checkLastFaktur ? (int)$checkLastFaktur->angka + 1 : 1;
                $noFaktur = $prefix . sprintf('%04d', $angkaTerakhir);
                // Akhir logika pembuatan nomor faktur

                $kodePerusahaan = '001';
                $tanggalSaatIni = date('Y-m-d');
                $jamSaatIni = date('H:i:s');
                $userID = Auth::id();
                $kodeKaryawan = Auth::user()->karyawan->Kode_Karyawan;
                $jenisIzin = $validatedData['jenisIzin'];
                $alasan = $validatedData['Alasan'];

                // $if($Jam_Keluar_Real < $Jam_Masuk_Real){
                //     $tanggalSakitIzinDari = $validatedData['tanggal'][0] ;

                // }else{

                // }

                // $jamMasuk   = Carbon::createFromFormat('H:i', $Jam_Masuk_Real);
                // $jamKeluar  = Carbon::createFromFormat('H:i', $Jam_Keluar_Real);
                // $jamIzin    = Carbon::createFromFormat('H:i', $validatedData['waktu']);
                $jamMasuk  = $this->parseJam($Jam_Masuk_Real);
                $jamKeluar = $this->parseJam($Jam_Keluar_Real);
                $jamIzin   = $this->parseJam($validatedData['waktu']);


                $tanggal = Carbon::parse($validatedData['tanggal'][0]);
                // dd($jamMasuk, $jamKeluar, $jamIzin, $tanggal);

                if ($jamKeluar->lessThan($jamMasuk)) {
                    // ✅ Shift malam (jam keluar < jam masuk, misalnya 19:00 – 01:00)
                    if ($jamIzin->lessThan($jamKeluar)) {
                        // izin jam lewat tengah malam → tambah 1 hari
                        $tanggalSakitIzinDari = $tanggal->copy()->addDay()->format('Y-m-d');
                    } else {
                        // izin jam di hari yang sama
                        $tanggalSakitIzinDari = $tanggal->format('Y-m-d');
                    }
                } else {
                    // ✅ Shift normal (jam keluar > jam masuk, misalnya 08:00 – 17:00)
                    $tanggalSakitIzinDari = $tanggal->format('Y-m-d');
                }


                // dd($tanggalSakitIzinDari, $validatedData['waktu']);



                $tanggalSakitIzinSampai = $validatedData['tanggal'][1] . ' ' . $validatedData['waktu'];
                $filePath = null;
                if ($request->hasFile('file')) {
                    $file = $request->file('file');
                    $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $folderPath = 'izinSakitCuti/' . date('Y/m');
                    $filePath = $file->storeAs($folderPath, $fileName, 'public');
                }

                if($filePath !== null){
                    Storage::disk('gcs')->putFileAs($folderPath, $file, $fileName);
                }

                $transaksi = [
                    'Kode_Perusahaan' => $kodePerusahaan,
                    'No_Transaksi' => $noFaktur,
                    'Tanggal' => $tanggalSaatIni,
                    'Jam' => $jamSaatIni,
                    'UserID' => $userID,

                ];

                $transaksi_detail = [
                    'Kode_Perusahaan' => $kodePerusahaan,
                    'No_Transaksi' => $noFaktur,
                    'Kode_Karyawan' => $kodeKaryawan,
                    'Jenis' => $jenisIzin,
                    'Tanggal_Masuk_Pulang' => $tanggalSakitIzinDari,
                    'Jam' => $validatedData['waktu'],
                    'Alasan' => $alasan,
                    'filePath' => $filePath != "" ? $filePath : null,
                ];

                $Division_Id =  Auth::user()->divisionKaryawan;
                $flow = DB::table('HRIS_Approval_flow')
                ->whereNull("Status")
                ->where("Kode_Karyawan_Requester", $kodeKaryawan)
                ->get()
                ->map(function ($item) use ($kodeKaryawan, $noFaktur) {
                    return [
                        'Kode_Karyawan' => $kodeKaryawan,
                        'update_at' => date('Y-m-d H:i:s'),
                        'Flow_Id' => $item->id,
                        'No_Transaksi' => $noFaktur,
                    ];
                });

                $inserFlowtoRequest = DB::table('HRIS_Approval_Request')->insert($flow->toArray());
                if($flow){
                     $HPflow = DB::table('HRIS_Approval_flow as a')
                    ->select("b.HP", "b.Nama", "b.Kode_Karyawan")
                    ->join('Karyawan as b', 'a.Kode_Karyawan' , '=','b.Kode_Karyawan')
                    ->where('a.Kode_Karyawan_Requester', $kodeKaryawan)
                    ->whereNull("a.Status")
                    ->orderBy("a.order_flow")
                    ->first();



                            $user = DB::table('Karyawan')
                                ->where('Kode_Karyawan', $HPflow->Kode_Karyawan)
                                ->first();

                            $userInsertNoHp = $user->HP ?? null;
                        if(!$userInsertNoHp || $userInsertNoHp == ' ' || $userInsertNoHp == '-' ){
                            Log::channel('waIzinPage')->warning('Pesan Error', [
                                    "pesan" => "Karyawan {$user->Nama} Tidak ada Nomor Hp untuk noitifikasi Transaksi {$noFaktur}"
                                ]);

                            }
                            // whatsapp message to user
                            $pesan = [
                                            "messaging_product" => "whatsapp",
                                            "to" => $userInsertNoHp,
                                            "type" => "template",
                                            "template" => [
                                                "name" => "notif_approver_hcis",
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
                                                                "text" => $user->Nama
                                                            ],
                                                            [
                                                                "type" => "text",
                                                                "text" => $noFaktur
                                                            ],
                                                            [
                                                                "type" => "text",
                                                                "text" => $this->parseIzin($jenisIzin)
                                                            ],
                                                            [
                                                                "type" => "text",
                                                                "text" => $this->formatTanggal($tanggalSakitIzinDari, null, $validatedData['waktu'])
                                                            ],
                                                            [
                                                                "type" => "text",
                                                                "text" => Auth::user()->karyawan->Nama
                                                            ]
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ];


                                $response = Whatsapp::send_message($pesan);
                                Log::channel('waIzinPage')->warning('Pesan Error', [
                                    "pesan" => $response
                                ]);

                                if($response){
                                    DB::table('HRIS_Approval_Request as a')
                                        ->join('HRIS_Approval_Flow as b', 'a.Flow_Id', '=', 'b.id')
                                        ->where("a.No_Transaksi", $noFaktur)
                                        ->whereNull("b.Status")
                                        ->where("b.Kode_Karyawan", $user->Kode_Karyawan)
                                        ->update(['a.angka_wa' => DB::raw('COALESCE(angka_wa, 0) + 1')]);
                                }

                }
                DB::table('Transaksi_Terlambat_Pulang_Cepat')->insert($transaksi);
                DB::table('Transaksi_Terlambat_Pulang_Cepat_Detail')->insert($transaksi_detail);

            }



            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Data izin berhasil disimpan.',
                'no_transaksi' => $noFaktur
            ], 200);

        } catch (\Throwable $e) {
            DB::rollback();
            Log::channel('izinLog')->error('Gagal Menyimpan data submitIzin: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan pada server. Coba lagi. '
            ], 500);
        }
    }


    function formatTanggal($start, $end, $waktu = null) {
        Carbon::setLocale('id');

        // Kalau ada waktu, berarti single date + time
        if (!empty($waktu)) {
            return Carbon::parse($start)->translatedFormat('d M Y') . ' ' . $waktu;
        }

        // Kalau tidak ada waktu → berarti range
        if (!empty($start) && !empty($end)) {
            $startDate = Carbon::parse($start);
            $endDate   = Carbon::parse($end);

            if ($startDate->equalTo($endDate)) {
                // Hanya satu hari
                return $startDate->translatedFormat('d M Y');
            } elseif ($startDate->month === $endDate->month && $startDate->year === $endDate->year) {
                // Range dalam bulan & tahun yang sama
                return $startDate->format('d') . '-' . $endDate->format('d') . ' ' . $endDate->translatedFormat('M Y');
            } elseif ($startDate->year === $endDate->year) {
                // Range beda bulan tapi masih di tahun yang sama
                return $startDate->translatedFormat('d M') . ' - ' . $endDate->translatedFormat('d M Y');
            } else {
                // Range beda tahun
                return $startDate->translatedFormat('d M Y') . ' - ' . $endDate->translatedFormat('d M Y');
            }
        }

        return null;
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
            Log::channel('absensiLog')->error('Error saat mengambil data shift absen: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Gagal mengambil data. Silakan periksa koneksi Anda. '
            ], 500);
        }
    }

    public function getJamKerja(Request $request)
    {
        try {
            $Tanggal = date('Y-m-d', strtotime($request[0]));
            $Hari = date('N', strtotime($Tanggal));
            if ($Hari <= 6) {
                $Hari += 1;
            } elseif ($Hari == 7) {
                $Hari = 1;
            }

            $Kode_Karyawan = Auth::user()->karyawan->Kode_Karyawan;

            $query = "
                WITH KaryawanShiftPermanen AS (
                    SELECT
                        e.Kode_Karyawan,
                        c.Jam_Masuk,
                        c.Jam_Keluar,
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
                    WHERE e.Kode_Perusahaan = '001'
                    AND b.Hari = ?
                    AND e.Kode_Karyawan = ?
                    AND d.Periode <= ?
                ),
                ShiftPermanen AS (
                    SELECT
                        Kode_Karyawan,
                        Jam_Masuk,
                        Jam_Keluar
                    FROM KaryawanShiftPermanen
                    WHERE rn_periode = 1
                ),
                ShiftSementara AS (
                    SELECT TOP 1
                        e.Kode_Karyawan,
                        c.Jam_Masuk,
                        c.Jam_Keluar
                    FROM HRIS_Shift_Kerja a
                    INNER JOIN HRIS_Shift_Kerja_Detail b ON a.ID_Shift = b.ID_Shift
                    INNER JOIN HRIS_Waktu_Kerja c ON b.ID_Waktu_Kerja = c.ID_Waktu_Kerja
                    INNER JOIN HRIS_Shift_Sementara d ON a.ID_Shift = d.ID_Shift
                    INNER JOIN Karyawan e ON d.Kode_Perusahaan = e.Kode_Perusahaan
                                        AND d.Kode_Karyawan = e.Kode_Karyawan
                    WHERE e.Kode_Perusahaan = '001'
                    AND b.Hari = ?
                    AND e.Kode_Karyawan = ?
                    AND d.Tanggal = ?
                    ORDER BY d.Urut DESC, d.Tanggal DESC
                )
                SELECT
                    COALESCE(ss.Kode_Karyawan, sp.Kode_Karyawan) as Kode_Karyawan,
                    COALESCE(ss.Jam_Masuk, sp.Jam_Masuk) as Jam_Masuk,
                    COALESCE(ss.Jam_Keluar, sp.Jam_Keluar) as Jam_Keluar
                FROM ShiftPermanen sp
                FULL OUTER JOIN ShiftSementara ss ON sp.Kode_Karyawan = ss.Kode_Karyawan
            ";

            $result = DB::select($query, [$Hari, $Kode_Karyawan, $Tanggal, $Hari, $Kode_Karyawan, $Tanggal]);

            $dataFinal = collect($result)->first();

            $Jam_Masuk = $dataFinal->Jam_Masuk ?? '08:30';
            $Jam_Keluar = $dataFinal->Jam_Keluar ?? '16:30';



            return response()->json([
                'status' => 200,
                'data' => [
                    'Jam_Masuk' => $Jam_Masuk,
                    'Jam_Keluar' => $Jam_Keluar,

                ]
            ]);
        } catch (\Throwable $e) {
            Log::channel('izinLog')->error('Gagal Mengambil data jam Kerja getJamKerja ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'error' => "Tidak bisa mengambil data jam Kerja "
            ], 500);
        }
    }


    public function indexIzinAdmin()
    {
        try{
            $Hari = date('N');
            if ($Hari <= 6 ) {
                $Hari += 1 ;
                } elseif ($Hari == 7) {
                    $Hari = 1;
                }
            $Tanggal = date('Y-m-d');
            $Kode_Karyawan = Auth::user()->karyawan->Kode_Karyawan;
            $nama_Karyawan = Auth::user()->karyawan->Nama;
            // dd($nama_Karyawan);
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
                    WHERE e.Kode_Perusahaan = '001'
                    AND b.Hari = ?
                    AND e.Kode_Karyawan = ?
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
                ShiftSementara AS (
                    SELECT
                    TOP 1
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
                        COALESCE(NULLIF(c.Jam_Selesai_Lintas_Hari, ''), NULLIF(c.Jam_Selesai_Absen_Keluar, '')) AS Jam_selesai_absen
                    FROM HRIS_Shift_Kerja a
                    INNER JOIN HRIS_Shift_Kerja_Detail b ON a.ID_Shift = b.ID_Shift
                    INNER JOIN HRIS_Waktu_Kerja c ON b.ID_Waktu_Kerja = c.ID_Waktu_Kerja
                    INNER JOIN HRIS_Shift_Sementara d ON a.ID_Shift = d.ID_Shift
                    INNER JOIN Karyawan e ON d.Kode_Perusahaan = e.Kode_Perusahaan
                                        AND d.Kode_Karyawan = e.Kode_Karyawan
                    INNER JOIN View_Divisi_Sub_Divisi f ON e.ID_Divisi_Sub_Divisi = f.ID_DIVISI_SUB_DIVISI
                    INNER JOIN View_Golongan_Sub_Golongan_Level_Jabatan g ON e.ID_Level_Jabatan = g.ID_Level_Jabatan
                    WHERE e.Kode_Perusahaan = '001'
                    AND b.Hari = ?
                    AND e.Kode_Karyawan = ?
                    AND d.Tanggal = ?
                    ORDER BY d.Urut DESC, d.Tanggal DESC
                )
                SELECT
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
            ";

            $result = DB::select($query, [$Hari, $Kode_Karyawan, $Tanggal, $Hari, $Kode_Karyawan, $Tanggal]);

            $dataFinal = collect($result)->map(function ($item) {
                if (!empty($item->UserID_Absen)) {
                    $item->UserID_Absen = Crypt::encryptString($item->UserID_Absen);
                }
                return $item;
            })->first();


            $IzinApprover = DB::table('HRIS_Approval_Flow as h')
                ->join('Karyawan as k', 'k.Kode_Karyawan', '=', 'h.Kode_Karyawan')
                ->where('h.Kode_Karyawan_Requester', $Kode_Karyawan)
                ->whereNull('h.Status')
                ->select('h.*', 'k.Nama')
                ->orderBy("h.order_flow")
                ->get();

            return inertia('izinPageAdmin', [
                'dataShift' => $dataFinal,
                'namaKaryawan' => $nama_Karyawan,
                'IzinApprover' =>$IzinApprover ?? null

            ]);
        }catch (\Throwable $e) {
            Log::channel('izinLog')->error('Gagal menampilkan page izin: ' . $e->getMessage());
            Alert::error('error', 'Terjadi Error saat menampilkan page izin!');
            return back();
        }
    }


    public function indexIzin()
    {
        try{


            $Hari = date('N');
            if ($Hari <= 6 ) {
                $Hari += 1 ;
                } elseif ($Hari == 7) {
                    $Hari = 1;
                }
            $Tanggal = date('Y-m-d');
            $Kode_Karyawan = Auth::user()->karyawan->Kode_Karyawan;
            $nama_Karyawan = Auth::user()->karyawan->Nama;
            // dd($nama_Karyawan);
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
                    WHERE e.Kode_Perusahaan = '001'
                    AND b.Hari = ?
                    AND e.Kode_Karyawan = ?
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
                ShiftSementara AS (
                    SELECT
                    TOP 1
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
                        COALESCE(NULLIF(c.Jam_Selesai_Lintas_Hari, ''), NULLIF(c.Jam_Selesai_Absen_Keluar, '')) AS Jam_selesai_absen
                    FROM HRIS_Shift_Kerja a
                    INNER JOIN HRIS_Shift_Kerja_Detail b ON a.ID_Shift = b.ID_Shift
                    INNER JOIN HRIS_Waktu_Kerja c ON b.ID_Waktu_Kerja = c.ID_Waktu_Kerja
                    INNER JOIN HRIS_Shift_Sementara d ON a.ID_Shift = d.ID_Shift
                    INNER JOIN Karyawan e ON d.Kode_Perusahaan = e.Kode_Perusahaan
                                        AND d.Kode_Karyawan = e.Kode_Karyawan
                    INNER JOIN View_Divisi_Sub_Divisi f ON e.ID_Divisi_Sub_Divisi = f.ID_DIVISI_SUB_DIVISI
                    INNER JOIN View_Golongan_Sub_Golongan_Level_Jabatan g ON e.ID_Level_Jabatan = g.ID_Level_Jabatan
                    WHERE e.Kode_Perusahaan = '001'
                    AND b.Hari = ?
                    AND e.Kode_Karyawan = ?
                    AND d.Tanggal = ?
                    ORDER BY d.Urut DESC, d.Tanggal DESC
                )
                SELECT
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
            ";

            $result = DB::select($query, [$Hari, $Kode_Karyawan, $Tanggal, $Hari, $Kode_Karyawan, $Tanggal]);

            $dataFinal = collect($result)->map(function ($item) {
                if (!empty($item->UserID_Absen)) {
                    $item->UserID_Absen = Crypt::encryptString($item->UserID_Absen);
                }
                return $item;
            })->first();


            $IzinApprover = DB::table('HRIS_Approval_Flow as h')
                ->join('Karyawan as k', 'k.Kode_Karyawan', '=', 'h.Kode_Karyawan')
                ->where('h.Kode_Karyawan_Requester', $Kode_Karyawan)
                ->whereNull('h.Status')
                ->select('h.*', 'k.Nama')
                ->orderBy("h.order_flow")
                ->get();

            return inertia('izinPage', [
                'dataShift' => $dataFinal,
                'namaKaryawan' => $nama_Karyawan,
                'IzinApprover' =>$IzinApprover ?? null

            ]);
        }catch (\Throwable $e) {
            Log::channel('izinLog')->error('Gagal menampilkan page izin: ' . $e->getMessage());
            Alert::error('error', 'Terjadi Error saat menampilkan page izin!');
            return back();
        }
    }

    public function indexIzinDH()
    {

        try{


            $Hari = date('N');
            if ($Hari <= 6 ) {
                $Hari += 1 ;
                } elseif ($Hari == 7) {
                    $Hari = 1;
                }
            $Tanggal = date('Y-m-d');
            $Kode_Karyawan = Auth::user()->karyawan->Kode_Karyawan;
            $nama_Karyawan = Auth::user()->karyawan->Nama;

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
                    WHERE e.Kode_Perusahaan = '001'
                    AND b.Hari = ?
                    AND e.Kode_Karyawan = ?
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
                ShiftSementara AS (
                    SELECT
                    TOP 1
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
                        COALESCE(NULLIF(c.Jam_Selesai_Lintas_Hari, ''), NULLIF(c.Jam_Selesai_Absen_Keluar, '')) AS Jam_selesai_absen
                    FROM HRIS_Shift_Kerja a
                    INNER JOIN HRIS_Shift_Kerja_Detail b ON a.ID_Shift = b.ID_Shift
                    INNER JOIN HRIS_Waktu_Kerja c ON b.ID_Waktu_Kerja = c.ID_Waktu_Kerja
                    INNER JOIN HRIS_Shift_Sementara d ON a.ID_Shift = d.ID_Shift
                    INNER JOIN Karyawan e ON d.Kode_Perusahaan = e.Kode_Perusahaan
                                        AND d.Kode_Karyawan = e.Kode_Karyawan
                    INNER JOIN View_Divisi_Sub_Divisi f ON e.ID_Divisi_Sub_Divisi = f.ID_DIVISI_SUB_DIVISI
                    INNER JOIN View_Golongan_Sub_Golongan_Level_Jabatan g ON e.ID_Level_Jabatan = g.ID_Level_Jabatan
                    WHERE e.Kode_Perusahaan = '001'
                    AND b.Hari = ?
                    AND e.Kode_Karyawan = ?
                    AND d.Tanggal = ?
                    ORDER BY d.Urut DESC, d.Tanggal DESC
                )
                SELECT
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
            ";

            $result = DB::select($query, [$Hari, $Kode_Karyawan, $Tanggal, $Hari, $Kode_Karyawan, $Tanggal]);

            $dataFinal = collect($result)->map(function ($item) {
                if (!empty($item->UserID_Absen)) {
                    $item->UserID_Absen = Crypt::encryptString($item->UserID_Absen);
                }
                return $item;
            })->first();




            return inertia('izinPageDH', [
                'dataShift' => $dataFinal,
                'namaKaryawan' => $nama_Karyawan,
            ]);
        }catch (\Throwable $e) {
            Log::channel('izinLog')->error('Gagal menampilkan page izinDH: ' . $e->getMessage());
            Alert::error('error', 'Terjadi Error saat menampilkan page izinDH!');
            return back();
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

    public function updateApproval(Request $request){
        try{

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
            Log::channel('absensiLog')->error('Tidak ada foto yang diupload');
            return response()->json([
                'status' => 400,
                'message' => 'Tidak ada foto yang diupload.'
            ], 400);
        }
        $lang = $request->input('location_longitude');
        $lat = $request->input('location_latitude');
        $accuracy = $request->input('accuracy');
        // if(!$lang || $lang == 0 || !$lat || !$lat == 0 || !$accuracy){
        //     Log::channel('absensiLog')->error('Tidak menyalakan lokasi');
        //     return response()->json([
        //         'status' => 400,
        //         'message' => 'Harap Nyalakan Lokasi Anda!'
        //     ], 400);
        // }
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
                // dd($Kode_Karyawan);
                if(!$UserID || !$UserID_Absen || !$Kode_Karyawan){
                    Log::channel('absenLog')->error('Tidak ada data User');

                    // dd($UserID, $UserID_Absen, $Kode_Karyawan);
                    return response()->json([
                    'status' => 500,
                    'message' => 'Tidak ada Data User'
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

                Log::channel('absensiLog')->error('Error menyimpan data absensi ke DB: ' . $dbException->getMessage(), [
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
            Log::channel('absensiLog')->error('Error saat upload foto absensi ke GCS: ' . $e->getMessage(), [
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


    public function destroyIzin(Request $request)
    {
        try {
            DB::beginTransaction();

            // ✅ Cek dulu apakah sudah ada approver yang flag 'Y'
            $sudahAdaApprove = DB::table('HRIS_Approval_Request')
                ->where('No_Transaksi', $request->No_Transaksi)
                ->whereNotNull('Flag_Approval')
                ->exists();

            if ($sudahAdaApprove) {
                DB::rollBack();
                return response()->json([
                    'status' => 400,
                    'message' => 'Data tidak bisa dihapus karena sudah ada approver yang menyetujui.'
                ], 400);
            }

            $recordsDeleted = 0;

            $recordsDeleted += DB::table('Transaksi_Sakit_Izin')
                ->where('No_Transaksi', $request->No_Transaksi)
                ->update(['Status' => 'Y']);

            $recordsDeleted += DB::table('Transaksi_Terlambat_Pulang_Cepat')
                ->where('No_Transaksi', $request->No_Transaksi)
                ->update(['Status' => 'Y']);

            $recordsDeleted += DB::table('Transaksi_Cuti')
                ->where('No_Transaksi', $request->No_Transaksi)
                ->update(['Status' => 'Y']);

            if ($recordsDeleted === 0) {
                DB::rollBack();
                Log::channel('izinLog')->error('Data tidak ditemukan');
                return response()->json([
                    'status' => 404,
                    'message' => 'Data tidak ditemukan.'
                ], 404);
            }

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => 'Berhasil menghapus data!'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::channel('izinLog')->error('Gagal menghapus data: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi. Detail: ' . $e->getMessage()
            ], 500);
        }
    }

}
