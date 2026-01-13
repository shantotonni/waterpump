<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceDetails extends Model
{
    use HasFactory;

    protected $table = 'ServiceDetails';

    public $timestamps = false;

    protected $primaryKey = "ServiceMasterID";

    protected $fillable = [
        'ServiceMasterID',
        'SparePartsCode',
        'QuantityUsed'
    ];

    public function getKeyName(){
        return "ServiceMasterID";
    }

    public function srviceMaster()
    {
        return $this->belongsTo(ServiceMaster::class);
    }
}
