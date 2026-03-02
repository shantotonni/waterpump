<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ServiceMaster extends Model
{
    use HasFactory;

    protected $table = 'ServiceMaster';

    public $timestamps = false;

    protected $primaryKey = 'ServiceMasterID';

    public $incrementing = false;

    protected $fillable = [
        'ServiceMasterID',
        'StaffID',
        'CustomerName',
        'Address',
        'Mobile',
        'DistrictCode',
        'TerritoryCode',
        'ModelCode',
        'PurchaseDate',
        'ServiceTime',
        'ActionTaken',
        'AttendDate',
        'ServiceCharge',
        'MRNo',
        'Status',
        'Remarks',
        'WarrantyCardNo',
        'EntryBy',
        'EntryDate',
        'EditBy',
        'EditDate'
    ];

    public function getKeyName(){
        return "ServiceMasterID";
    }

    /**
     * Get all of the comments for the ServiceMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function srviceDetails()
    {
        return $this->hasMany(ServiceDetails::class);
    }
}
