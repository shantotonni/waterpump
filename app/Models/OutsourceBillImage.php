<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutsourceBillImage extends Model
{
    use HasFactory;

    protected $table = 'OutsourceBillImage';
    public $timestamps = false;
    protected $primaryKey = false;
    public $incrementing = false;
    protected $guarded = [];
}
