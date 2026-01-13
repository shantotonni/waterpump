<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\UserNotDefinedException;
use Tymon\JWTAuth\JWTAuth;
use TymonJWTAuthExceptionsJWTException;
use SymfonyComponentHttpFoundationResponse;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'dashboardLogin']]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staffid' => 'required|string',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid'], 400);
        }

        $token_validity = 24 * 60 * 60;

        $this->guard()->factory()->setTTL($token_validity);

        if ($validator->validated()) {
            $credentials = [
                'staffid' => $request->staffid,
                'password' => $request->password,
                'usertype' => 'U',
                'active'  => 'Y'
            ];
            if (!$token = $this->guard()->attempt($credentials)) {

                return response()->json(['error' => 'Unauthorized'], 401);
            } else {
                $userid = $this->guard()->user()->id;
                $userDetails = $this->userinfo($userid);

                return $this->respondWithToken($token, $userDetails);
            }
        }
        return false;
    }

    public function dashboardLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staffid' => 'required|string',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $token_validity = 24 * 60;

        $this->guard()->factory()->setTTL($token_validity);

        if ($validator->validated()) {
            // print_r($checkUser);die;
            $userType = User::where('users.staffid', '=', $request->staffid)->first();
            // print_r($userType->usertype);die;
            if($userType->usertype !== 'U'){
                $credentials = [
                    'staffid' => $request->staffid,
                    'password' => $request->password,
                    'active'  => 'Y'
                ];
                if (!$token = $this->guard()->attempt($credentials)) {

                    return response()->json(['error' => 'Unauthorized'], 401);
                } else {
                    $userid = $this->guard()->user()->id;
                    $userDetails = $this->userinfo($userid);

                    return $this->respondWithToken($token, $userDetails);
                }
            }else{
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }
        return false;
    }

    public function register(Request $request)
    {
        // $input = $request->all();
        // print_r($input);die;
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'staffid' => 'required|string',
            'password' => 'required|string|min:6',
            'mobile' => 'required|Numeric|min:11',
            'territorycode' => 'required|string',
            'districtcode' => 'required|string',
            'address' => 'required|string',
            'usertype' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $userExists = User::where('staffid', '=', $request->staffid)->first();

        if($userExists === null){
            $user = User::create(
                array_merge(
                    $validator->validated(),
                    // ['image' => $request->image],
                    ['dob' => $request->dob],
                    ['nid' => $request->nid],
                    ['bank_name' => $request->bank_name],
                    ['bank_acc_no' => $request->bank_acc_no],
                    ['routing_no' => $request->routing_no],
                    ['created_at' => Carbon::now()],
                    ['created_by' => $request->created_by],
                    ['joindate' => Carbon::now()],
                    ['password' => bcrypt($request->password)],
                )
            );
            return response()->json(['status' => true, 'message' => 'User created successfully', 'user' => $this->userinfo($user->id)], 200)->header('Content-Type', 'application/json');
        }else{
            User::where('staffid', $request->staffid)
                            ->update(['username' => $request->username, 'mobile' => $request->mobile, 'territorycode' => $request->territorycode,
                                        'districtcode' => $request->districtcode, 'address' => $request->address, 'usertype' => $request->usertype,
                                        'dob' => $request->dob, 'nid' => $request->nid, 'bank_name' => $request->bank_name,
                                        'bank_acc_no' => $request->bank_acc_no, 'routing_no' => $request->routing_no, 'updated_at' => Carbon::now(), 'updated_by' => $request->updated_by]);
            return response()->json(['status' => true, 'message' => 'User updated successfully', 'user'=>$this->userinfo($userExists->id)], 200)
                            ->header('Content-Type', 'application/json');
        }
        return false;
    }

    public function lockUser(Request $request){
        // print_r($request);
        $userLock = User::where('staffid', $request->staffid)
            ->update(['active' => $request->active, 'updated_at' => Carbon::now(), 'updated_by' => $request->updated_by]);
        if($userLock){
            return response()->json(['status' => true, 'message' => 'User locked successfully'], 200)
            ->header('Content-Type', 'application/json');
        }else{
            return response()->json(['status' => false, 'message' => 'Failed'], 200)
            ->header('Content-Type', 'application/json');
        }
        return false;
    }

    public function logout()
    {
        // $this->guard()->invalidate();
        $this->guard()->logout();
        return response()->json(['message' => 'User Logged out successfully']);
    }

    public function profile()
    {
        try{
            $userid = $this->guard()->user()->id;
            $userDetails = $this->userinfo($userid);
            if(!empty($userDetails)){
                return response()->json(['status'=>true,'userinfo'=>$userDetails],200)->header('Content-Type', 'application/json');
            }else{
                return response()->json(['status'=>false,'message'=>'No user found'], Response::HTTP_NOT_FOUND)->header('Content-Type', 'application/json');
            }
        }catch(NotFoundHttpException $e){
            return response()->json(['status'=>false, 'message'=>'No user found', 'error'=>$e], Response::HTTP_METHOD_NOT_ALLOWED)->header('Content-Type', 'application/json');
        }
    }

    public function refresh()
    {
        try{
            return $this->respondWithToken($this->guard()->refresh(true, true));
        }catch(\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e){
            return response()->json(['message' => 'Sorry, the user cannot be logged out','error'=>$e], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function userinfo($userid){
        $user = User::join('UserType', 'users.usertype', '=', 'UserType.UserType')
                ->join('Territory','Territory.TTYCode','=','users.territorycode')
                ->join('ViewDistrict','ViewDistrict.DistrictCode','=','users.districtcode')
                ->select('users.id AS UserID','users.username AS UserName','users.staffid AS StaffID','users.mobile AS MobileNo','users.joindate AS JoinDate','Territory.TTYName AS TerritoryName','ViewDistrict.DistrictName',
                'users.dob AS DateOfBirth','users.address AS Address','users.imagefilename AS ImageFileName','users.imagefilepath AS ImageFilePath','users.nid AS NID', 'users.usertype AS UserType', 'UserType.UserTypeName')
                ->where('users.id', '=', $userid)->first();

        return $user;
    }

    public function respondWithToken($token, $user='')
    {
        return response()->json([
            'status'   => true,
            'userinfo' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'token_validity' => auth('api')->factory()->getTTL()
        ]);
    }

    protected function guard()
    {
        return Auth::guard('api');
    }
}
