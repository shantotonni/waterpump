<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $table = 'ServiceMaster';
    protected $primaryKey = 'ServiceMasterID';
    public $timestamps = false;

   // public $incrementing = false;
    //protected $keyType = 'string';


    public function user(){
        return $this->belongsTo('App\Models\User','StaffID','staffid');
    }

    public function territory(){
        return $this->belongsTo('App\Models\Territory','TerritoryCode','TTYCode');
    }

    public function district(){
        return $this->belongsTo('App\Models\District','DistrictCode','DistrictCode');
    }

    public function model(){
        return $this->belongsTo('App\Models\ServiceModel','ModelCode','BrandCode');
    }

    public function point(){
        return $this->hasOne('App\Models\PointDetails','ServiceMasterID','ServiceMasterID');
    }
}
