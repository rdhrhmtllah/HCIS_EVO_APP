<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Helpers\Whatsapp;
use Illuminate\Support\Facades\Log;

class BatasCutiAkhirTahun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cuti:cutiAkhirTahun';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command ini berjalan sekali setahun (pada 1 Januari) untuk menghanguskan dan membatasi sisa cuti. (TAHUNAN)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hari = now()->toDateString();
        $tahunProses = now()->subYear()->year;

        $this->info("Proses Check batas cuti akhir tahun {$tahunProses}");
        Log::channel('BatasCutiAkhirTahunLog')->info("Proses Check batas cuti akhir tahun {$tahunProses} pada {$hari}");
        $this->CutiHangus($tahunProses);
        $this->BatasiCuti($tahunProses);

        Log::channel('BatasCutiAkhirTahunLog')->info("Proses Pembatasan cuti akhir tahun {$tahunProses} pada {$hari} telah selesai!");
        $this->info("Proses Pembatasan cuti akhir tahun {$tahunProses} telah selesai!");
        return 0;
    }

    public function CutiHangus(int $year){

        $this->info("Mencari Cuti yang expired pada tanggal 31 desember {$year}......");
        Log::channel('BatasCutiAkhirTahunLog')->info("Mencari Cuti yang expired pada tanggal 31 desember {$year}......");
        $expiryDate = "{$year}-12-31";

        $hitungHangus = DB::table('HRIS_Buku_Cuti')
                        ->where('tanggal_expired', $expiryDate)
                        ->where('sisa_cuti', '>', 0)
                        ->update(['sisa_cuti' => 0, 'notes' => DB::raw("CONCAT(notes, ' - Hangus Otomatis')")]);

        Log::channel('BatasCutiAkhirTahunLog')->info("Proses selesai! Menghanguskan {$hitungHangus} cuti pada {$hari}");
        $this->info("Proses selesai! Menghanguskan {$hitungHangus} cuti");
    }

    public function BatasiCuti(int $year){
        $hari = now()->toDateString();

        $this->info("Proses Batasi maksimal cuti akhir tahun {$year}");
        Log::channel('BatasCutiAkhirTahunLog')->info("Proses Batasi maksimal cuti akhir tahun {$year} pada {$hari}");

        $Karyawans =  Karyawan::where('Tanggal_Resign', null)->get();
        $HitungMemotong = 0;

        foreach($Karyawans as $Karyawan){
            $cutiAkhirTahun = DB::table("HRIS_Buku_Cuti")
                                ->where("Kode_Karyawan", $Karyawan->Kode_Karyawan)
                                ->whereYear("tanggal_diberikan", $year)
                                ->where("sisa_cuti", ">", 0);

            $totalSekarang = (clone $cutiAkhirTahun)->sum("sisa_cuti");

            if($totalSekarang > 6){
                $jumlahDikurang = $totalSekarang - 6;

                $cutiDipotong = $cutiAkhirTahun->orderBy("tanggal_diberikan", "desc")->get();
                foreach($cutiDipotong as $cuti){

                    if($jumlahDikurang <= 0) break;

                    $deductible = min($jumlahDikurang, $cuti->sisa_cuti);
                    $newRemainingDays = $cuti->sisa_cuti - $deductible;

                    DB::table('HRIS_Buku_Cuti')
                        ->where('Id_Buku_Cuti', $cuti->Id_Buku_Cuti)
                        ->update(['sisa_cuti' => $newRemainingDays]);
                    $jumlahDikurang -= $deductible;
                }

                $this->line("Cuti dipotong untuk Karyawan {$Karyawan->Nama}. memotong mejadi ".($totalSekarang - 6)."Hari");
                Log::channel("Cuti dipotong untuk Karyawan {$Karyawan->Nama}. memotong mejadi ".($totalSekarang - 6)."Hari pada {$hari}");

                $HitungMemotong++;
            }
        }

        Log::channel("Pemotongan {$HitungMemotong} cuti karyawan telah selesai! pada {$hari}");
        $this->info("Pemotongan {$HitungMemotong} cuti karyawan telah selesai!");
    }
}
