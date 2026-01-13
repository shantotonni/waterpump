<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SparePartsStock extends Model
{
    use HasFactory;

    protected $table = 'SparePartsStock';

    public $timestamps = false;

    protected $primaryKey = 'StockID';

    protected $fillable = [
        'StaffID',
        'ProductCode',
        'Period',
        'Opening',
        'Recive',
        'Used',
        'Adjustment',
        'Westage',
        'Closing',
        'ReceiveDate',
        'EntryBy',
        'EntryDate',
        'EditBy',
        'EditDate'
    ];

}
