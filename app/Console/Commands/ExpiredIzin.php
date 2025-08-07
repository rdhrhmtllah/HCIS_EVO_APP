<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\Whatsapp;


class ExpiredIzin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'izin:expiredIzin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'command ini berfungsi untuk memflag izin yang sudah lebih dari 2 hari tidak di acc oleh approver (HARIAN)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hari = now()->toDateString();
        $KmrnLusa = Carbon::today()->subDay(2);
        // dd($KmrnLusa);
        $this->info("Memulai proses pencarian approval yang perlu diexpiredkan untuk tanggal {$KmrnLusa->toDateString()}.");
        Log::channel('expiredIzin')->info("Memulai proses pencarian approval yang perlu diexpiredkan untuk tanggal {$KmrnLusa->toDateString()} pada {$hari}.");

        try{

            $datasKemarin = DB::table("HRIS_Approval_Request")
            ->whereDate("created_at", Carbon::today()->subDay(2))
            ->where("Flag_Approval", null)
            // ->where("angka_wa", '>',2)
            ->get();
            // dd($datasKemarin);
            if($datasKemarin->isEmpty()){
                $this->warn("Tidak ada Transaksi yang expired pada tanggal {$KmrnLusa}");
                Log::channel('expiredIzin')->info("Tidak ada Transaksi yang expired pada tanggal {$KmrnLusa}, hari {$hari}");

                return 0;
            }

            $groupedNo_Transaksi = $datasKemarin->groupBy("No_Transaksi");

            foreach($groupedNo_Transaksi as $No_TransaksiId => $requests){
                // dd($requests);

                $prefix = substr($No_TransaksiId, 0, 2);
                    if($prefix == 'IS'){
                        $Izin = DB::table('Transaksi_Sakit_Izin_Detail')->where('No_Transaksi', $No_TransaksiId);
                        $Izin->update(['Flag_Approval' => 'P']);
                    }else if($prefix == 'TP'){
                        $Izin = DB::table('Transaksi_Terlambat_Pulang_Cepat_Detail')->where('No_Transaksi', $No_TransaksiId);
                        $Izin->update(['Flag_Approval' => 'P']);
                    }else if($prefix == 'CT'){
                        $Izin = DB::table('Transaksi_Cuti_Detail')->where('No_Transaksi', $No_TransaksiId);
                        $Izin->update(['Flag_Approval' => 'P']);
                    }else{
                        Log::channel('expiredIzin')->warning('No_Traksaksi tidak ditemukan untuk Transaksi terlambat,cuti, izin.', [
                        'No_Transaksi' => $No_TransaksiId,
                        ]);
                        continue;
                    }
                    $izinID = $Izin->first()->Kode_Karyawan;
                                // dd($izinID);

                    $requester = DB::table('Karyawan')
                                ->where('Kode_Karyawan', $izinID)
                                ->first();
                    $nomorHP = $requester->HP;
                    if (empty($nomorHP) || $nomorHP === '-') {
                        $this->warn("Tidak dapat mengirim pesan ke {$requester->Nama} karena tidak ada nomor HP yang valid untuk Transaksi: {$No_Transaksi}!");
                        Log::channel('expiredIzin')->warning('Nomor HP tidak ada atau tidak valid untuk approver.', [
                            'nama_approver' => $requester->Nama,
                            'kode_karyawan_approver' => $requester->Kode_Karyawan,
                            'nomor_hp_mentah' => $requester->HP,
                            'No_Transaksi' => $No_TransaksiId
                        ]);
                        continue;
                    }
                    $pesan = [
                    "messaging_product" => "whatsapp",
                    "to" => $nomorHP,
                    "type" => "template", // Menggunakan template jika Anda sudah punya template yang cocok
                    "template" => [
                        "name" => "confirm_izin_tolak", // Nama template Anda
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
                                        "text" => $requester->Nama// Parameter pertama untuk nama approver
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => $No_TransaksiId// Parameter pertama untuk nama approver
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => "Sistem karena lewat dari 2 hari"// Parameter pertama untuk nama approver
                                    ]
                                    // Anda mungkin perlu menambahkan parameter lain di sini
                                    // sesuai dengan template 'pakai_absen' Anda
                                    // Jika template Anda bisa menerima list ID, tambahkan di sini
                                    // Contoh:
                                    // [
                                    //     "type" => "text",
                                    //     "text" => $requestIdsString
                                    // ]
                                ]
                            ]
                        ]
                    ]
                ];



                try {
                    $response = Whatsapp::send_message($pesan);

                    if (isset($response['error'])) {
                        Log::channel('expiredIzin')->warning('Gagal mengirim pesan WhatsApp - Error API.', [
                            'nama_approver' => $requester->Nama,
                            'nomor_hp_approver' => $nomorHP,
                            'No_Transaksi' => $No_TransaksiId,
                            'Kode_Karyawan' => $izinID,
                            'payload_whatsapp_dikirim' => $pesan,
                            'respons_api_whatsapp' => $response,
                        ]);
                    } else {
                        Log::channel('expiredIzin')->info('Pesan WhatsApp berhasil dikirim.', [
                            'nama_approver' => $requester->Nama,
                            'nomor_hp_approver' => $nomorHP,
                            'No_Transaksi' => $No_TransaksiId,
                            'Kode_Karyawan' => $izinID,
                            'payload_whatsapp_dikirim' => $pesan,
                            'respons_api_whatsapp' => $response,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::channel('expiredIzin')->error('Terjadi pengecualian saat mengirim pesan WhatsApp.', [
                        'nama_approver' => $requester->Nama,
                        'nomor_hp_approver' => $nomorHP,
                        'No_Transaksi' => $No_TransaksiId,
                        'Kode_Karyawan' => $izinID,
                        'payload_whatsapp_dikirim' => $pesan,
                        'pesan_pengecualian' => $e->getMessage(),
                        'kode_pengecualian' => $e->getCode(),
                        'trace_pengecualian' => $e->getTraceAsString(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::channel('expiredIzin')->error('Kesalahan umum selama proses notifikasi WhatsApp.', [
                'tanggal_proses' => $KmrnLusa->toDateString(),
                'pesan_pengecualian' => $e->getMessage(),
                'kode_pengecualian' => $e->getCode(),
                'trace_pengecualian' => $e->getTraceAsString()
            ]);
            return 1;
        }

        $this->info("Proses notifikasi ulang WhatsApp selesai untuk tanggal {$KmrnLusa->toDateString()}.");
        Log::channel('expiredIzin')->info("Proses notifikasi ulang WhatsApp selesai untuk tanggal {$KmrnLusa->toDateString()}, hari {$hari}.");

        return 0;

    }


}


