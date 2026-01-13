<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\SparePartsStock;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class SparePartsStockController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->user = $this->guard()->user();
    }

    public function getStaffIds(){

        try {
            return response()->json([
                'status' => true,
                'userinfo' => $this->getUsersInfo(),
            ], 200)->header('Content-Type', 'application/json');
        } catch (NotFoundHttpException $e) {
            return response()->json(['status' => false, 'message' => 'Nothing found', 'error' => $e], Response::HTTP_NOT_FOUND)
                ->header('Content-Type', 'application/json');
        }

    }

    public function addStock(Request $request)
    {
        // $input = $request->all();
        // print_r($input);die;
        $validator = Validator::make($request->all(), [
            'StaffID' => 'required|string',
            'ProductCode' => 'required|string',
            'Period' => 'required|string',
            'Recive' => 'required|Numeric'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $opening = 0;
        if($request->Opening != null){
            $opening = $request->Opening;
        }

        $userExists = SparePartsStock::where('StaffID', '=', $request->StaffID)->where('ProductCode', '=', $request->ProductCode)->first();

        if($userExists == null){
            SparePartsStock::create(
                array_merge(
                    $validator->validated(),
                    ['Period' => str_replace("-", "", $request->Period)],
                    ['Opening' => $opening],
                    ['ReceiveDate' => Carbon::now()],
                    ['EntryBy' => $request->EntryBy],
                    ['EntryDate' => Carbon::now()]
                )
            );
            return response()->json(['status' => true, 'message' => 'Stock Added successfully'], 200)
                    ->header('Content-Type', 'application/json');
        }else{
            SparePartsStock::where('StaffID', $request->StaffID)
                            ->where('ProductCode', '=', $request->ProductCode)
                            ->update(['Opening' => $opening, 'Recive' => $request->Recive,
                                        'EditBy' => $request->EntryBy, 'EditDate' => Carbon::now()]);
            return response()->json(['status' => true, 'message' => 'Stock updated successfully'], 200)
                            ->header('Content-Type', 'application/json');
        }
        return false;
    }

    public function stockList(Request $request){

        $columns = ['StaffID', 'username', 'ProductCode', 'ProductName', 'Period', 'Recive', 'Opening', 'ReceiveDate'];
        $length = $request->input('length');
        $column = $request->input('column');
        $dir = $request->input('dir');
        $searchValue = $request->input('search');
        $draw = $request->input('draw');
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');

        try {
            return response()->json([
                'status' => true,
                'stockList' => $this->getStockList($columns, $length, $column, $dir, $searchValue, $draw, $fromDate, $toDate),
            ], 200)->header('Content-Type', 'application/json');
        } catch (NotFoundHttpException $e) {
            return response()->json(['status' => false, 'message' => 'Nothing found', 'error' => $e], Response::HTTP_NOT_FOUND)
                ->header('Content-Type', 'application/json');
        }
    }

    public function getStockList($columns, $length, $column, $dir, $searchValue, $draw, $fromDate, $toDate){

        $query = SparePartsStock::join('Product', 'Product.ProductCode', '=', 'SparePartsStock.ProductCode')
                        ->join('users', 'users.staffid', '=', 'SparePartsStock.StaffID')
                        ->select('SparePartsStock.*', 'users.staffid AS StaffID', 'users.username AS UserName', 'Product.*')
                        ->orderBy("SparePartsStock.$columns[$column]", $dir);
        if($searchValue){
            $query->where(function($query) use ($searchValue){
                $query->where('SparePartsStock.StaffID', 'like', '%'.$searchValue.'%');
            });
        }

        if($fromDate && $toDate){
            $query->whereBetween('SparePartsStock.Period', [$fromDate, $toDate]);
        }

        $allServices = $query->paginate($length);

        return ['data'=>$allServices, 'draw'=>$draw];
    }

    public function getUsersInfo(){

        $userinfo = DB::table('users')->select('staffid AS StaffID','username AS StaffName')->where('usertype', '=', 'U')->get();

        return $userinfo;

    }

    public function excelExportStockReport(Request $request){
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');

        $query = SparePartsStock::join('Product', 'Product.ProductCode', '=', 'SparePartsStock.ProductCode')
                        ->join('users', 'users.staffid', '=', 'SparePartsStock.StaffID')
                        ->select(DB::raw('ROW_NUMBER() OVER (ORDER BY SparePartsStock.StaffID DESC) SL'),'SparePartsStock.*', 'users.staffid AS StaffID', 'users.username AS UserName', 'Product.*')
                        ->orderBy('SparePartsStock.StaffID','desc');

        if($fromDate && $toDate){
            $query->whereBetween('SparePartsStock.EntryDate', [$fromDate, $toDate]);
        }

        $serviceData = $query->get();

        // echo '<pre>';print_r($data);die;
        return response()->json($serviceData);
        // return Excel::download($serviceData, 'servicelist.xlsx');

    }

    protected function guard()
    {
        return Auth::guard();
    }
}
