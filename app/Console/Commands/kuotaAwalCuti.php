<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\Whatsapp;


class kuotaAwalCuti extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cuti:kuotaAwalCuti';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menambahkan 12 kuota cuti di 1 tahun pertama karyawan (HARIAN)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $hari = now()->toDateString();

        Log::channel('kuotaAwalCuti')->info("Proses aturan {$rule->nama_rule} untuk lebih dari {$rule->tahun_minimal} tahunProses Pemberian 12 kuota cuti pada {$HariIni} telah Dimulai!...{$hari}");

        $HariIni = now()->toDateString();
        $tanggalAwalJoin = now()->subYear()->toDateString();
        $Karyawans = Karyawan::where('Tanggal_Resign', null)->whereDate('Tgl_Masuk', $tanggalAwalJoin)->get();

        if($Karyawans->isEmpty()){
            $this->info("Tidak ada Karyawan yang pas 1 tahun pada {$HariIni} !");
            Log::channel('kuotaAwalCuti')->info("Tidak ada Karyawan yang pas 1 tahun pada...{$hari}");

            return 0;
        }

        foreach($Karyawans as $Karyawan){
            $sudahDiberi = DB::table('HRIS_buku_cuti')->where('notes', '12 Kuota cuti tahun pertama')->exists();
            if($sudahDiberi){
                $this->warn("12 Kuota Pertama #{$Karyawan->Kode_Karyawan} telah diberikan, dilewati");
                Log::channel('kuotaAwalCuti')->info("12 Kuota Pertama #{$Karyawan->Kode_Karyawan} telah diberikan, dilewati...{$hari}");

                continue;
            }

            DB::table('HRIS_buku_cuti')->insert([
                'Kode_Karyawan' => $Karyawan->Kode_Karyawan,
                'tipe_cuti' => 'Tahunan',
                'tanggal_diberikan' => now()->toDateString(),
                'tanggal_expired' => now()->addYear()->endOfYear()->toDateString(),
                'jumlah_awal_cuti' => 12,
                'sisa_cuti' => 12,
                'notes' => '12 Kuota cuti tahun pertama'
            ]);

            $this->info("Berhasil memberikan 12 kuota cuti pertama pada ${$Karyawan->Nama}.");
            Log::channel('kuotaAwalCuti')->info("Berhasil memberikan 12 kuota cuti pertama pada ${$Karyawan->Nama}...{$hari}");


        }
        $this->info("Proses Pemberian 12 kuota cuti pada {$HariIni} telah selesai!");
        Log::channel('kuotaAwalCuti')->info("Proses Pemberian 12 kuota cuti pada {$HariIni} telah selesai!...{$hari}");

        return 0;
    }
}
