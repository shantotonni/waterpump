<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutsourceService extends Model
{
    use HasFactory;

    protected $table = 'OutsourceService';
    public $timestamps = false;
    protected $primaryKey = 'OutsourceServiceId';
    public $incrementing = false;
    protected $guarded = [];
}
