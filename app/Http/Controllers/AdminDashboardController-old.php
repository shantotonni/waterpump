<?php

namespace App\Http\Controllers;

use App\Models\AdminDashboard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Models\ServiceDetails;
use App\Models\ServiceMaster;
use Maatwebsite\Excel\Concerns\ToArray;

class AdminDashboardController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->user = $this->guard()->user();
    }

    public function getAllUsers(Request $request)
    {
        $columns = ['id', 'staffid', 'username'];
        $length = $request->input('length');
        $column = $request->input('column');
        $dir = $request->input('dir');
        $searchValue = $request->input('search');
        $draw = $request->input('draw');
        // echo 'hi';die;
        try {
            // $users = new User();
            return response()->json([
                'status' => true,
                'users' => $this->allUsers($columns, $length, $column, $dir, $searchValue, $draw),
            ], 200)->header('Content-Type', 'application/json');
        } catch (NotFoundHttpException $e) {
            return response()->json(['status' => false, 'message' => 'Nothing found', 'error' => $e], Response::HTTP_NOT_FOUND)
                ->header('Content-Type', 'application/json');
        }
    }

    public function getServiceSummary()
    {
        try {
            $avgRating = DB::select(DB::raw("exec usp_doLoadMonthlyAvgRatingPoint"));
            if(!empty($avgRating)){
                $avgCSIPoint = round($avgRating[0]->AvgRatingPoint, 2);
            }else{
                $avgCSIPoint = 0;
            }
            return response()->json([
                'status' => true,
                'TotalServiceGiven' => ServiceMaster::count(),
                'CurrentMonthServiceGiven'  =>  $this->currentMonthServiceGiven(),
                'CurrentDayServiceGiven'  => $this->currentDayServiceGiven(),
                'MonthlyGivenServiceGraph' => $this->monthlyGivenServiceGraph(),
                'AvgCSIRating' => $avgCSIPoint
            ], 200)->header('Content-Type', 'application/json');
        } catch (NotFoundHttpException $e) {
            return response()->json(['status' => false, 'message' => 'Nothing found', 'error' => $e], Response::HTTP_NOT_FOUND)
                ->header('Content-Type', 'application/json');
        }
    }

    public function getTTYWiseDailyServiceChartData(Request $request)
    {
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');
        try {
            return response()->json([
                'status' => true,
                'TTyWiseTodayServiceProvided' => $this->ttyWiseTodayServiceProvided($fromDate, $toDate)
            ], 200)->header('Content-Type', 'application/json');
        } catch (NotFoundHttpException $e) {
            return response()->json(['status' => false, 'message' => 'Nothing found', 'error' => $e], Response::HTTP_NOT_FOUND)
                ->header('Content-Type', 'application/json');
        }
    }

    public function getTTYWiseMonthlyServiceChartData(Request $request)
    {
        $month = $request->input('fromDate');
        if(isset($month) && !empty($month)){
            $period = $month.'-01';
        }else{
            $period = '';
        }
        // return $period;
        try {
            return response()->json([
                'status' => true,
                'TTyWiseMonthlyServiceRatio' => $this->ttyWiseMonthlyServiceRatio($period)
            ], 200)->header('Content-Type', 'application/json');
        } catch (NotFoundHttpException $e) {
            return response()->json(['status' => false, 'message' => 'Nothing found', 'error' => $e], Response::HTTP_NOT_FOUND)
                ->header('Content-Type', 'application/json');
        }
    }

    public function getSplyWiseYearlyServiceChartData(Request $request)
    {
        $month = $request->input('fromDate');
        if(isset($month) && !empty($month)){
            $period = $month.'-01';
        }else{
            $period = '';
        }
        // return $period;
        try {
            return response()->json([
                'status' => true,
                'splyWiseYearlyServiceComparison' => $this->splyWiseYearlyServiceComparison()
            ], 200)->header('Content-Type', 'application/json');
        } catch (NotFoundHttpException $e) {
            return response()->json(['status' => false, 'message' => 'Nothing found', 'error' => $e], Response::HTTP_NOT_FOUND)
                ->header('Content-Type', 'application/json');
        }
    }

    public function allUsers($columns, $length, $column, $dir, $searchValue, $draw){
        $query = User::join('UserType', 'users.usertype', '=', 'UserType.UserType')
                ->join('Territory','Territory.TTYCode','=','users.territorycode')
                ->join('ViewDistrict','ViewDistrict.DistrictCode','=','users.districtcode')
                ->leftjoin('PointDetails','PointDetails.StaffID','=','users.staffid')
                ->select('users.id AS UserID','users.username AS UserName','users.staffid AS StaffID','users.mobile AS MobileNo','users.joindate AS JoinDate','Territory.TTYCode AS TerritoryCode','Territory.TTYName AS TerritoryName','ViewDistrict.DistrictCode','ViewDistrict.DistrictName',
                'users.dob AS DateOfBirth','users.address AS Address','users.imagefilename AS ImageFileName','users.imagefilepath AS ImageFilePath','users.nid AS NID', 'users.usertype AS UserType', 'UserType.UserTypeName',
                'users.active',DB::raw('ROUND(AVG(Point),1) as TotalPoint'))
                ->groupBy('users.id','users.username','users.staffid','users.mobile', 'users.joindate', 'Territory.TTYCode', 'Territory.TTYName', 'ViewDistrict.DistrictCode', 'ViewDistrict.DistrictName', 'users.dob', 'users.address', 'users.imagefilename', 'users.imagefilepath', 'users.nid', 'users.usertype', 'UserType.UserTypeName', 'users.active')
                ->orderBy($columns[$column], $dir);
        if($searchValue){
            $query->where(function($query) use ($searchValue){
                $query->where('users.staffid', 'like', '%'.$searchValue.'%')
                        ->orWhere('users.username', 'like', '%'.$searchValue.'%');
            });
        }

        $user = $query->paginate($length);

        return ['data'=>$user, 'draw'=>$draw];
    }

    public function currentMonthServiceGiven()
    {
        $monthlyTotalServiceGiven = DB::select(DB::raw("SELECT COUNT(*) AS CurrentMonthTotal
                                    FROM ServiceMaster WHERE MONTH(CONVERT(date, EntryDate))= MONTH(GETDATE())"));
        return $monthlyTotalServiceGiven[0]->CurrentMonthTotal;
    }

    public function currentDayServiceGiven()
    {
        $dailyTotalServiceGiven = DB::select(DB::raw("SELECT COUNT(*) AS CurrentDayTotal
                                    FROM ServiceMaster WHERE CONVERT(date, EntryDate)=CONVERT(date, GETDATE())"));
        return $dailyTotalServiceGiven[0]->CurrentDayTotal;
    }

    public function monthlyGivenServiceGraph()
    {
        $monthlyServiceGiven = DB::select(DB::raw("SELECT CONVERT(date, EntryDate) Date,count(*) NumberOfServiceGiven
                                                    FROM [WaterPumpService].[dbo].[ServiceMaster]
                                                    where MONTH(CONVERT(date, EntryDate))= MONTH(GETDATE())
                                                    group by EntryDate"));
        return $monthlyServiceGiven;
    }

    public function ttyWiseTodayServiceProvided($fromDate, $toDate)
    {
        if(!empty($fromDate) && !empty($toDate)){
            $ttyWiseDailyServiceGiven = DB::select(DB::raw("SELECT T.TTYName, COUNT(SM.ServiceMasterID) NoOfServices
                                                        FROM Territory T
                                                        LEFT JOIN WaterPumpService.dbo.ServiceMaster SM ON SM.TerritoryCode=T.TTYCode
                                                        AND CONVERT(date, SM.EntryDate) BETWEEN '$fromDate' AND '$toDate'
                                                        GROUP BY T.TTYName"));
        }else{
            $ttyWiseDailyServiceGiven = DB::select(DB::raw("SELECT T.TTYName, COUNT(SM.ServiceMasterID) NoOfServices
                                                        FROM Territory T
                                                        LEFT JOIN WaterPumpService.dbo.ServiceMaster SM ON SM.TerritoryCode=T.TTYCode
                                                        AND CONVERT(date, SM.EntryDate)= CONVERT(date, GETDATE())
                                                        GROUP BY T.TTYName"));
        }

        return $ttyWiseDailyServiceGiven;
    }

    public function ttyWiseMonthlyServiceRatio($period)
    {
        if(!empty($period)){
            $ttyWiseMonthlyService = DB::select(DB::raw("SELECT T.TTYName, COUNT(SM.ServiceMasterID) NoOfServices,
                                                        CONCAT(CAST(ROUND(

                                                                    (COUNT(SM.ServiceMasterID)*100.0)/(SELECT COUNT(*) FROM WaterPumpService.dbo.ServiceMaster
                                                                            WHERE MONTH(CONVERT(date, EntryDate))= MONTH(CONVERT(date, '$period')))

                                                                    ,1) AS decimal(10,1)),'%') AS ServicePercentage
                                                        FROM [WaterPumpService].[dbo].Territory T
                                                        LEFT JOIN WaterPumpService.dbo.ServiceMaster SM ON SM.TerritoryCode=T.TTYCode
                                                        AND MONTH(CONVERT(date, SM.EntryDate))= MONTH(CONVERT(date, '$period'))
                                                        GROUP BY T.TTYName"));
        }else{
            $ttyWiseMonthlyService = DB::select(DB::raw("SELECT T.TTYName, COUNT(SM.ServiceMasterID) NoOfServices,
                                                        CONCAT(CAST(ROUND(

                                                                    (COUNT(SM.ServiceMasterID)*100.0)/(SELECT COUNT(*) FROM WaterPumpService.dbo.ServiceMaster
                                                                            WHERE MONTH(CONVERT(date, EntryDate))= MONTH(GETDATE()))

                                                                    ,1) AS decimal(10,1)),'%') AS ServicePercentage
                                                        FROM [WaterPumpService].[dbo].Territory T
                                                        LEFT JOIN WaterPumpService.dbo.ServiceMaster SM ON SM.TerritoryCode=T.TTYCode
                                                        AND MONTH(CONVERT(date, SM.EntryDate))= MONTH(GETDATE())
                                                        GROUP BY T.TTYName"));
        }

        return $ttyWiseMonthlyService;
    }

    public function splyWiseYearlyServiceComparison()
    {
        // if(!empty($period)){
            $splyWiseService = DB::select(DB::raw("SELECT
                                                        YEAR(EntryDate) as Year,
                                                        SUM(CASE datepart(month,EntryDate) WHEN 1 THEN 1 ELSE 0 END) AS 'January',
                                                        SUM(CASE datepart(month,EntryDate) WHEN 2 THEN 1 ELSE 0 END) AS 'February',
                                                        SUM(CASE datepart(month,EntryDate) WHEN 3 THEN 1 ELSE 0 END) AS 'March',
                                                        SUM(CASE datepart(month,EntryDate) WHEN 4 THEN 1 ELSE 0 END) AS 'April',
                                                        SUM(CASE datepart(month,EntryDate) WHEN 5 THEN 1 ELSE 0 END) AS 'May',
                                                        SUM(CASE datepart(month,EntryDate) WHEN 6 THEN 1 ELSE 0 END) AS 'June',
                                                        SUM(CASE datepart(month,EntryDate) WHEN 7 THEN 1 ELSE 0 END) AS 'July',
                                                        SUM(CASE datepart(month,EntryDate) WHEN 8 THEN 1 ELSE 0 END) AS 'August',
                                                        SUM(CASE datepart(month,EntryDate) WHEN 9 THEN 1 ELSE 0 END) AS 'September',
                                                        SUM(CASE datepart(month,EntryDate) WHEN 10 THEN 1 ELSE 0 END) AS 'October',
                                                        SUM(CASE datepart(month,EntryDate) WHEN 11 THEN 1 ELSE 0 END) AS 'November',
                                                        SUM(CASE datepart(month,EntryDate) WHEN 12 THEN 1 ELSE 0 END) AS 'December'
                                                        --SUM(CASE datepart(year,EntryDate) WHEN YEAR(EntryDate) THEN 1 ELSE 0 END) AS 'TotalService'
                                                    FROM      [WaterPumpService].[dbo].[ServiceMaster]
                                                    WHERE     YEAR(EntryDate) = YEAR(EntryDate) or YEAR(EntryDate) = YEAR(EntryDate)-1
                                                    GROUP BY  YEAR(EntryDate)
                                                    order by  YEAR(EntryDate) ASC"));
        // }else{
        //     $splyWiseService = DB::select(DB::raw("SELECT
        //                                                 YEAR(EntryDate) as Year,
        //                                                 SUM(CASE datepart(month,EntryDate) WHEN 1 THEN 1 ELSE 0 END) AS 'January',
        //                                                 SUM(CASE datepart(month,EntryDate) WHEN 2 THEN 1 ELSE 0 END) AS 'February',
        //                                                 SUM(CASE datepart(month,EntryDate) WHEN 3 THEN 1 ELSE 0 END) AS 'March',
        //                                                 SUM(CASE datepart(month,EntryDate) WHEN 4 THEN 1 ELSE 0 END) AS 'April',
        //                                                 SUM(CASE datepart(month,EntryDate) WHEN 5 THEN 1 ELSE 0 END) AS 'May',
        //                                                 SUM(CASE datepart(month,EntryDate) WHEN 6 THEN 1 ELSE 0 END) AS 'June',
        //                                                 SUM(CASE datepart(month,EntryDate) WHEN 7 THEN 1 ELSE 0 END) AS 'July',
        //                                                 SUM(CASE datepart(month,EntryDate) WHEN 8 THEN 1 ELSE 0 END) AS 'August',
        //                                                 SUM(CASE datepart(month,EntryDate) WHEN 9 THEN 1 ELSE 0 END) AS 'September',
        //                                                 SUM(CASE datepart(month,EntryDate) WHEN 10 THEN 1 ELSE 0 END) AS 'October',
        //                                                 SUM(CASE datepart(month,EntryDate) WHEN 11 THEN 1 ELSE 0 END) AS 'November',
        //                                                 SUM(CASE datepart(month,EntryDate) WHEN 12 THEN 1 ELSE 0 END) AS 'December'
        //                                                 --SUM(CASE datepart(year,EntryDate) WHEN YEAR(EntryDate) THEN 1 ELSE 0 END) AS 'TotalService'
        //                                             FROM      [WaterPumpService].[dbo].[ServiceMaster]
        //                                             WHERE     YEAR(EntryDate) = YEAR(EntryDate) or YEAR(EntryDate) = YEAR(EntryDate)-1
        //                                             GROUP BY  YEAR(EntryDate)
        //                                             order by  YEAR(EntryDate) ASC"));
        // }

        return $splyWiseService;
    }

    protected function guard()
    {
        return Auth::guard('api');
    }
}
