<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Berjalan setiap tanggal 1 pada awal bulan
        // $schedule->command('cuti:tambahCuti')->monthly();

        // // Berjalan setiap hari pada pukul 00:00
        // $schedule->command('cuti:hakCutiTambahan')->daily();
        // $schedule->command('cuti:kuotaAwalCuti')->daily();
        // $schedule->command('cuti:cutiHangusHarian')->daily();
        $schedule->command('izin:waUlang')->daily();
        // $schedule->command('izin:waUlang')->everyMinute();

        $schedule->command('izin:expiredIzin')->dailyAt('23:59');
        $schedule->command('absen:flagCheckinout')->hourly();
        // Berjalan setiap akhir tahun pada 31 Desember
        // $schedule->command('cuti:cutiAkhirTahun')->yearlyOn(12, 31, '23:59');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
