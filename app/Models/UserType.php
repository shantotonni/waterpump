<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserType extends Model
{

    protected $table = 'UserType';
    public $timestamps = false;
    protected $primaryKey = "UserTypeID";
    protected $fillable = [
        'UserTypeID', 'UserType', 'UserTypeName'
    ];
}
