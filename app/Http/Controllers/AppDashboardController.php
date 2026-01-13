<?php

namespace App\Http\Controllers;

use App\Models\ServiceMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Response;
use App\Models\AppDashboard;


class AppDashboardController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->user = $this->guard()->user();
    }

    public function getServiceSummary()
    {
        try {
            $summary = new AppDashboard();
            return response()->json([
                'status' => true,
                'TotalServiceGiven' => $summary->totalServiceGiven(Auth::user()->staffid),
                'CurrentMonthServiceGiven'  =>  $summary->currentMonthServiceGiven(Auth::user()->staffid),
                'CurrentRating'  => $summary->currentRating(Auth::user()->staffid),
                'MonthlyGivenServiceGraph' => $summary->monthlyGivenServiceGraph(Auth::user()->staffid)
            ], 200)->header('Content-Type', 'application/json');
        } catch (NotFoundHttpException $e) {
            return response()->json(['status' => false, 'message' => 'Nothing found', 'error' => $e], Response::HTTP_NOT_FOUND)
                ->header('Content-Type', 'application/json');
        }
    }

    protected function guard()
    {
        return Auth::guard('api');
    }
}
