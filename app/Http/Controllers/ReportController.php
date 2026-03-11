<?php

namespace App\Http\Controllers;

use App\Models\OutsourceBillImage;
use App\Models\OutsourceService;
use App\Models\SelfService;
use App\Models\SelfServiceBillImage;
use App\Models\ServiceMaster;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\ServiceDetails;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->user = $this->guard()->user();
    }

    public function providedServiceReport(Request $request){

        $columns = ['ServiceMasterID', 'StaffID', 'CustomerName', 'Mobile', 'TTYName', 'DistrictName', 'username', 'AttendDate', 'Feedback', 'Point'];
        $length = $request->input('length');
        $column = $request->input('column');
        $dir = $request->input('dir');
        $searchValue = $request->input('search');
        $draw = $request->input('draw');
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');
        $serviceTime = $request->input('serviceTime');
        $business = $request->input('business');
        try {
            return response()->json([
                'status' => true,
                'serviceMaster' => $this->getServiceMaster($columns, $length, $column, $dir, $searchValue, $draw, $fromDate, $toDate, $serviceTime, $business)
            ], 200)->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Nothing found', 'error' => $e], Response::HTTP_NOT_FOUND)
                ->header('Content-Type', 'application/json');
        }

    }

    public function engineerWiseCSI(Request $request)
    {
        try {
            $period = $request->period;
            $data = DB::select("EXEC doLoadEngineerMonthCSIDetailsForWaterpump '$period'");
            return response()->json([
                'status' => true,
                'data' => $data
            ], 200)->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong!'
            ],500);
        }

    }

    public function serviceSummaryReport(Request $request)
    {
        try {
            $fromDate = $request->input('fromDate');
            $toDate = $request->input('toDate');
            $business = $request->input('business');
            $data = DB::select("SET NOCOUNT ON; EXEC ServiceSummaryReport '$fromDate', '$toDate 23:59:59'");

            if (!empty($business)) {
                $data = collect($data)->filter(function ($item) use ($business) {
                    return $item->Business === $business;
                })->values()->all();
            }

            return response()->json([
                'status' => true,
                'data' => $data
            ], 200)->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong!'
            ], 500);
        }
    }

    public function providedMergedServiceReport(Request $request)
    {
        $columns = ['ServiceMasterID', 'StaffID', 'CustomerName', 'Mobile', 'TTYName', 'username', 'AttendDate', 'Feedback', 'Point'];
        $length = $request->input('length');
        $column = $request->input('column');
        $dir = $request->input('dir');
        $searchValue = $request->input('search');
        $draw = $request->input('draw');
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');
        $serviceTime = $request->input('serviceTime');

        try {
            // Get both service types
            $selfService = $this->getSelfServiceTest($columns, $length, $column, $dir, $searchValue, $draw, $fromDate, $toDate, $serviceTime);
            $outsourceService = $this->getOutsourceServiceTest($columns, $length, $column, $dir, $searchValue, $draw, $fromDate, $toDate, $serviceTime);

            // Extract data arrays
            $selfData = collect($selfService['data'])->map(function ($item) {
                return [
                    'ServiceMasterID' => $item->ServiceMasterID,
                    'StaffID' => $item->StaffID,
                    'StaffName' => $item->StaffName,
                    'CustomerName' => $item->CustomerName,
                    'Mobile' => $item->Mobile,
                    'Address' => $item->Address,
                    'Brandname' => $item->Brandname,
                    'PurchaseDate' => $item->PurchaseDate,
                    'ActionTaken' => $item->ActionTaken,
                    'TotalCost' => $item->SelfTotalCost,
                    'TechnicianName' => $item->StaffName, // Engineer himself
                    'ServiceType' => 'SelfService',
                    'TTYName' => $item->TTYName,
                    'EntryDate' => $item->EntryDate
                ];
            });

            $outsourceData = collect($outsourceService['data'])->map(function ($item) {
                return [
                    'ServiceMasterID' => $item->ServiceMasterID,
                    'StaffID' => $item->StaffID,
                    'StaffName' => $item->StaffName,
                    'CustomerName' => $item->CustomerName,
                    'Mobile' => $item->Mobile,
                    'Address' => $item->Address,
                    'Brandname' => $item->Brandname,
                    'PurchaseDate' => $item->PurchaseDate,
                    'ActionTaken' => $item->ActionTaken,
                    'TotalCost' => $item->OutsourceTotalCost,
                    'TechnicianName' => $item->TechnicianName, // From LocalTechnician
                    'ServiceType' => 'Outsource',
                    'TTYName' => $item->TTYName,
                    'EntryDate' => $item->EntryDate
                ];
            });

            // Merge both collections
            $merged = $selfData->merge($outsourceData)->sortByDesc('EntryDate')->values();

            $grouped = $merged->groupBy(function($item) {
                return $item['TechnicianName'] . '_' . $item['ServiceType']; // Unique per person per role
            })->map(function($items) {
                return [
                    'ServiceMasterID' => $items->first()['ServiceMasterID'],
                    'StaffID' => $items->first()['StaffID'],
                    'StaffName' => $items->first()['TechnicianName'],
                    'CustomerName' => $items->first()['CustomerName'],
                    'Mobile' => $items->first()['Mobile'],
                    'Address' => $items->first()['Address'],
                    'Brandname' => $items->first()['Brandname'],
                    'Role' => $items->first()['ServiceType'], // SelfService or Outsource
                    'TotalCost' => $items->sum('TotalCost'),
                    'ServiceCount' => $items->count(),
                    'TTYName' => $items->first()['TTYName'],
                    'PurchaseDate' => $items->first()['PurchaseDate'],
                    'ActionTaken' => $items->first()['ActionTaken'],
                ];
            })->values();

            // Apply pagination manually since merging removes the paginator
            $currentPage = (int)$request->input('page', 1);
            $perPage = (int)$length;
            $pagedData = $grouped->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $total = $grouped->count();

             return response()->json([
                'status' => true,
                'serviceMaster' => [
                    'data' => $pagedData,
                    'current_page' => $currentPage,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                    'from' => ($total > 0) ? (($currentPage - 1) * $perPage + 1) : 0,
                    'to' => ($total > $currentPage * $perPage) ? ($currentPage * $perPage) : $total,
                    'draw' => $draw,
                ]
            ], 200)->header('Content-Type', 'application/json');

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function providedSelfServiceReport(Request $request){

        $columns = ['ServiceMasterID', 'StaffID', 'CustomerName', 'Mobile', 'TTYName', 'username', 'AttendDate', 'Feedback', 'Point'];
        $length = $request->input('length');
        $column = $request->input('column');
        $dir = $request->input('dir');
        $searchValue = $request->input('search');
        $draw = $request->input('draw');
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');
        $serviceTime = $request->input('serviceTime');

        try {
            return response()->json([
                'status' => true,
                'serviceMaster' => $this->getSelfService($columns, $length, $column, $dir, $searchValue, $draw, $fromDate, $toDate, $serviceTime)
            ], 200)->header('Content-Type', 'application/json');
        } catch (NotFoundHttpException $e) {
            return response()->json(['status' => false, 'message' => 'Nothing found', 'error' => $e], Response::HTTP_NOT_FOUND)
                ->header('Content-Type', 'application/json');
        }

    }

    public function providedOutsourceServiceReport(Request $request){

        $columns = ['ServiceMasterID', 'StaffID', 'CustomerName', 'Mobile', 'TTYName', 'DistrictName', 'username', 'AttendDate', 'Feedback', 'Point'];
        $length = $request->input('length');
        $column = $request->input('column');
        $dir = $request->input('dir');
        $searchValue = $request->input('search');
        $draw = $request->input('draw');
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');
        $serviceTime = $request->input('serviceTime');

        try {
            return response()->json([
                'status' => true,
                'serviceMaster' => $this->getOutsourceService($columns, $length, $column, $dir, $searchValue, $draw, $fromDate, $toDate, $serviceTime)
            ], 200)->header('Content-Type', 'application/json');
        } catch (NotFoundHttpException $e) {
            return response()->json(['status' => false, 'message' => 'Nothing found', 'error' => $e], Response::HTTP_NOT_FOUND)
                ->header('Content-Type', 'application/json');
        }

    }

    public function providedServiceDetailReport(Request $request){
        // print_r($request->input());
        $columns = ['ServiceMasterID', 'StaffID', 'CustomerName', 'Mobile', 'TTYName', 'DistrictName', 'username', 'AttendDate', 'Feedback', 'Point'];
        $length = $request->input('length');
        $column = $request->input('column');
        $dir = $request->input('dir');
        $searchValue = $request->input('search');
        $draw = $request->input('draw');
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');
        $serviceTime = $request->input('serviceTime');

        try {
            return response()->json([
                'status' => true,
                'serviceDetails' => $this->getServiceDetails($columns, $length, $column, $dir, $searchValue, $draw, $fromDate, $toDate, $serviceTime)
            ], 200)->header('Content-Type', 'application/json');
        } catch (NotFoundHttpException $e) {
            return response()->json(['status' => false, 'message' => 'Nothing found', 'error' => $e], Response::HTTP_NOT_FOUND)
                ->header('Content-Type', 'application/json');
        }

    }

    public function getImage($id,$type) {
        if ($type === 'SelfWarrantyCardImage') {
            $data = SelfService::where('ServiceMasterId',$id)->get();
        } elseif ($type === 'OutsourceWarrantyCardImage') {
            $data = OutsourceService::where('ServiceMasterId',$id)->get();
        } elseif ($type === 'SelfBillImage') {
            $query = SelfService::where('ServiceMasterId',$id)->first();
            $data = SelfServiceBillImage::where('SelfServiceId',$query->SelfServiceId)->get();
        } elseif ($type === 'OutsourceBillImage') {
            $query = OutsourceService::where('ServiceMasterId',$id)->first();
            $data = OutsourceBillImage::where('OutsourceServiceId',$query->OutsourceServiceId)->get();
        }
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'imgType' => $type
        ]);
    }

    public function getImagePDF(Request $request) {
        try{
            $data = [];
            if ($request->imageType === 'selfWarrantyImage') {
                $q = SelfService::select('SelfService.*')->join('ServiceMaster','ServiceMaster.ServiceMasterID','SelfService.ServiceMasterId')->where('ServiceMaster.ServiceType','SelfService');
                if ($request->tableData['fromDate'] != '' && $request->tableData['toDate'] != '') {
                    $q->whereDate('ServiceMaster.EntryDate','>=',$request->tableData['fromDate']);
                    $q->whereDate('ServiceMaster.EntryDate','<=',$request->tableData['toDate']);
                }
                $data = $q->get();
            }
            if ($request->imageType === 'SelfBillImage') {
                $q = SelfService::select('SelfService.*')->join('ServiceMaster','ServiceMaster.ServiceMasterID','SelfService.ServiceMasterId')->where('ServiceMaster.ServiceType','SelfService');
                if ($request->tableData['fromDate'] != '' && $request->tableData['toDate'] != '') {
                    $q->whereDate('ServiceMaster.EntryDate','>=',$request->tableData['fromDate']);
                    $q->whereDate('ServiceMaster.EntryDate','<=',$request->tableData['toDate']);
                }
                $serviceIds = $q->pluck('SelfServiceId')->toArray();
                $data = SelfServiceBillImage::select('SelfServiceBillImage.*','SelfService.ServiceMasterId')->join('SelfService','SelfService.SelfServiceId','SelfServiceBillImage.SelfServiceId')->whereIn('SelfServiceBillImage.SelfServiceId',$serviceIds)->get();
            }
            if ($request->imageType === 'OutsourceWarrantyCardImage') {
                $q = OutsourceService::select('OutsourceService.*')->join('ServiceMaster','ServiceMaster.ServiceMasterID','OutsourceService.ServiceMasterId')->where('ServiceMaster.ServiceType','Outsource');
                if ($request->tableData['fromDate'] != '' && $request->tableData['toDate'] != '') {
                    $q->whereDate('ServiceMaster.EntryDate','>=',$request->tableData['fromDate']);
                    $q->whereDate('ServiceMaster.EntryDate','<=',$request->tableData['toDate']);
                }
                $data = $q->get();
            }
            if ($request->imageType === 'OutsourceBillImage') {
                $q = OutsourceService::select('OutsourceService.*')->join('ServiceMaster','ServiceMaster.ServiceMasterID','OutsourceService.ServiceMasterId')->where('ServiceMaster.ServiceType','Outsource');
                if ($request->tableData['fromDate'] != '' && $request->tableData['toDate'] != '') {
                    $q->whereDate('ServiceMaster.EntryDate','>=',$request->tableData['fromDate']);
                    $q->whereDate('ServiceMaster.EntryDate','<=',$request->tableData['toDate']);
                }
                $serviceIds = $q->pluck('OutsourceServiceId')->toArray();
                $data = OutsourceBillImage::select('OutsourceBillImage.*','OutsourceService.ServiceMasterId')->join('OutsourceService','OutsourceService.OutsourceServiceId','OutsourceBillImage.OutsourceServiceId')->whereIn('OutsourceBillImage.OutsourceServiceId',$serviceIds)->get();
            }
            return response()->json([
                'status' => 'success',
                'data' => $data,
                'imageType' => $request->imageType
            ]);
        } catch(\Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
            ],500);
        }
    }

    public function dailySparePartsReport(Request $request){
        // print_r($request->input());
        $columns = ['staffid', 'username', 'ProductName'];
        $length = $request->input('length');
        $column = $request->input('column');
        $dir = $request->input('dir');
        $searchValue = $request->input('search');
        $draw = $request->input('draw');

        if($request->input('fromDate') && $request->input('toDate')){
            $fromDate = $request->input('fromDate');
            $toDate = $request->input('toDate');
        }else{
            $fromDate = date('Y-m-d');
            $toDate = date('Y-m-d');
        }

        try {
            return response()->json([
                'status' => true,
                'dailySparePartsReport' => $this->getDailySparePartsReport($columns, $length, $column, $dir, $searchValue, $draw, $fromDate, $toDate)
            ], 200)->header('Content-Type', 'application/json');
        } catch (NotFoundHttpException $e) {
            return response()->json(['status' => false, 'message' => 'Nothing found', 'error' => $e], Response::HTTP_NOT_FOUND)
                ->header('Content-Type', 'application/json');
        }
    }

    public function getServiceMaster($columns, $length, $column, $dir, $searchValue, $draw, $fromDate, $toDate, $serviceTime, $business = '')
    {
        $query = Report::leftJoin('ViewDistrict AS D', 'D.DistrictCode', '=', 'ServiceMaster.DistrictCode')
            ->join('users AS U', 'U.staffid', '=', 'ServiceMaster.StaffID')
            ->join('Territory AS T', 'T.TTYCode', '=', 'ServiceMaster.TerritoryCode')
            ->join('Model AS M', 'M.BrandCode', '=', 'ServiceMaster.ModelCode')
            ->leftJoin('PointDetails AS P', 'P.ServiceMasterID', '=', 'ServiceMaster.ServiceMasterID')
            ->select(
                'ServiceMaster.ServiceMasterID',
                'ServiceMaster.Remarks',
                'ServiceMaster.StaffID',
                'ServiceMaster.CustomerName',
                'ServiceMaster.Address',
                'ServiceMaster.Mobile',
                'ServiceMaster.DistrictCode',
                'ServiceMaster.TerritoryCode',
                'ServiceMaster.ModelCode',
                'ServiceMaster.PurchaseDate',
                'ServiceMaster.ServiceTime',
                'ServiceMaster.ActionTaken',
                'ServiceMaster.AttendDate',
                'ServiceMaster.ServiceCharge',
                'ServiceMaster.MRNo',
                'ServiceMaster.Status',
                'ServiceMaster.WarrantyCardNo',
                'ServiceMaster.EntryBy',
                'ServiceMaster.EntryDate',
                'ServiceMaster.Business',
                'T.TTYCode',
                'T.TTYName',
                'M.BrandCode',
                'M.Brandname',
                'U.username AS StaffName',
                'D.DistrictName',
                DB::raw('ISNULL(P.Point, 0) AS Point'),
                'P.Feedback',
            )
            ->groupByRaw('
                [ServiceMaster].[ServiceMasterID], [ServiceMaster].[Remarks],
                [ServiceMaster].[StaffID], [ServiceMaster].[CustomerName],
                [ServiceMaster].[Address], [ServiceMaster].[Mobile],
                [ServiceMaster].[DistrictCode], [ServiceMaster].[TerritoryCode],
                [ServiceMaster].[ModelCode], [ServiceMaster].[PurchaseDate],
                [ServiceMaster].[ServiceTime], [ServiceMaster].[ActionTaken],
                [ServiceMaster].[AttendDate], [ServiceMaster].[ServiceCharge],
                [ServiceMaster].[MRNo], [ServiceMaster].[Status],
                [ServiceMaster].[WarrantyCardNo], [ServiceMaster].[EntryBy],
                [ServiceMaster].[EntryDate], [ServiceMaster].[Business],
                [T].[TTYCode], [T].[TTYName],
                [M].[BrandCode], [M].[Brandname],
                [U].[username],
                [D].[DistrictName],
                [P].[Point], [P].[Feedback]
            ')
            ->orderBy('ServiceMaster.' . $columns[$column], $dir);

        if ($searchValue) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('ServiceMaster.StaffID', 'like', '%' . $searchValue . '%')
                    ->orWhere('U.username', 'like', '%' . $searchValue . '%')
                    ->orWhere('D.DistrictName', 'like', '%' . $searchValue . '%')
                    ->orWhere('ServiceMaster.MRNo', 'like', '%' . $searchValue . '%')
                    ->orWhere('ServiceMaster.CustomerName', 'like', '%' . $searchValue . '%')
                    ->orWhere('T.TTYName', 'like', '%' . $searchValue . '%')
                    ->orWhere('ServiceMaster.ServiceTime', 'like', '%' . $searchValue . '%');
            });
        }

        if ($fromDate && $toDate) {
            $query->whereBetween(DB::raw('CONVERT(date, ServiceMaster.EntryDate)'), [$fromDate, $toDate]);
        }

        if (!empty($serviceTime)) {
            $query->where('ServiceMaster.ServiceTime', '=', $serviceTime);
        }

        if (!empty($business)) {
            $query->where('ServiceMaster.Business', '=', $business);
        }

        $allServices = $query->paginate(10);

        return ['data' => $allServices, 'draw' => $draw];
    }

    public function getSelfService($columns, $length, $column, $dir, $searchValue, $draw, $fromDate, $toDate, $serviceTime){

        $query = Report::leftJoin('ViewDistrict AS D', 'D.DistrictCode', '=', 'ServiceMaster.DistrictCode')
            ->join('users AS U', 'U.staffid', '=', 'ServiceMaster.StaffID')
            ->join('Territory AS T', 'T.TTYCode', '=', 'ServiceMaster.TerritoryCode')
            ->join('Model AS M', 'M.BrandCode', '=', 'ServiceMaster.ModelCode')
            ->leftjoin('PointDetails AS P', 'P.ServiceMasterID', '=', 'ServiceMaster.ServiceMasterID')
            ->join('SelfService','SelfService.ServiceMasterID','ServiceMaster.ServiceMasterID')
            ->select('ServiceMaster.ServiceMasterID','ServiceMaster.Remarks','ServiceMaster.StaffID','ServiceMaster.CustomerName','ServiceMaster.Address',
                DB::raw('RIGHT([ServiceMaster].[Mobile],11) AS Mobile'),'ServiceMaster.DistrictCode','ServiceMaster.TerritoryCode','ServiceMaster.ModelCode',
                DB::raw('CONVERT(VARCHAR(23), ServiceMaster.PurchaseDate, 103) AS PurchaseDate'),'ServiceMaster.ServiceTime','ServiceMaster.ActionTaken',
                DB::raw('CONVERT(VARCHAR(23), ServiceMaster.AttendDate, 103) AS AttendDate'),
                DB::raw('CAST(ServiceMaster.ServiceCharge AS decimal(18,2)) AS ServiceCharge'),
                DB::raw('CONVERT(VARCHAR(100), ServiceMaster.MRNo) AS MRNo'),'ServiceMaster.Status',
                DB::raw('CONVERT(VARCHAR(100), ServiceMaster.WarrantyCardNo) AS WarrantyCardNo'),'ServiceMaster.EntryBy',
                DB::raw("CONVERT(VARCHAR(23), ServiceMaster.EntryDate, 103) +' '+ CONVERT(VARCHAR(23), ServiceMaster.EntryDate, 114) EntryDate"), 'T.TTYCode', 'T.TTYName', 'M.BrandCode', 'M.Brandname', 'U.username AS StaffName', 'D.DistrictName',
                DB::raw('ISNULL(P.Point,0) AS Point'),'P.Feedback','SelfService.TotalCost as SelfTotalCost')
            ->orderBy('ServiceMaster.'.$columns[$column], $dir);
        if($searchValue){
            $query->where(function($query) use ($searchValue){
                $query->where('ServiceMaster.StaffID', 'like', '%'.$searchValue.'%')
                    ->orWhere('U.username', 'like', '%'.$searchValue.'%')
                    ->orWhere('D.DistrictName', 'like', '%'.$searchValue.'%')
                    ->orWhere('ServiceMaster.MRNo', 'like', '%'.$searchValue.'%')
                    ->orWhere('ServiceMaster.CustomerName', 'like', '%'.$searchValue.'%')
                    ->orWhere('T.TTYName', 'like', '%'.$searchValue.'%')
                    ->orWhere('ServiceMaster.ServiceTime', 'like', '%'.$searchValue.'%');
            });
        }

        if($fromDate && $toDate){
            $query->whereBetween(DB::raw('CONVERT(date, ServiceMaster.EntryDate)'), [$fromDate, $toDate]);
        }

        if(!empty($serviceTime)){
            $query->where('ServiceMaster.ServiceTime', '=', $serviceTime);
        }
        // return $query;

        $query->where('ServiceType','SelfService');

        $allServices = $query->paginate(10);

        // dd(DB::getQueryLog()); // Show results of log

        return ['data'=>$allServices, 'draw'=>$draw];

    }

    public function getSelfServiceTest($columns, $length, $column, $dir, $searchValue, $draw, $fromDate, $toDate, $serviceTime)
    {
        $query = Report::leftJoin('ViewDistrict AS D', 'D.DistrictCode', '=', 'ServiceMaster.DistrictCode')
            ->join('users AS U', 'U.staffid', '=', 'ServiceMaster.StaffID')
            ->join('Territory AS T', 'T.TTYCode', '=', 'ServiceMaster.TerritoryCode')
            ->join('Model AS M', 'M.BrandCode', '=', 'ServiceMaster.ModelCode')
            ->leftjoin('PointDetails AS P', 'P.ServiceMasterID', '=', 'ServiceMaster.ServiceMasterID')
            ->join('SelfService','SelfService.ServiceMasterID','ServiceMaster.ServiceMasterID')
            ->select('ServiceMaster.ServiceMasterID','ServiceMaster.Remarks','ServiceMaster.StaffID','ServiceMaster.CustomerName','ServiceMaster.Address',
                DB::raw('RIGHT([ServiceMaster].[Mobile],11) AS Mobile'),'ServiceMaster.DistrictCode','ServiceMaster.TerritoryCode','ServiceMaster.ModelCode',
                DB::raw('CONVERT(VARCHAR(23), ServiceMaster.PurchaseDate, 103) AS PurchaseDate'),'ServiceMaster.ServiceTime','ServiceMaster.ActionTaken',
                DB::raw('CONVERT(VARCHAR(23), ServiceMaster.AttendDate, 103) AS AttendDate'),
                DB::raw('CAST(ServiceMaster.ServiceCharge AS decimal(18,2)) AS ServiceCharge'),
                DB::raw('CONVERT(VARCHAR(100), ServiceMaster.MRNo) AS MRNo'),'ServiceMaster.Status',
                DB::raw('CONVERT(VARCHAR(100), ServiceMaster.WarrantyCardNo) AS WarrantyCardNo'),'ServiceMaster.EntryBy',
                DB::raw("CONVERT(VARCHAR(23), ServiceMaster.EntryDate, 103) +' '+ CONVERT(VARCHAR(23), ServiceMaster.EntryDate, 114) EntryDate"), 'T.TTYCode', 'T.TTYName', 'M.BrandCode', 'M.Brandname', 'U.username AS StaffName', 'D.DistrictName',
                DB::raw('ISNULL(P.Point,0) AS Point'),'P.Feedback','SelfService.TotalCost as SelfTotalCost')
            ->orderBy('ServiceMaster.'.$columns[$column], $dir);
        if($searchValue){
            $query->where(function($query) use ($searchValue){
                $query->where('ServiceMaster.StaffID', 'like', '%'.$searchValue.'%')
                    ->orWhere('U.username', 'like', '%'.$searchValue.'%')
                    ->orWhere('D.DistrictName', 'like', '%'.$searchValue.'%')
                    ->orWhere('ServiceMaster.MRNo', 'like', '%'.$searchValue.'%')
                    ->orWhere('ServiceMaster.CustomerName', 'like', '%'.$searchValue.'%')
                    ->orWhere('T.TTYName', 'like', '%'.$searchValue.'%')
                    ->orWhere('ServiceMaster.ServiceTime', 'like', '%'.$searchValue.'%');
            });
        }

        if($fromDate && $toDate){
            $query->whereBetween(DB::raw('CONVERT(date, ServiceMaster.EntryDate)'), [$fromDate, $toDate]);
        }

        if(!empty($serviceTime)){
            $query->where('ServiceMaster.ServiceTime', '=', $serviceTime);
        }

        $query->where('ServiceType','SelfService');

        $allServices = $query->get();

        return ['data'=>$allServices, 'draw'=>$draw];

    }

    public function getOutsourceService($columns, $length, $column, $dir, $searchValue, $draw, $fromDate, $toDate, $serviceTime){
        $query = Report::join('ViewDistrict AS D', 'D.DistrictCode', '=', 'ServiceMaster.DistrictCode')
            ->join('users AS U', 'U.staffid', '=', 'ServiceMaster.StaffID')
            ->join('Territory AS T', 'T.TTYCode', '=', 'ServiceMaster.TerritoryCode')
            ->join('Model AS M', 'M.BrandCode', '=', 'ServiceMaster.ModelCode')
            ->leftjoin('PointDetails AS P', 'P.ServiceMasterID', '=', 'ServiceMaster.ServiceMasterID')
            ->join('OutsourceService','OutsourceService.ServiceMasterID','ServiceMaster.ServiceMasterID')
            ->select('ServiceMaster.ServiceMasterID','ServiceMaster.Remarks','ServiceMaster.StaffID','ServiceMaster.CustomerName','ServiceMaster.Address',
                DB::raw('RIGHT([ServiceMaster].[Mobile],11) AS Mobile'),'ServiceMaster.DistrictCode','ServiceMaster.TerritoryCode','ServiceMaster.ModelCode',
                DB::raw('CONVERT(VARCHAR(50), ServiceMaster.PurchaseDate, 103) AS PurchaseDate'),'ServiceMaster.ServiceTime','ServiceMaster.ActionTaken',
                DB::raw('CONVERT(VARCHAR(50), ServiceMaster.AttendDate, 103) AS AttendDate'),
                DB::raw('CAST(ServiceMaster.ServiceCharge AS decimal(18,2)) AS ServiceCharge'),
                DB::raw('CONVERT(VARCHAR(255), ServiceMaster.MRNo) AS MRNo'),'ServiceMaster.Status',
                DB::raw('CONVERT(VARCHAR(255), ServiceMaster.WarrantyCardNo) AS WarrantyCardNo'),'ServiceMaster.EntryBy',
                DB::raw("CONVERT(VARCHAR(50), ServiceMaster.EntryDate, 103) +' '+ CONVERT(VARCHAR(50), ServiceMaster.EntryDate, 114) EntryDate"), 'T.TTYCode', 'T.TTYName', 'M.BrandCode', 'M.Brandname', 'U.username AS StaffName', 'D.DistrictName',
                DB::raw('ISNULL(P.Point,0) AS Point'),
                'P.Feedback','OutsourceService.TotalCost as OutsourceTotalCost','OutsourceService.TechnicianName','OutsourceService.TechnicianMobile','OutsourceService.TechnicianAddress')
            ->orderBy('ServiceMaster.'.$columns[$column], $dir);
        if($searchValue){
            $query->where(function($query) use ($searchValue){
                $query->where('ServiceMaster.StaffID', 'like', '%'.$searchValue.'%')
                    ->orWhere('U.username', 'like', '%'.$searchValue.'%')
                    ->orWhere('D.DistrictName', 'like', '%'.$searchValue.'%')
                    ->orWhere('ServiceMaster.MRNo', 'like', '%'.$searchValue.'%')
                    ->orWhere('ServiceMaster.CustomerName', 'like', '%'.$searchValue.'%')
                    ->orWhere('T.TTYName', 'like', '%'.$searchValue.'%')
                    ->orWhere('ServiceMaster.ServiceTime', 'like', '%'.$searchValue.'%');
            });
        }

        if($fromDate && $toDate){
            $query->whereBetween(DB::raw('CONVERT(date, ServiceMaster.EntryDate)'), [$fromDate, $toDate]);
        }

        if(!empty($serviceTime)){
            $query->where('ServiceMaster.ServiceTime', '=', $serviceTime);
        }
        // return $query;

        $query->where('ServiceType','Outsource');

        $allServices = $query->paginate(10);

        // dd(DB::getQueryLog()); // Show results of log

        return ['data'=>$allServices, 'draw'=>$draw];

    }

    public function getOutsourceServiceTest($columns, $length, $column, $dir, $searchValue, $draw, $fromDate, $toDate, $serviceTime){
        $query = Report::join('ViewDistrict AS D', 'D.DistrictCode', '=', 'ServiceMaster.DistrictCode')
            ->join('users AS U', 'U.staffid', '=', 'ServiceMaster.StaffID')
            ->join('Territory AS T', 'T.TTYCode', '=', 'ServiceMaster.TerritoryCode')
            ->join('Model AS M', 'M.BrandCode', '=', 'ServiceMaster.ModelCode')
            ->leftjoin('PointDetails AS P', 'P.ServiceMasterID', '=', 'ServiceMaster.ServiceMasterID')
            ->join('OutsourceService','OutsourceService.ServiceMasterID','ServiceMaster.ServiceMasterID')
            ->select('ServiceMaster.ServiceMasterID','ServiceMaster.Remarks','ServiceMaster.StaffID','ServiceMaster.CustomerName','ServiceMaster.Address',
                DB::raw('RIGHT([ServiceMaster].[Mobile],11) AS Mobile'),'ServiceMaster.DistrictCode','ServiceMaster.TerritoryCode','ServiceMaster.ModelCode',
                DB::raw('CONVERT(VARCHAR(50), ServiceMaster.PurchaseDate, 103) AS PurchaseDate'),'ServiceMaster.ServiceTime','ServiceMaster.ActionTaken',
                DB::raw('CONVERT(VARCHAR(50), ServiceMaster.AttendDate, 103) AS AttendDate'),
                DB::raw('CAST(ServiceMaster.ServiceCharge AS decimal(18,2)) AS ServiceCharge'),
                DB::raw('CONVERT(VARCHAR(255), ServiceMaster.MRNo) AS MRNo'),'ServiceMaster.Status',
                DB::raw('CONVERT(VARCHAR(255), ServiceMaster.WarrantyCardNo) AS WarrantyCardNo'),'ServiceMaster.EntryBy',
                DB::raw("CONVERT(VARCHAR(50), ServiceMaster.EntryDate, 103) +' '+ CONVERT(VARCHAR(50), ServiceMaster.EntryDate, 114) EntryDate"), 'T.TTYCode', 'T.TTYName', 'M.BrandCode', 'M.Brandname', 'U.username AS StaffName', 'D.DistrictName',
                DB::raw('ISNULL(P.Point,0) AS Point'),
                'P.Feedback','OutsourceService.TotalCost as OutsourceTotalCost','OutsourceService.TechnicianName','OutsourceService.TechnicianMobile','OutsourceService.TechnicianAddress')
            ->orderBy('ServiceMaster.'.$columns[$column], $dir);
        if($searchValue){
            $query->where(function($query) use ($searchValue){
                $query->where('ServiceMaster.StaffID', 'like', '%'.$searchValue.'%')
                    ->orWhere('U.username', 'like', '%'.$searchValue.'%')
                    ->orWhere('D.DistrictName', 'like', '%'.$searchValue.'%')
                    ->orWhere('ServiceMaster.MRNo', 'like', '%'.$searchValue.'%')
                    ->orWhere('ServiceMaster.CustomerName', 'like', '%'.$searchValue.'%')
                    ->orWhere('T.TTYName', 'like', '%'.$searchValue.'%')
                    ->orWhere('ServiceMaster.ServiceTime', 'like', '%'.$searchValue.'%');
            });
        }

        if($fromDate && $toDate){
            $query->whereBetween(DB::raw('CONVERT(date, ServiceMaster.EntryDate)'), [$fromDate, $toDate]);
        }

        if(!empty($serviceTime)){
            $query->where('ServiceMaster.ServiceTime', '=', $serviceTime);
        }

        $query->where('ServiceType','Outsource');

        $allServices = $query->get();

        return ['data'=>$allServices, 'draw'=>$draw];

    }

    public function getServiceDetails($columns, $length, $column, $dir, $searchValue, $draw, $fromDate, $toDate, $serviceTime){

        // DB::enableQueryLog(); // Enable query log

        $query = Report::join('ViewDistrict AS D', 'D.DistrictCode', '=', 'ServiceMaster.DistrictCode')
                        ->join('users AS U', 'U.staffid', '=', 'ServiceMaster.StaffID')
                        ->join('Territory AS T', 'T.TTYCode', '=', 'ServiceMaster.TerritoryCode')
                        ->join('Model AS M', 'M.BrandCode', '=', 'ServiceMaster.ModelCode')
                        ->join('ServiceDetails AS SD', 'SD.ServiceMasterID', '=', 'ServiceMaster.ServiceMasterID')
                        ->join('Product', 'Product.ProductCode', 'SD.SparePartsCode')
                        ->leftjoin('PointDetails AS P', 'P.ServiceMasterID', '=', 'ServiceMaster.ServiceMasterID')
                        ->select('ServiceMaster.ServiceMasterID','ServiceMaster.Remarks','ServiceMaster.StaffID','ServiceMaster.CustomerName','ServiceMaster.Address',
                                DB::raw('RIGHT([ServiceMaster].[Mobile],11) AS Mobile'),'ServiceMaster.DistrictCode','ServiceMaster.TerritoryCode','ServiceMaster.ModelCode',
                                DB::raw('CONVERT(VARCHAR(23), ServiceMaster.PurchaseDate, 103) AS PurchaseDate'),'ServiceMaster.ServiceTime','ServiceMaster.ActionTaken',
                                DB::raw('CONVERT(VARCHAR(23), ServiceMaster.AttendDate, 103) AS AttendDate'),
                                DB::raw('CAST(ServiceMaster.ServiceCharge AS decimal(1,2)) AS ServiceCharge'),
                                DB::raw('CONVERT(VARCHAR(100), ServiceMaster.MRNo) AS MRNo'),'ServiceMaster.Status',
                                DB::raw('CONVERT(VARCHAR(100), ServiceMaster.WarrantyCardNo) AS WarrantyCardNo'),'ServiceMaster.EntryBy',
                                DB::raw("CONVERT(VARCHAR(23), ServiceMaster.EntryDate, 103) +' '+ CONVERT(VARCHAR(23), ServiceMaster.EntryDate, 114) EntryDate"), 'T.TTYCode', 'T.TTYName', 'M.BrandCode', 'M.Brandname', 'U.username AS StaffName', 'D.DistrictName',
                                DB::raw('ISNULL(P.Point,0) AS Point'),'P.Feedback', 'SD.SparePartsCode', 'SD.QuantityUsed', 'Product.ProductName')
                        ->groupByRaw('[ServiceMaster].[ServiceMasterID],[ServiceMaster].[StaffID],[ServiceMaster].[CustomerName],[ServiceMaster].[Address],[ServiceMaster].[Mobile],
                                        [ServiceMaster].[DistrictCode], [ServiceMaster].[TerritoryCode], [ServiceMaster].[ModelCode],[ServiceMaster].[PurchaseDate],[ServiceMaster].[ServiceTime],
                                        [ServiceMaster].[ActionTaken], [ServiceMaster].[AttendDate],[ServiceMaster].[ServiceCharge],[ServiceMaster].[MRNo],[ServiceMaster].[Status],
                                        [ServiceMaster].[WarrantyCardNo],[ServiceMaster].[EntryBy],[ServiceMaster].[EntryDate],[T].[TTYCode],[T].[TTYName], [M].[BrandCode],[M].[Brandname],
                                        [U].[username],[D].[DistrictName],[P].[Point], [P].[Feedback], [SD].[SparePartsCode], [SD].[QuantityUsed], [Product].[ProductName]')
                        ->orderBy('ServiceMaster.'.$columns[$column], $dir);
        if($searchValue){
            $query->where(function($query) use ($searchValue){
                $query->where('ServiceMaster.StaffID', 'like', '%'.$searchValue.'%')
                        ->orWhere('U.username', 'like', '%'.$searchValue.'%')
                        ->orWhere('D.DistrictName', 'like', '%'.$searchValue.'%')
                        ->orWhere('ServiceMaster.MRNo', 'like', '%'.$searchValue.'%')
                        ->orWhere('ServiceMaster.CustomerName', 'like', '%'.$searchValue.'%')
                        ->orWhere('T.TTYName', 'like', '%'.$searchValue.'%')
                        ->orWhere('ServiceMaster.ServiceTime', 'like', '%'.$searchValue.'%');
            });
        }

        if($fromDate && $toDate){
            $query->whereBetween(DB::raw('CONVERT(date, ServiceMaster.EntryDate)'), [$fromDate, $toDate]);
        }

        if(!empty($serviceTime)){
            $query->where('ServiceMaster.ServiceTime', '=', $serviceTime);
        }

        $serviceDetailsData = $query->paginate($length);

        // dd(DB::getQueryLog()); // Show results of log

        return ['data'=>$serviceDetailsData, 'draw'=>$draw];

    }

    public function getDailySparePartsReport($columns, $length, $column, $dir, $searchValue, $draw, $fromDate, $toDate){

        $query = ServiceDetails::join('ServiceMaster', 'ServiceMaster.ServiceMasterID', '=', 'ServiceDetails.ServiceMasterID')
                                ->join('Product AS P', 'P.ProductCode', '=', 'ServiceDetails.SparePartsCode')
                                ->leftjoin('SparePartsStock AS S', 'S.ProductCode', '=', 'ServiceDetails.SparePartsCode')
                                ->join('users', 'users.staffid', '=', 'S.StaffID')
                                ->join('Territory AS T', 'T.TTYCode', '=', 'users.territorycode')
                                ->select(DB::raw('ROW_NUMBER() OVER (ORDER BY users.staffid DESC) SL'),'users.staffid', 'users.username AS StaffName', 'T.TTYName AS TerritoryName', 'ServiceDetails.SparePartsCode',
                                        'P.ProductName AS SparePartsName', 'S.Opening', 'S.Recive',
                                        DB::raw('ISNULL((S.Opening+S.Recive),0) AS TotalQuantity'),
                                        DB::raw('ISNULL(SUM(ServiceDetails.QuantityUsed),0) AS UsedQuantity'),
                                        DB::raw('ISNULL(((S.Opening+S.Recive)-ISNULL(SUM(ServiceDetails.QuantityUsed),0)),0) AS Closing'))
                                ->groupBy('ServiceDetails.SparePartsCode','S.Recive','S.Opening','P.ProductName','users.staffid','users.username','T.TTYName')
                                ->orderBy($columns[$column], $dir);
        if($searchValue){
            $query->where(function($query) use ($searchValue){
                $query->where('users.staffid', 'like', '%'.$searchValue.'%')
                        ->orWhere('users.username', 'like', '%'.$searchValue.'%')
                        ->orWhere('P.ProductName', 'like', '%'.$searchValue.'%');
            });
        }

        if($fromDate && $toDate){
            $query->whereBetween(DB::raw('CONVERT(date, ServiceMaster.EntryDate)'), [$fromDate, $toDate]);
        }

        $dailySparePartsReportData = $query->paginate($length);

        return ['data'=>$dailySparePartsReportData, 'draw'=>$draw];

    }

    // public function getServiceDetails($serviceMasterId){

    //     $result = DB::table('ServiceDetails AS SD')
    //                 ->join('Product AS P', 'P.ProductCode', 'SD.SparePartsCode')
    //                 ->select('SD.*', 'P.ProductName')
    //                 ->where('SD.ServiceMasterID', $serviceMasterId)
    //                 ->get();

    //     return $result;

    // }

    public function excelExport(Request $request){
        $fromDate = $request->input('fromDate');
        //$carbon_from_date = Carbon::createFromFormat('Y-m-d H:i:s', $fromDate.' 00:00:00')->toDateTimeString();
        $toDate = $request->input('toDate');
        //$carbon_to_date = Carbon::createFromFormat('Y-m-d H:i:s', $toDate.' 59:59:00')->toDateTimeString();

        $serviceTime = $request->input('serviceTime');
        $business = $request->input('business');
        $query = Report::leftJoin('ViewDistrict AS D', 'D.DistrictCode', '=', 'ServiceMaster.DistrictCode')
                        ->join('users AS U', 'U.staffid', '=', 'ServiceMaster.StaffID')
                        ->join('Territory AS T', 'T.TTYCode', '=', 'ServiceMaster.TerritoryCode')
                        ->join('Model AS M', 'M.BrandCode', '=', 'ServiceMaster.ModelCode')
                        ->leftjoin('PointDetails AS P', 'P.ServiceMasterID', '=', 'ServiceMaster.ServiceMasterID')
            ->select('ServiceMaster.ServiceMasterID','ServiceMaster.Remarks','ServiceMaster.StaffID','ServiceMaster.CustomerName','ServiceMaster.Address','ServiceMaster.Mobile',
                'ServiceMaster.DistrictCode','ServiceMaster.TerritoryCode','ServiceMaster.ModelCode','ServiceMaster.PurchaseDate','ServiceMaster.ServiceTime','ServiceMaster.ActionTaken',
                'ServiceMaster.AttendDate','ServiceMaster.ServiceCharge','ServiceMaster.MRNo','ServiceMaster.Status','ServiceMaster.WarrantyCardNo','ServiceMaster.EntryBy',
                'ServiceMaster.EntryDate','T.TTYCode','T.TTYName','M.BrandCode','M.Brandname','U.username AS StaffName','D.DistrictName','P.Point',DB::raw('ISNULL(P.Point,0) AS Point'),'P.Feedback')
                        ->select('ServiceMaster.ServiceMasterID','ServiceMaster.Remarks','ServiceMaster.StaffID','ServiceMaster.CustomerName','ServiceMaster.Address',
                                DB::raw('RIGHT([ServiceMaster].[Mobile],11) AS Mobile'),'ServiceMaster.DistrictCode','ServiceMaster.TerritoryCode','ServiceMaster.ModelCode',
                                DB::raw('CONVERT(VARCHAR(23), ServiceMaster.PurchaseDate, 103) AS PurchaseDate'),'ServiceMaster.ServiceTime','ServiceMaster.ActionTaken',
                                DB::raw('CONVERT(VARCHAR(23), ServiceMaster.AttendDate, 103) AS AttendDate'),'ServiceMaster.ServiceCharge AS ServiceCharge',
                                //DB::raw('CAST(ServiceMaster.ServiceCharge AS decimal(10,2)) AS ServiceCharge'),
                                DB::raw('CONVERT(VARCHAR(100), ServiceMaster.MRNo) AS MRNo'),'ServiceMaster.Status',
                                DB::raw('CONVERT(VARCHAR(100), ServiceMaster.WarrantyCardNo) AS WarrantyCardNo'),'ServiceMaster.EntryBy',
                                DB::raw("CONVERT(VARCHAR(23), ServiceMaster.EntryDate, 103) +' '+ CONVERT(VARCHAR(23), ServiceMaster.EntryDate, 114) EntryDate"), 'T.TTYCode', 'T.TTYName', 'M.BrandCode', 'M.Brandname', 'U.username AS StaffName', 'D.DistrictName',
                                DB::raw('ISNULL(P.Point,0) AS Point'),'P.Feedback',
                                'ServiceMaster.Business')
                        ->groupByRaw('[ServiceMaster].[ServiceMasterID],[ServiceMaster].[Remarks],[ServiceMaster].[StaffID],[ServiceMaster].[CustomerName],[ServiceMaster].[Address],[ServiceMaster].[Mobile],
                                        [ServiceMaster].[DistrictCode], [ServiceMaster].[TerritoryCode], [ServiceMaster].[ModelCode],[ServiceMaster].[PurchaseDate],[ServiceMaster].[ServiceTime],
                                        [ServiceMaster].[ActionTaken], [ServiceMaster].[AttendDate],[ServiceMaster].[ServiceCharge],[ServiceMaster].[MRNo],[ServiceMaster].[Status],
                                        [ServiceMaster].[WarrantyCardNo],[ServiceMaster].[EntryBy],[ServiceMaster].[EntryDate],[ServiceMaster].[Business],[T].[TTYCode],[T].[TTYName], [M].[BrandCode],[M].[Brandname],
                                        [U].[username],[D].[DistrictName],[P].[Point], [P].[Feedback]')
                        ->orderBy('ServiceMaster.ServiceMasterID','desc');


        if($fromDate && $toDate){
            $query->whereDate('ServiceMaster.EntryDate', '>=', $fromDate);
            $query->whereDate('ServiceMaster.EntryDate', '<=', $toDate);
        }

        if(!empty($serviceTime)){
            $query->where('ServiceMaster.ServiceTime', '=', $serviceTime);
        }

        if(!empty($business)){
            $query->where('ServiceMaster.Business', '=', $business);
        }

        $serviceData = $query
//            ->where('IsOTPVerified',1)
            ->get();

       //  dd(DB::getQueryLog()); // Show results of log

        // echo '<pre>';print_r($data);die;
        return response()->json($serviceData);
        // return Excel::download($serviceData, 'servicelist.xlsx');

    }

    public function excelSelfExport(Request $request){
        $fromDate = $request->input('fromDate');
        //$carbon_from_date = Carbon::createFromFormat('Y-m-d H:i:s', $fromDate.' 00:00:00')->toDateTimeString();
        $toDate = $request->input('toDate');
        //$carbon_to_date = Carbon::createFromFormat('Y-m-d H:i:s', $toDate.' 59:59:00')->toDateTimeString();

        $serviceTime = $request->input('serviceTime');
        $query = Report::join('SelfService','SelfService.ServiceMasterId','ServiceMaster.ServiceMasterID')
            ->join('ViewDistrict AS D', 'D.DistrictCode', '=', 'ServiceMaster.DistrictCode')
            ->join('users AS U', 'U.staffid', '=', 'ServiceMaster.StaffID')
            ->join('Territory AS T', 'T.TTYCode', '=', 'ServiceMaster.TerritoryCode')
            ->join('Model AS M', 'M.BrandCode', '=', 'ServiceMaster.ModelCode')
            ->leftjoin('PointDetails AS P', 'P.ServiceMasterID', '=', 'ServiceMaster.ServiceMasterID')
            ->select('ServiceMaster.ServiceMasterID','ServiceMaster.Remarks','ServiceMaster.StaffID','ServiceMaster.CustomerName','ServiceMaster.Address',
                DB::raw('RIGHT([ServiceMaster].[Mobile],11) AS Mobile'),'ServiceMaster.DistrictCode','ServiceMaster.TerritoryCode','ServiceMaster.ModelCode',
                DB::raw('CONVERT(VARCHAR(23), ServiceMaster.PurchaseDate, 103) AS PurchaseDate'),'ServiceMaster.ServiceTime','ServiceMaster.ActionTaken',
                DB::raw('CONVERT(VARCHAR(23), ServiceMaster.AttendDate, 103) AS AttendDate'),'ServiceMaster.ServiceCharge AS ServiceCharge',
                //DB::raw('CAST(ServiceMaster.ServiceCharge AS decimal(10,2)) AS ServiceCharge'),
                DB::raw('CONVERT(VARCHAR(100), ServiceMaster.MRNo) AS MRNo'),'ServiceMaster.Status',
                DB::raw('CONVERT(VARCHAR(100), ServiceMaster.WarrantyCardNo) AS WarrantyCardNo'),'ServiceMaster.EntryBy',
                DB::raw("CONVERT(VARCHAR(23), ServiceMaster.EntryDate, 103) +' '+ CONVERT(VARCHAR(23), ServiceMaster.EntryDate, 114) EntryDate"), 'T.TTYCode', 'T.TTYName', 'M.BrandCode', 'M.Brandname', 'U.username AS StaffName', 'D.DistrictName',
                DB::raw('ISNULL(P.Point,0) AS Point'),'P.Feedback','SelfService.TotalCost')
            ->orderBy('ServiceMaster.ServiceMasterID','desc');


        if($fromDate && $toDate){
            $query->whereDate('ServiceMaster.EntryDate', '>=', $fromDate);
            $query->whereDate('ServiceMaster.EntryDate', '<=', $toDate);
        }

        if(!empty($serviceTime)){
            $query->where('ServiceMaster.ServiceTime', '=', $serviceTime);
        }

        $serviceData = $query->get();

        //  dd(DB::getQueryLog()); // Show results of log

        // echo '<pre>';print_r($data);die;
        return response()->json($serviceData);
        // return Excel::download($serviceData, 'servicelist.xlsx');

    }

    public function excelOutsourceExport(Request $request){
        $fromDate = $request->input('fromDate');
        //$carbon_from_date = Carbon::createFromFormat('Y-m-d H:i:s', $fromDate.' 00:00:00')->toDateTimeString();
        $toDate = $request->input('toDate');
        //$carbon_to_date = Carbon::createFromFormat('Y-m-d H:i:s', $toDate.' 59:59:00')->toDateTimeString();

        $serviceTime = $request->input('serviceTime');
        $query = Report::join('OutsourceService','OutsourceService.ServiceMasterId','ServiceMaster.ServiceMasterID')
            ->leftJoin('ViewDistrict AS D', 'D.DistrictCode', '=', 'ServiceMaster.DistrictCode')
            ->join('users AS U', 'U.staffid', '=', 'ServiceMaster.StaffID')
            ->join('Territory AS T', 'T.TTYCode', '=', 'ServiceMaster.TerritoryCode')
            ->join('Model AS M', 'M.BrandCode', '=', 'ServiceMaster.ModelCode')
            ->leftjoin('PointDetails AS P', 'P.ServiceMasterID', '=', 'ServiceMaster.ServiceMasterID')
            ->select('ServiceMaster.ServiceMasterID','ServiceMaster.Remarks','ServiceMaster.StaffID','ServiceMaster.CustomerName','ServiceMaster.Address',
                DB::raw('RIGHT([ServiceMaster].[Mobile],11) AS Mobile'),'ServiceMaster.DistrictCode','ServiceMaster.TerritoryCode','ServiceMaster.ModelCode',
                DB::raw('CONVERT(VARCHAR(23), ServiceMaster.PurchaseDate, 103) AS PurchaseDate'),'ServiceMaster.ServiceTime','ServiceMaster.ActionTaken',
                DB::raw('CONVERT(VARCHAR(23), ServiceMaster.AttendDate, 103) AS AttendDate'),'ServiceMaster.ServiceCharge AS ServiceCharge',
                //DB::raw('CAST(ServiceMaster.ServiceCharge AS decimal(10,2)) AS ServiceCharge'),
                DB::raw('CONVERT(VARCHAR(100), ServiceMaster.MRNo) AS MRNo'),'ServiceMaster.Status',
                DB::raw('CONVERT(VARCHAR(100), ServiceMaster.WarrantyCardNo) AS WarrantyCardNo'),'ServiceMaster.EntryBy',
                DB::raw("CONVERT(VARCHAR(23), ServiceMaster.EntryDate, 103) +' '+ CONVERT(VARCHAR(23), ServiceMaster.EntryDate, 114) EntryDate"), 'T.TTYCode', 'T.TTYName', 'M.BrandCode', 'M.Brandname', 'U.username AS StaffName', 'D.DistrictName',
                DB::raw('ISNULL(P.Point,0) AS Point'),'P.Feedback','TechnicianName','TechnicianAddress','TechnicianMobile','OutsourceService.TotalCost')
            ->orderBy('ServiceMaster.ServiceMasterID','desc');


        if($fromDate && $toDate){
            $query->whereDate('ServiceMaster.EntryDate', '>=', $fromDate);
            $query->whereDate('ServiceMaster.EntryDate', '<=', $toDate);
        }

        if(!empty($serviceTime)){
            $query->where('ServiceMaster.ServiceTime', '=', $serviceTime);
        }

        $serviceData = $query->get();

        //  dd(DB::getQueryLog()); // Show results of log

        // echo '<pre>';print_r($data);die;
        return response()->json($serviceData);
        // return Excel::download($serviceData, 'servicelist.xlsx');

    }

    public function excelExportDetailReport(Request $request){
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');
        $serviceTime = $request->input('serviceTime');

        $query = Report::join('ViewDistrict AS D', 'D.DistrictCode', '=', 'ServiceMaster.DistrictCode')
                        ->join('users AS U', 'U.staffid', '=', 'ServiceMaster.StaffID')
                        ->join('Territory AS T', 'T.TTYCode', '=', 'ServiceMaster.TerritoryCode')
                        ->join('Model AS M', 'M.BrandCode', '=', 'ServiceMaster.ModelCode')
                        ->join('ServiceDetails AS SD', 'SD.ServiceMasterID', '=', 'ServiceMaster.ServiceMasterID')
                        ->join('Product', 'Product.ProductCode', 'SD.SparePartsCode')
                        ->leftjoin('PointDetails AS P', 'P.ServiceMasterID', '=', 'ServiceMaster.ServiceMasterID')
            ->select('ServiceMaster.ServiceMasterID','ServiceMaster.Remarks','ServiceMaster.StaffID','ServiceMaster.CustomerName','ServiceMaster.Address','ServiceMaster.Mobile',
                'ServiceMaster.DistrictCode','ServiceMaster.TerritoryCode','ServiceMaster.ModelCode','ServiceMaster.PurchaseDate','ServiceMaster.ServiceTime','ServiceMaster.ActionTaken',
                'ServiceMaster.AttendDate','ServiceMaster.ServiceCharge','ServiceMaster.MRNo','ServiceMaster.Status','ServiceMaster.WarrantyCardNo','ServiceMaster.EntryBy',
                'ServiceMaster.EntryDate','T.TTYCode','T.TTYName','M.BrandCode','M.Brandname','U.username AS StaffName','D.DistrictName','P.Point',DB::raw('ISNULL(P.Point,0) AS Point'),'P.Feedback')
                        ->select('ServiceMaster.ServiceMasterID','ServiceMaster.StaffID','ServiceMaster.CustomerName',
                                    'ServiceMaster.Address',
                                    DB::raw('RIGHT([ServiceMaster].[Mobile],11) AS Mobile'),'ServiceMaster.DistrictCode','ServiceMaster.TerritoryCode','ServiceMaster.ModelCode',
                                    DB::raw('CONVERT(VARCHAR(23), ServiceMaster.PurchaseDate, 103) AS PurchaseDate'),'ServiceMaster.ServiceTime','ServiceMaster.ActionTaken',
                                    DB::raw('CONVERT(VARCHAR(23), ServiceMaster.AttendDate, 103) AS AttendDate'),'ServiceMaster.ServiceCharge as ServiceCharge',
                                    //DB::raw('CAST(ServiceMaster.ServiceCharge AS decimal(10,2)) AS ServiceCharge'),
                                    DB::raw('CONVERT(VARCHAR(100), ServiceMaster.MRNo) AS MRNo'),'ServiceMaster.Status',
                                    DB::raw('CONVERT(VARCHAR(100), ServiceMaster.WarrantyCardNo) AS WarrantyCardNo'),'ServiceMaster.EntryBy',
                                    DB::raw("CONVERT(VARCHAR(23), ServiceMaster.EntryDate, 103) +' '+ CONVERT(VARCHAR(23), ServiceMaster.EntryDate, 114) EntryDate"), 'T.TTYCode', 'T.TTYName', 'M.BrandCode', 'M.Brandname', 'U.username AS StaffName', 'D.DistrictName',
                                    DB::raw('ISNULL(P.Point,0) AS Point'),'P.Feedback', 'SD.SparePartsCode', 'SD.QuantityUsed', 'Product.ProductName')
                        ->groupByRaw('[ServiceMaster].[ServiceMasterID],[ServiceMaster].[StaffID],[ServiceMaster].[CustomerName],[ServiceMaster].[Address],[ServiceMaster].[Mobile],
                                        [ServiceMaster].[DistrictCode], [ServiceMaster].[TerritoryCode], [ServiceMaster].[ModelCode],[ServiceMaster].[PurchaseDate],[ServiceMaster].[ServiceTime],
                                        [ServiceMaster].[ActionTaken], [ServiceMaster].[AttendDate],[ServiceMaster].[ServiceCharge],[ServiceMaster].[MRNo],[ServiceMaster].[Status],
                                        [ServiceMaster].[WarrantyCardNo],[ServiceMaster].[EntryBy],[ServiceMaster].[EntryDate],[T].[TTYCode],[T].[TTYName], [M].[BrandCode],[M].[Brandname],
                                        [U].[username],[D].[DistrictName],[P].[Point], [P].[Feedback], [SD].[SparePartsCode], [SD].[QuantityUsed], [Product].[ProductName]')
                        ->orderBy('ServiceMaster.ServiceMasterID','desc');

        if($fromDate && $toDate){
            $query->whereBetween(DB::raw('CONVERT(date, ServiceMaster.EntryDate)'), [$fromDate, $toDate]);
        }

        if(!empty($serviceTime)){
            $query->where('ServiceMaster.ServiceTime', '=', $serviceTime);
        }

        $serviceDetailData = $query->get();

        // echo '<pre>';print_r($data);die;
        return response()->json($serviceDetailData);
        // return Excel::download($serviceData, 'servicelist.xlsx');

    }

    public function excelExportDailySparePartsReport(Request $request){
        if($request->input('fromDate') && $request->input('toDate')){
            $fromDate = $request->input('fromDate');
            $toDate = $request->input('toDate');
        }else{
            $fromDate = date('Y-m-d');
            $toDate = date('Y-m-d');;
        }


        $query = ServiceDetails::join('ServiceMaster', 'ServiceMaster.ServiceMasterID', '=', 'ServiceDetails.ServiceMasterID')
                                ->join('Product AS P', 'P.ProductCode', '=', 'ServiceDetails.SparePartsCode')
                                ->leftjoin('SparePartsStock AS S', 'S.ProductCode', '=', 'ServiceDetails.SparePartsCode')
                                ->join('users', 'users.staffid', '=', 'S.StaffID')
                                ->join('Territory AS T', 'T.TTYCode', '=', 'users.territorycode')
                                ->select(DB::raw('ROW_NUMBER() OVER (ORDER BY ServiceMaster.ServiceMasterID DESC) SL'),'users.staffid', 'users.username AS StaffName', 'T.TTYName AS TerritoryName', 'ServiceDetails.SparePartsCode',
                                        'P.ProductName AS SparePartsName', 'S.Opening', 'S.Recive',
                                        DB::raw('ISNULL((S.Opening+S.Recive),0) AS TotalQuantity'),
                                        DB::raw('ISNULL(SUM(ServiceDetails.QuantityUsed),0) AS UsedQuantity'),
                                        DB::raw('ISNULL(((S.Opening+S.Recive)-ISNULL(SUM(ServiceDetails.QuantityUsed),0)),0) AS Closing'))
                                ->groupBy('ServiceDetails.SparePartsCode','S.Recive','S.Opening','P.ProductName','users.staffid','users.username','T.TTYName', 'ServiceMaster.ServiceMasterID')
                        ->orderBy('ServiceMaster.ServiceMasterID','desc');

        if($fromDate && $toDate){
            $query->whereBetween(DB::raw('CONVERT(date, ServiceMaster.EntryDate)'), [$fromDate, $toDate]);
        }

        $dailySparePartsReportData = $query->get();

        // echo '<pre>';print_r($data);die;
        return response()->json($dailySparePartsReportData);
        // return Excel::download($serviceData, 'servicelist.xlsx');

    }

    public function updateOutsourceServiceTotalCost(Request $request)
    {
        $request->validate([
            'serviceMasterId' => 'required',
            'totalCost' => 'required|numeric|min:0',
        ]);

        $outsourceService = OutsourceService::where('ServiceMasterID', $request->serviceMasterId)->first();

        if (!$outsourceService) {
            return response()->json(['status' => false, 'message' => 'Record not found.'], 404);
        }

        $outsourceService->TotalCost = $request->totalCost;
        $outsourceService->save();

        return response()->json(['status' => true, 'message' => 'Total cost updated successfully.']);
    }

    public function updateSelfServiceTotalCost(Request $request)
    {
        $request->validate([
            'serviceMasterId' => 'required',
            'totalCost' => 'required|numeric|min:0',
        ]);

        $selfService = SelfService::where('ServiceMasterID', $request->serviceMasterId)->first();

        if (!$selfService) {
            return response()->json(['status' => false, 'message' => 'Record not found.'], 404);
        }

        $selfService->TotalCost = $request->totalCost;
        $selfService->save();

        return response()->json(['status' => true, 'message' => 'Total cost updated successfully.']);
    }

    protected function guard()
    {
        return Auth::guard('api');
    }
}
