<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\Whatsapp;

class flagCheckinout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'absen:flagCheckinout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command ini untuk flag checkin out untuk data terlambat dan pulang cepat';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hari = now()->toDateString();
        Log::channel("FlagCheckinout")->info("Proses Flaging Checkinout untuk terlambat dan pulang cepat pada {$hari} dimulai... ");
        try{
            $transaksiPulang = DB::table('Transaksi_Terlambat_Pulang_Cepat_Detail as a')
                        ->select("a.*")
                        ->join("Transaksi_Terlambat_Pulang_Cepat as b", 'a.No_Transaksi', '=', 'b.No_Transaksi')
                        ->whereNull("b.Status")
                        ->where('a.Jenis', 'pulangCepat')
                        ->where("a.Flag_Approval", 'Y')
                        ->whereNull("Flag_Checkinout")->get();
            $transaksiTerlambat = DB::table('Transaksi_Terlambat_Pulang_Cepat_Detail as a')
                        ->select("a.*")
                        ->join("Transaksi_Terlambat_Pulang_Cepat as b", 'a.No_Transaksi', '=', 'b.No_Transaksi')
                        ->whereNull("b.Status")
                        ->where('a.Jenis', 'terlambat')
                        ->where("a.Flag_Approval", 'Y')
                        ->whereNull("Flag_Checkinout")->get();



            // dd($transaksi);

            if(!empty($transaksiTerlambat)){
                foreach ($transaksiTerlambat as $t) {
                    $tanggal = date('Y-m-d', strtotime($t->Tanggal_Masuk_Pulang));

                    $UserID_Absen = DB::table("Karyawan")
                                    ->where("Kode_Karyawan", $t->Kode_Karyawan)
                                    ->first()->UserID_Absen;
                    $jamIzin = $t->Jam;

                    $Untuk_Tanggal = date('Y-m-d H:i:s', strtotime("$tanggal"));
                    // Range ±1 jam dari jam izin
                    $startTime = date('Y-m-d H:i:s', strtotime("$tanggal $jamIzin -1 hour"));
                    $endTime   = date('Y-m-d H:i:s', strtotime("$tanggal $jamIzin +1 hour"));

                    $checkout = DB::table('CheckInOut')
                        ->where('UserID', $UserID_Absen)
                        ->whereBetween('CHECKTIME', [$startTime, $endTime])
                        ->orderBy('CHECKTIME', 'asc') // urutkan kalau mau ambil terdekat
                        ->first();
                    $checktime = $checkout->CHECKTIME ?? null;
                    // dd($checktime);

                    $insertChecktime =  DB::table('CheckInOut')
                        ->where('UserID', $UserID_Absen)
                        ->whereBetween('CHECKTIME', [$startTime, $endTime])
                        ->orderBy('CHECKTIME', 'asc')
                        ->update([
                            'Alasan' => 'TERLAMBAT',
                            'Hasil' => 'Masuk',
                            'Untuk_Tanggal' => $Untuk_Tanggal
                        ]);

                    if($insertChecktime){
                        DB::table('Transaksi_Terlambat_Pulang_Cepat_Detail')
                        ->where('No_Transaksi', $t->No_Transaksi)
                        ->update(['Flag_Checkinout' => 'Y']);
                    }

                    // Debug hasil
                    Log::channel('FlagCheckinout')->info("No Transaksi: {$t->No_Transaksi} :Checktime = {$checktime}");
                    $this->info("No Transaksi: {$t->No_Transaksi} :Checktime = {$checktime}");
                }
            }

            if(!empty($transaksiPulang)){
                foreach ($transaksiPulang as $t) {
                    $tanggal = date('Y-m-d', strtotime($t->Tanggal_Masuk_Pulang));

                    $UserID_Absen = DB::table("Karyawan")
                                    ->where("Kode_Karyawan", $t->Kode_Karyawan)
                                    ->first()->UserID_Absen;
                    $jamIzin = $t->Jam;

                    $Untuk_Tanggal = date('Y-m-d H:i:s', strtotime("$tanggal"));
                    // Range ±1 jam dari jam izin
                    $startTime = date('Y-m-d H:i:s', strtotime("$tanggal $jamIzin -1 hour"));
                    $endTime   = date('Y-m-d H:i:s', strtotime("$tanggal $jamIzin +1 hour"));

                    $checkout = DB::table('CheckInOut')
                        ->where('UserID', $UserID_Absen)
                        ->whereBetween('CHECKTIME', [$startTime, $endTime])
                        ->orderBy('CHECKTIME', 'asc') // urutkan kalau mau ambil terdekat
                        ->first();
                    $checktime = $checkout->CHECKTIME ?? null;
                    // dd($checktime);

                    $insertChecktime =  DB::table('CheckInOut')
                        ->where('UserID', $UserID_Absen)
                        ->whereBetween('CHECKTIME', [$startTime, $endTime])
                        ->orderBy('CHECKTIME', 'asc')
                        ->update([
                            'Alasan' => 'PULANG CEPAT',
                            'Hasil' => 'Keluar',
                            'Untuk_Tanggal' => $Untuk_Tanggal
                        ]);
                    if($insertChecktime){
                        DB::table('Transaksi_Terlambat_Pulang_Cepat_Detail')
                        ->where('No_Transaksi', $t->No_Transaksi)
                        ->update(['Flag_Checkinout' => 'Y']);
                    }

                    // Debug hasil
                    Log::channel('FlagCheckinout')->info("No Transaksi: {$t->No_Transaksi} :Checktime = {$checktime}");
                    $this->info("No Transaksi: {$t->No_Transaksi} :Checktime = {$checktime}");
                }
            }

         } catch (\Exception $e) {
            Log::channel('FlagCheckinout')->error("Kesalahan umum proses notifikasi.  pada {$hari}", [
                'pesan_pengecualian' => $e->getMessage(),
            ]);
            return 1;
        }

        Log::channel('FlagCheckinout')->info("Proses Flaging Checkinout untuk terlambat dan pulang cepat pada {$hari} dimulai...");
        return 0;
    }
}
