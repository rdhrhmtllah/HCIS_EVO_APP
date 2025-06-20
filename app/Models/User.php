<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Level;
use App\Models\Division;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'KPI_Users';
    protected $primaryKey = 'Id_Users';

    public  $timestamps = false;

    protected $fillable = [
        'Username',
        'Password',
        'Role',
        'Email',
        'Kode_Users',
        'Address',
        'Division_Id',
        'Level_Id'
    ];

    protected $hidden = [
        'Password',
        'remember_token',
    ];


    public function getAuthPassword()
    {
        return $this->Password;
    }


    public function username()
    {
        return 'Username';
    }

    public function divisions(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'Division_Id', 'Id_Division');
    }

    public function levels(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'Level_Id', 'Id_Level');
    }

    public function reviewDetail(): BelongsTo
    {
        return $this->belongsTo(GlobalReviewDetail::class, 'Employee_Id','Id_Users');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'Id_Users', 'UserID_Web');
    }

    public function memilikiJabatan(string $page): bool
    {
        return DB::table('karyawan as a')
            ->join('View_Divisi_Sub_Divisi as b', 'a.ID_Divisi_Sub_Divisi', '=', 'b.ID_DIVISI_SUB_DIVISI')
            ->join('View_Golongan_Sub_Golongan_Level_Jabatan as c', 'a.ID_Level_Jabatan', '=', 'c.ID_Level_Jabatan')
            ->join('HRIS_Page_Access as d', function ($join) {
                $join->on('b.ID_Divisi', '=', 'd.ID_Divisi')
                     ->on('c.ID_Level', '=', 'd.ID_Level');
            })
            ->where('d.UserID_Web', $this->Id_Users)
            ->where('d.Jenis_page', $page)
            ->exists();
    }


    public function isSupervisor(): bool{
        return $this->memilikiJabatan('ShiftManagement');
    }

    public function openKPI(): bool{
        return false;
    }

    public function divisionKaryawan()
    {
        return $this->hasOneThrough(
            View_Divisi_Sub_Divisi::class,
            Karyawan::class,
            'UserID_Web',
            'ID_Divisi_Sub_Divisi',
            'Id_Users',
            'ID_DIVISI_SUB_DIVISI'
        );
    }
    public function JabatanKaryawan()
    {
        return $this->hasOneThrough(
            View_Golongan_Sub_Golongan_Level_Jabatan::class,
            Karyawan::class,
            'UserID_Web',
            'ID_Level_Jabatan',
            'Id_Users',
            'ID_Level_Jabatan'
        );
    }
}
