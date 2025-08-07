<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Helpers\Whatsapp;
use Illuminate\Support\Facades\Log;

class cutiHangusHarian extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cuti:cutiHangusHarian';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'command ini mencari cuti yang expired setiap hari (HARIAN)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $HariIni = now()->toDateString();
        $this->info("Mencari cuti yang expired hari ini {$HariIni}.........");
        Log::channel('cutiHangusHarian')->info("Mencari cuti yang expired hari ini {$HariIni}.........");


        $hitungHangus = DB::table('HRIS_Buku_Cuti')
                        ->where("tanggal_expired", $HariIni)
                        ->where("sisa_cuti",">", 0)
                        ->update(['sisa_cuti' => 0, 'notes' => DB::raw("CONCAT(notes, ' - Hangus harian otomatis')")]);


        Log::channel('cutiHangusHarian')->info("Proses selesai, Telah Menghanguskan {$hitungHangus} Cuti pada {$HariIni}");
        $this->info("Proses selesai, Telah Menghanguskan {$hitungHangus} Cuti pada {$HariIni}");

    }
}
