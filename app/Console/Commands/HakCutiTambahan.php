<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\Whatsapp;

class HakCutiTambahan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cuti:hakCutiTambahan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menambahkan Cuti Tambahan sesuai dengan lama join dan juga golongan (HARIAN)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hari = now()->toDateString();
        $this->info('Mencari karaywan yang mendapatkan hak cuti tambahan...');
        Log::channel('HakCutiTambahan')->info("Mencari karaywan yang mendapatkan hak cuti tambahan...{$hari}");


        $rules = DB::table('HRIS_Hak_Cuti_Tambahan')->where('status', null)->get();

        if($rules->isEmpty()){
            $this->info('Tidak ada aturan hak cuti tambahan yang aktif');
            Log::channel('HakCutiTambahan')->info("idak ada aturan hak cuti tambahan yang aktif {$hari}");

            return 0;
        }


        foreach($rules as $rule){
            $this->line("Proses aturan {$rule->nama_rule} untuk lebih dari {$rule->tahun_minimal} tahun");
            Log::channel('HRIS_Hak_Cuti_Tambahan')->info("Proses aturan {$rule->nama_rule} untuk lebih dari {$rule->tahun_minimal} tahun...{$hari}");

            // $targetJoinDate = now()->subYears($rule->tahun_minimal)->toDateString();
            // $targetJoinDate = '2025-07-01';

            $Karyawans = DB::table('Karyawan AS a')
                            ->join("View_Golongan_Sub_Golongan_Level_Jabatan AS b", "a.ID_Level_Jabatan", "=", 'b.ID_Level_Jabatan')
                            ->where('a.Tanggal_Resign', null)
                            ->whereMonth("a.Tgl_Masuk", "<=", now()->month)
                            ->whereDay("a.Tgl_Masuk", now()->day)
                            ->where("a.Tgl_Masuk", "<=", now()->subYear($rule->tahun_minimal))
                            // ->where("status", 1) //Untuk Karyawnan tetap
                            ->whereBetween("b.ID_Golongan", [$rule->golongan_min_id, $rule->golongan_max_id])
                            ->where("Kode_Karyawan", "RIDHO RAHMAT")
                            ->get();
            // dd($Karyawans);

            if($Karyawans->isEmpty()){
                continue;
            }

            foreach($Karyawans as $Karyawan){
                $noteForThisYear =  $rule->nama_rule.'-'.now()->year;

                $sudahDikasih = DB::table('HRIS_Buku_Cuti')
                                ->where("notes", $noteForThisYear)
                                ->exists();
                if($sudahDikasih){
                    $this->warn("Karyawan #{$Karyawan->Nama} sudah diberikan hak tambahan tahun ini. skip...");
                    Log::channel('HRIS_Hak_Cuti_Tambahan')->info("Karyawan #{$Karyawan->Nama} sudah diberikan hak tambahan tahun ini. skip...{$hari}");

                    continue;
                }

                 DB::table('HRIS_buku_cuti')->insert([
                    'Kode_Karyawan' => $Karyawan->Kode_Karyawan,
                    'tipe_cuti' => 'Tambahan',
                    'tanggal_diberikan' => now()->toDateString(),
                    'tanggal_expired' => now()->addMonths($rule->masa_berlaku_cuti)->toDateString(),
                    'jumlah_awal_cuti' => $rule->tambahan_hari,
                    'sisa_cuti' => $rule->tambahan_hari,
                    'notes' => $noteForThisYear
                ]);

                $this->info("Telah memberikan tambahan {$rule->nama_rule} pada karyawan #{$Karyawan->Nama}");
                Log::channel('HRIS_Hak_Cuti_Tambahan')->info("Telah memberikan tambahan {$rule->nama_rule} pada karyawan #{$Karyawan->Nama}...{$hari}");


            }

        }
        $this->info("Penambahan Hak cuti tambahan telah selesai di proses.");
        Log::channel('HRIS_Hak_Cuti_Tambahan')->info("Penambahan Hak cuti tambahan telah selesai di proses...{$hari}");

        return 0;
    }
}
