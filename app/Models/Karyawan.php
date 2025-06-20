<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    use HasFactory;
    protected $table = 'Karyawan';
    // protected
    protected $guarded = [];

    public $timestamps = false;

      public function user()
    {
        // Parameter kedua adalah nama foreign key di tabel users
        return $this->hasOne(User::class, 'UserID_Web', 'Id_Users');
    }


}
