<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Karyawan extends Model
{
    use HasFactory;
    protected $table = 'Karyawan';
    protected $primaryKey = 'Kode_Karyawan';
    public $incrementing = false; // Karena Kode_Karyawan mungkin bukan auto-incrementing integer
    protected $keyType = 'string';
    // protected
    protected $guarded = [];

    public $timestamps = false;

      public function user()
    {
        return $this->hasOne(User::class, 'UserID_Web', 'Id_Users');
    }

    public function division(){
    return $this->hasOne(View_Divisi_Sub_Divisi::class, 'ID_Divisi_Sub_Divisi','ID_Divisi_Sub_Divisi');
    }

    public function level(){
    return $this->hasOne(View_Golongan_Sub_Golongan_Level_Jabatan::class, 'ID_Level_Jabatan','ID_Level_Jabatan');
    }

    public function calculateLeaveBalance(): array
    {
        // Hitung total cuti yang tersedia
        $hitungCuti = DB::table('HRIS_Buku_Cuti')
                        ->where("Kode_Karyawan", $this->Kode_Karyawan)
                        ->where('tanggal_expired', '>=', now()->toDateString())
                        ->sum('sisa_cuti');

        // Hitung total hari cuti yang sedang diproses
        $allBookingTransaksi = DB::table('Transaksi_Cuti_Detail as a')
                            ->join("Transaksi_Cuti as b", 'a.No_Transaksi', '=', 'b.No_Transaksi')
                            ->where("a.Kode_Karyawan", $this->Kode_Karyawan)
                            ->where("a.Flag_Approval", null)
                            ->where("b.Status", null)
                            ->get();

        $totalHari = $allBookingTransaksi->sum(function($item) {
            return Carbon::parse($item->Tanggal_Cuti_Dari)
                ->diffInDays(Carbon::parse($item->Tanggal_Cuti_Sampai)) + 1;
        });

        $totalSaldo = $hitungCuti - $totalHari;

        return [
            'remaining' => max($totalSaldo, 0),
            'debt' => max(-$totalSaldo, 0)
        ];
    }

    public function sisa_cuti(): float
    {
        return $this->calculateLeaveBalance()['remaining'];
    }

    public function hutang_cuti(): float
    {
        return $this->calculateLeaveBalance()['debt'];
    }




}
