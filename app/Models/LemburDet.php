<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LemburDet extends Model
{
    use HasFactory;
    protected $table = 'Transaksi_Lembur_Det';
    // protected $primaryKey = 'Id_Cross_Review'
    protected $guarded =[];
    public $timestamps = false;
}
