<?php

namespace App\Http\Controllers;

use App\Models\ServiceMaster;
use App\Models\ServiceDetails;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ServiceController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->user = $this->guard()->user();
    }

    public function servieList(){
        try {
            return response()->json([
                'status' => true,
                'serviceMasterList' => $this->getServiceMasterList(Auth::user()->staffid),
            ], 200)->header('Content-Type', 'application/json');
        } catch (NotFoundHttpException $e) {
            return response()->json(['status' => false, 'message' => 'Nothing found', 'error' => $e], Response::HTTP_NOT_FOUND)
                ->header('Content-Type', 'application/json');
        }
    }

    public function usedSparePartsList(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'ServiceMasterId' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Invalid'], 400);
            }

            if($validator->validated()){
                return response()->json([
                    'status' => true,
                    'usedSparePartsList' => $this->getServiceDetailsList($request->ServiceMasterId)
                ], 200)->header('Content-Type', 'application/json');
            }
        } catch (NotFoundHttpException $e) {
            return response()->json(['status' => false, 'message' => 'Nothing found', 'error' => $e], Response::HTTP_NOT_FOUND)
                ->header('Content-Type', 'application/json');
        }
    }

    public function getServiceDetails()
    {
        try {
            return response()->json([
                'status' => true,
                'district' => $this->getDistrict(),
                'territory' => $this->getTerritory(),
                'problemDetails' => $this->problemDetails(),
                'models' => $this->getModels(),
                'spareParts' => $this->getProducts()
            ], 200)->header('Content-Type', 'application/json');
        } catch (NotFoundHttpException $e) {
            return response()->json(['status' => false, 'message' => 'Nothing found', 'error' => $e], Response::HTTP_NOT_FOUND)
                ->header('Content-Type', 'application/json');
        }
    }

    public function postService(Request $request)
    {

        // print_r($request->SpareDetails);die;

        $validator = Validator::make($request->all(), [
            'StaffID'           =>  'required|string',
            'CustomerName'      =>  'required|string',
            'Address'           =>  'required|string',
            'Mobile'            =>  'required|min:11',
            'DistrictCode'      =>  'required|string',
            'TerritoryCode'     =>  'required|string',
            'ModelCode'         =>  'required|string',
            'PurchaseDate'      =>  'required|date',
            'ServiceTime'       =>  'required|string',
            'ActionTaken'       =>  'required|string',
            'AttendDate'        =>  'required|date',
            'ServiceCharge'     =>  'required|numeric',
            'MRNo'              =>  'required|string',
            'SpareDetails'      =>  'required',
            'WarrantyCardNo'    =>  'required|string',
            'Status'            =>  'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid'], 400);
        }

        if ($validator->validated()) {
            $doInsertServicemaster = ServiceMaster::create(
                array_merge(
                    $validator->validated(),
                    ['EntryBy' => $request->StaffID],
                    ['EntryDate' => Carbon::now()]
                )
            );

            $lastInsertId = $doInsertServicemaster->ServiceMasterID;

            if (!empty($lastInsertId)) {
                foreach ($request->SpareDetails as $details) {
                    $serviceDetails['ServiceMasterID']    =   $lastInsertId;
                    $serviceDetails['SparePartsCode']     =   $details['SparePartsCode'];
                    $serviceDetails['QuantityUsed']       =   $details['QuantityUsed'];
                    ServiceDetails::create($serviceDetails);
                }
            }
            return response()->json(['message' => 'Submission Complete.'], 200)->header('Content-Type', 'application/json');
        }
        return false;
    }

    public function searchProvidedServiceList(Request $request){
        // print_r($request->searchquery);die;
        try {
            $validator = Validator::make($request->all(), [
                'searchquery' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Invalid'],400);
            }

            if($validator->validated()){
                return response()->json([
                    'status' => true,
                    'searchResult' => $this->getSearchResult($request->searchquery)
                ], 200)->header('Content-Type', 'application/json');
            }
        } catch (NotFoundHttpException $e) {
            return response()->json(['status' => false, 'message' => 'No result found', 'error' => $e], Response::HTTP_NOT_FOUND)
                ->header('Content-Type', 'application/json');
        }
    }

    public function getAllTerritories(){
        $territories = DB::table('Territory')->get();
        return \response()->json([
            'data'=>$territories
        ]);
    }

    public function getAllTechnicians(Request $request)
    {
        $length = $request->input('length', 10);
        $columnIndex = $request->input('column', 0);
        $dir = $request->input('dir', 'desc');
        $searchValue = $request->input('search', '');

        $columns = [
            0 => 'TechnicianID',
            1 => 'TechnicianName',
            2 => 'TechnicianMobileNo',
            3 => 'TechnicianAddress',
            4 => 'BankAccountNo',
            5 => 'RoutingNo',
            6 => 'BankName',
        ];

        $orderColumn = $columns[$columnIndex] ?? 'TechnicianID';

        $query = DB::table('LocalTechnician');

        if (!empty($searchValue)) {
            $query->where(function($q) use ($searchValue) {
                $q->where('TechnicianName', 'like', "%{$searchValue}%")
                ->orWhere('TechnicianMobileNo', 'like', "%{$searchValue}%")
                ->orWhere('TechnicianAddress', 'like', "%{$searchValue}%")
                ->orWhere('BankName', 'like', "%{$searchValue}%");
            });
        }

        $query->orderBy($orderColumn, $dir);

        $technicians = $query->paginate($length);

        $technicians->getCollection()->transform(function ($tech) {
            $territoryCodes = DB::table('TechnicianTerritory')
                ->where('TechnicianID', $tech->TechnicianID)
                ->pluck('Territory')
                ->toArray();

            if (in_array('%', $territoryCodes)) {
                $territoryNames = ['All'];
            } else {
                $territoryNames = DB::table('TechnicianTerritory')
                    ->join('Territory', 'Territory.TTYCode', '=', 'TechnicianTerritory.Territory')
                    ->where('TechnicianID', $tech->TechnicianID)
                    ->pluck('Territory.TTYName')
                    ->toArray();
            }

            return [
                'TechnicianID' => $tech->TechnicianID,
                'Name' => $tech->TechnicianName,
                'MobileNo' => $tech->TechnicianMobileNo,
                'Address' => $tech->TechnicianAddress,
                'BankAccountNo' => $tech->BankAccountNo,
                'RoutingNo' => $tech->RoutingNo,
                'BankName' => $tech->BankName,
                'Territories' => implode(', ', $territoryNames),
                'TerritoriesArray' => $territoryCodes,
            ];
        });

        return response()->json($technicians, 200);
    }

    public function getTerritoryWiseTechnicians(Request $request)
    {
        $user = auth()->user();
        
        $territoryCode = $request->input('territory_code');

        if (empty($territoryCode)) {
            return response()->json(['status' => false, 'message' => 'Territory code is required.'], 400);
        }

        $technicianIds = DB::table('TechnicianTerritory')
            ->where('Territory', $territoryCode)
            ->orWhere('Territory', '%')
            ->pluck('TechnicianID')
            ->unique()
            ->toArray();

        $technicians = DB::table('LocalTechnician')
            ->whereIn('TechnicianID', $technicianIds)
            ->get();

        return response()->json([
            'status' => true,
            'technicians' => $technicians
        ], 200);
    }

    public function saveLocalTechnician(Request $request)
    {
        DB::beginTransaction();
        try {
            $rules = [
                'name' => 'required|string',
                'mobile' => 'required|string',
                'address' => 'required|string',
                'bank_account' => 'required|string',
                'routing_no' => 'required|string',
                'bank_name' => 'required|string'
            ];
            if (!$request->boolean('is_national')) {
                $rules['territories'] = 'required|array|min:1';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            $techId = DB::table('LocalTechnician')->insertGetId([
                'TechnicianName' => $validated['name'],
                'TechnicianMobileNo' => $validated['mobile'],
                'TechnicianAddress' => $validated['address'],
                'BankAccountNo' => $validated['bank_account'],
                'RoutingNo' => $validated['routing_no'],
                'BankName' => $validated['bank_name'],
            ]);

            if ($request->boolean('is_national')) {
                DB::table('TechnicianTerritory')->insert([
                    'TechnicianID' => $techId,
                    'Territory' => '%'
                ]);
            } else {
                foreach ($validated['territories'] as $territory) {
                    DB::table('TechnicianTerritory')->insert([
                        'TechnicianID' => $techId,
                        'Territory' => is_array($territory) ? $territory['TTYCode'] : $territory,
                    ]);
                }
            }

            DB::commit();

            return response()->json(['status' => true, 'message' => 'Technician saved successfully.'], 200)
                ->header('Content-Type', 'application/json');
        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateLocalTechnician(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $rules = [
                'name' => 'required|string',
                'mobile' => 'required|string',
                'address' => 'required|string',
                'bank_account' => 'required|string',
                'routing_no' => 'required|string',
                'bank_name' => 'required|string'
            ];

            if (!$request->boolean('is_national')) {
                $rules['territories'] = 'required|array|min:1';
            }

            $validator = Validator::make($request->all(), $rules);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            DB::table('LocalTechnician')
                ->where('TechnicianID', $id)
                ->update([
                    'TechnicianName' => $validated['name'],
                    'TechnicianMobileNo' => $validated['mobile'],
                    'TechnicianAddress' => $validated['address'],
                    'BankAccountNo' => $validated['bank_account'],
                    'RoutingNo' => $validated['routing_no'],
                    'BankName' => $validated['bank_name'],
                ]);

            DB::table('TechnicianTerritory')->where('TechnicianID', $id)->delete();

            if ($request->boolean('is_national')) {
                DB::table('TechnicianTerritory')->insert([
                    'TechnicianID' => $id,
                    'Territory' => '%'
                ]);
            } else {
                foreach ($validated['territories'] as $territory) {
                    DB::table('TechnicianTerritory')->insert([
                        'TechnicianID' => $id,
                        'Territory' => is_array($territory) ? $territory['TTYCode'] : $territory,
                    ]);
                }
            }

            DB::commit();

            return response()->json(['status' => true, 'message' => 'Technician updated successfully.'], 200)
                ->header('Content-Type', 'application/json');
        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteLocalTechnician($id)
    {
        DB::beginTransaction();
        try {
            DB::table('TechnicianTerritory')->where('TechnicianID', $id)->delete();
            DB::table('LocalTechnician')->where('TechnicianID', $id)->delete();

            DB::commit();

            return response()->json(['status' => true, 'message' => 'Technician deleted successfully.'], 200)
                ->header('Content-Type', 'application/json');
        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDistrict()
    {
        return DB::table('ViewDistrict')->get();
    }

    public function getTerritory()
    {
        return DB::table('Territory')->get();
    }

    public function getProducts()
    {
        return DB::table('Product')->get();
    }

    public function problemDetails()
    {
        return DB::table('ProblemDetails')->get();
    }

    public function getModels()
    {
        return DB::table('Model')->get();
    }

    public function getServiceDetailsList($serviceMasterId){
        $serviceDetailsList = DB::table('ServiceDetails AS SD')
                        ->select('SD.ServiceMasterID','SD.SparePartsCode', 'P.ProductName AS SparePartsName')
                        ->join('Product AS P', 'P.ProductCode', '=', 'SD.SparePartsCode')
                        ->where('SD.ServiceMasterID', '=', $serviceMasterId)->get();

        return $serviceDetailsList;
    }

    public function getServiceMasterList($staffid){
        $serviceMaster = DB::table('ServiceMaster AS M')
                            ->select('M.ServiceMasterID','M.StaffID','M.CustomerName','M.Address','M.Mobile','D.DistrictName','T.TTYName AS TerritoryName','Model.BrandName AS ModelName',
                                         'M.PurchaseDate AS ProductPurchaseDate','M.ServiceTime','M.ActionTaken','M.AttendDate','M.ServiceCharge','M.MRNo','M.Status','M.WarrantyCardNo')
                            ->join('ViewDistrict AS D', 'D.DistrictCode', '=', 'M.DistrictCode')
                            ->join('Territory AS T', 'T.TTYCode', '=', 'M.TerritoryCode')
                            ->join('Model', 'Model.BrandCode', '=','M.ModelCode')
                            ->where('M.StaffID', '=', $staffid)
                            ->get();

        return $serviceMaster;
    }

    public function getSearchResult($searchQuery){
        $searchQuery = DB::table('ServiceMaster AS M')
                            ->select('M.ServiceMasterID','M.StaffID','M.CustomerName','M.Address','M.Mobile','D.DistrictName','T.TTYName AS TerritoryName','Model.BrandName AS ModelName',
                                        'M.PurchaseDate AS ProductPurchaseDate','M.ServiceTime','M.ActionTaken','M.AttendDate','M.ServiceCharge','M.MRNo','M.Status','M.WarrantyCardNo')
                            ->join('ViewDistrict AS D', 'D.DistrictCode', '=', 'M.DistrictCode')
                            ->join('Territory AS T', 'T.TTYCode', '=', 'M.TerritoryCode')
                            ->join('Model', 'Model.BrandCode', '=','M.ModelCode')
                            ->where('M.CustomerName', 'like', '%'. $searchQuery. '%')
                            ->orWhere('M.Mobile', 'like', '%'. $searchQuery. '%')
                            ->orWhere('T.TTYName', 'like', '%'. $searchQuery. '%')
                            ->orWhere('M.MRNo', 'like', '%'. $searchQuery. '%')
                            ->get();

        return $searchQuery;
    }

    protected function guard()
    {
        return Auth::guard();
    }
}
