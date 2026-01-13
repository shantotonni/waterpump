<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceModel extends Model
{
    use HasFactory;

    protected $table = 'Model';
    protected $primaryKey = 'BrandCode';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
}
