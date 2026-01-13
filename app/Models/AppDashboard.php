<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ServiceMaster;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppDashboard extends Model
{
    use HasFactory;

    public function totalServiceGiven($staffid)
    {
        $totalServiceGiven = ServiceMaster::where('StaffID', $staffid)->count();
        return $totalServiceGiven;
    }

    public function currentMonthServiceGiven($staffid)
    {
        $monthlyTotalServiceGiven = DB::select(DB::raw("SELECT COUNT(*) AS CurrentMonthTotal
                                    FROM ServiceMaster WHERE StaffID='$staffid' AND MONTH(CONVERT(date, AttendDate))= MONTH(GETDATE())"));
        return (int)$monthlyTotalServiceGiven[0]->CurrentMonthTotal;
    }

    public function currentRating($staffid)
    {
        $rating = PointDetails::where('StaffID', $staffid)->avg('Point');
        return round($rating, 2);
    }

    public function monthlyGivenServiceGraph($staffid)
    {
        $monthlyServiceGiven = DB::select(DB::raw("SELECT CONVERT(date, AttendDate) Date,CONVERT(int,COUNT(*)) NumberOfServiceGiven
                                                    FROM [WaterPumpService].[dbo].[ServiceMaster]
                                                    where StaffID='$staffid' AND MONTH(CONVERT(date, AttendDate)) = MONTH(GETDATE())
                                                    group by AttendDate"));
        return $monthlyServiceGiven;
    }
}
