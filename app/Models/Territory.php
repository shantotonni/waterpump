<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Territory extends Model
{
    use HasFactory;

    protected $table = 'Territory';
    protected $primaryKey = 'TTYCode';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
}
