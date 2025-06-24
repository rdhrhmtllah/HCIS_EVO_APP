<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


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





}
