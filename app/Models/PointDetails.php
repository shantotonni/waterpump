<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointDetails extends Model
{
    use HasFactory;

    protected $table = 'PointDetails';
    public $timestamps = false;
    protected $primaryKey = "PointDetailsID";

    protected $fillable = [
        'StaffID',
        'ServiceMasterID',
        'Point',
        'Feedback',
        'EntryBy',
        'EntryDate'
    ];
}
