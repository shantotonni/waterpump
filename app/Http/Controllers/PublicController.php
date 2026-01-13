<?php

namespace App\Http\Controllers;

use App\Models\PointDetails;
use App\Models\ServiceMaster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicController extends Controller
{
    public function dashboard()
    {
        try {
            $avgRating = DB::connection('dbcrm')->select(DB::raw("exec usp_WaterPumpCSI"));
            if(!empty($avgRating)){
                $avgCSIPoint = round($avgRating[0]->CSI_Percentage, 2);
            }else{
                $avgCSIPoint = 0;
            }
            $start = new Carbon('first day of this month');
            $current_month_first_date = date('Y-m-d',strtotime($start));
            $current_month_last_date = date('Y-m-d',strtotime(Carbon::now()));
            $currentYear = Carbon::now()->format('Y');
            //Start Mechanic
            $thisMonth = Carbon::now()->format('Ym');
            $startMechanics = DB::select("EXEC doLoadTopEngineerMonthCSIDetailsForWaterpump '$thisMonth'");
            array_map(function ($q) {
                $q->Score = 5/100*$q->Score;
                $q->avatar = !empty($q->avatar) ? $q->avatar : 'avatar.png';
            },$startMechanics);
            return response()->json([
                'status' => true,
                'TotalServiceGiven' => ServiceMaster::whereRaw(DB::raw("LEFT(CONVERT(DATE,EntryDate),4) = '$currentYear'"))->count(),
                'CurrentMonthServiceGiven'  =>  $this->currentMonthServiceGiven($current_month_first_date,$current_month_last_date),
                'CurrentDayServiceGiven'  => $this->currentDayServiceGiven(),
                'AvgCSIRating' => $avgCSIPoint,
                'yearCompare' => $this->yearwisecompare(),
                'starMechanics' => $startMechanics
            ], 200)->header('Content-Type', 'application/json');
        } catch (NotFoundHttpException $e) {
            return response()->json(['status' => false, 'message' => 'Nothing found', 'error' => $e], 404);
        }
    }

    public function currentMonthServiceGiven($fromDate, $toDate)
    {
        if(!empty($fromDate) && !empty($toDate)){
            $monthlyTotalServiceGiven = DB::select(DB::raw("SELECT COUNT(*) AS CurrentMonthTotal
                                        FROM ServiceMaster 
                                        WHERE CONVERT(date, EntryDate) between '$fromDate' and '$toDate'"));
        }else{
            $monthlyTotalServiceGiven = DB::select(DB::raw("SELECT COUNT(*) AS CurrentMonthTotal
                                        FROM ServiceMaster 
                                        WHERE MONTH(CONVERT(date, EntryDate))= MONTH(GETDATE())"));
        }
        return $monthlyTotalServiceGiven[0]->CurrentMonthTotal;
    }

    public function currentDayServiceGiven()
    {
        $dailyTotalServiceGiven = DB::select(DB::raw("SELECT COUNT(*) AS CurrentDayTotal
                                    FROM ServiceMaster WHERE CONVERT(date, EntryDate)=CONVERT(date, GETDATE())"));
        return $dailyTotalServiceGiven[0]->CurrentDayTotal;
    }

    public function yearwisecompare()
    {
        $first = Carbon::now()->subYear(1)->format('Y');
        $second = Carbon::now()->format('Y');
        $query = $this->getPdoResult($first,$second);
        $firstYear = $query[0];
        $secondYear = $query[1];
        $months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        $dataFirst = [];
        $dataSecond = [];
        if (count($firstYear) > 0) {
            $j = 0;
            for ($i = 0; $i < count($months); $i++) {
                if (isset($firstYear[$j])) {
                    if ($months[$i] === $firstYear[$j]['Months']) {
                        $dataFirst[] = intval($firstYear[$j]['Services']);
                        $j += 1;
                    } else {
                        $dataFirst[] = 0;
                    }
                } else {
                    $dataFirst[] = 0;
                }
            }
        }
        if (count($secondYear) > 0) {
            $k = 0;
            for ($i = 0; $i < count($months); $i++) {
                if (isset($secondYear[$k])) {
                    if ($months[$i] === $secondYear[$k]['Months']) {
                        $dataSecond[] = intval($secondYear[$k]['Services']);
                        $k += 1;
                    } else {
                        $dataSecond[] = 0;
                    }
                } else {
                    $dataSecond[] = 0;
                }
            }
        }
        return [
            'data' => [
                ['name' => $first,'data' => $dataFirst],
                ['name' => $second,'data' => $dataSecond]
            ]
        ];
    }

    public function getPdoResult($first,$second)
    {
        $conn = DB::connection('sqlsrv');
        $sql = "EXEC sp_compare_between_two_years '$first','$second'";
        $pdo = $conn->getPdo()->prepare($sql);
        $pdo->execute();
        $res = array();
        do {
            $rows = $pdo->fetchAll(\PDO::FETCH_ASSOC);
            $res[] = $rows;
        } while ($pdo->nextRowset());
        return $res;
    }
}
