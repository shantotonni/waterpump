<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SelfService extends Model
{
    use HasFactory;

    protected $table = 'SelfService';
    public $timestamps = false;
    protected $primaryKey = 'SelfServiceId';
    public $incrementing = false;
    protected $guarded = [];
}
