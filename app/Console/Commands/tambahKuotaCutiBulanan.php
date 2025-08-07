<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\Whatsapp;


class tambahKuotaCutiBulanan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cuti:tambahCuti';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perintah ini berfunsi untuk menambah kuota cuti karyawan secara otomatis sesuai dengan golongan nya (BULANAN)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hari = now()->toDateString();

        $this->info('Proses Penambahan Kuota cuti bulanan berlangsung...');
        Log::channel('tambahKuotaCutiBulanan')->info("Proses Penambahan Kuota cuti bulanan berlangsung...{$hari}");

        $bulanIni = now()->format('F Y');

        $Karyawans = Karyawan::where('Tanggal_Resign', null)->get();
        foreach($Karyawans as $Karyawan){
            $CutiTerakhir = DB::table('HRIS_buku_cuti')->where('Kode_Karyawan', $Karyawan->Kode_Karyawan)
                            ->where('notes', 'like', 'Kuota cuti rutin bulanan%')
                            ->latest('tanggal_diberikan')->first();


            if($CutiTerakhir && Carbon::parse($CutiTerakhir->tanggal_diberikan)->isSameMonth(now())){
                $this->warn("Sudah mendapatkan kuota cuti bulan {$bulanIni}");
                Log::channel('tambahKuotaCutiBulanan')->info("Sudah mendapatkan kuota cuti bulan $bulanIni...{$hari}");

                continue;
            }

            $TambahHari = 1;


            if($Karyawan->level->ID_Golongan == 6){
                if(now()->month >= 7){
                    $TambahHari = 2;
                }
            }

            DB::table('HRIS_buku_cuti')->insert([
                'Kode_Karyawan' => $Karyawan->Kode_Karyawan,
                'tipe_cuti' => 'Tahunan',
                'tanggal_diberikan' => now()->toDateString(),
                'tanggal_expired' => now()->addYear()->endOfYear()->toDateString(),
                'jumlah_awal_cuti' => $TambahHari,
                'sisa_cuti' => $TambahHari,
                'notes' => 'Kuota cuti rutin bulanan '. now()->format('F Y')
            ]);

            $this->line("Berhasil Menambahkan {$TambahHari} Cuti bulanan pada #{$Karyawan->Nama} ");
            Log::channel('tambahKuotaCutiBulanan')->info("Berhasil Menambahkan {$TambahHari} Cuti bulanan pada #{$Karyawan->Nama}...{$hari}");


        }
        $this->info('Proses penambahan cuti bulanan telah selesai');
        Log::channel('tambahKuotaCutiBulanan')->info("Proses penambahan cuti bulanan telah selesai...{$hari}");

        return 0;

    }
}
