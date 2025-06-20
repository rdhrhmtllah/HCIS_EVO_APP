<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class GlobalReview extends Model
{
    use HasFactory;
    protected $table = 'KPI_Global_Review';
    // protected $primaryKey = 'Id_Global_Review';

    protected $fillable = [
        'No_Review',
        'Date',
        'Time',
        'User_Id_Review',
        'Id_Periode',
        'Division_Id'
    ];

    public $timestamps = false;

    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'User_Id_Review', '');
    // }

    public function reviewDetail(): HasMany{
        return $this->hasMany(GlobalReviewDetail::class, 'No_Review', 'No_Review');
    }

    public function periode(): BelongsTo{
        return $this->belongsTo(MasterPeriode::class, 'Id_Periode', 'Id_Periode');
    }

}
