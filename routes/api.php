<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




Route::group(['namespace'  => 'App\Http\Controllers'],
    function ($router) {
        Route::get('get-period-wise-engineer-csi', 'EngineerCSIController@engineerWiseCSI');
    }
);

Route::group(['middleware' => 'api', 'namespace'  => 'App\Http\Controllers', 'prefix' => 'auth'],
    function ($router) {
        Route::post('login', 'AuthController@login')->name('login');
        Route::post('dashboardlogin', 'AuthController@dashboardLogin');
        Route::post('register', 'AuthController@register');
        Route::post('logout', 'AuthController@logout')->name('logout');
        Route::get('profile', 'AuthController@profile');
        Route::post('refresh', 'AuthController@refresh');
        Route::post('lockuser', 'AuthController@lockUser');
    }
);

Route::group(['middleware' => 'api', 'namespace'  => 'App\Http\Controllers',],
    function ($router) {
        Route::post('forgotpassword', 'ForgotPasswordController@forgotPassword');
        Route::post('verifyotp', 'ForgotPasswordController@verifyOtp');
        Route::post('resetpassword', 'ForgotPasswordController@resetPassword');
    }
);

Route::group(['middleware' => 'api', 'namespace'  => 'App\Http\Controllers', 'prefix'=> 'service',],
    function ($router) {
        Route::get('servicedetails', 'ServiceController@getServiceDetails');
        Route::get('getallterritories', 'ServiceController@getAllTerritories');
        Route::get('territorywisetechnicians', 'ServiceController@getTerritoryWiseTechnicians');
        Route::get('alltechnicians', 'ServiceController@getAllTechnicians');
        Route::post('savelocaltechnician', 'ServiceController@saveLocalTechnician');
        Route::post('updatelocaltechnician/{id}', 'ServiceController@updateLocalTechnician');
        Route::delete('deletelocaltechnician/{id}', 'ServiceController@deleteLocalTechnician');
        Route::post('postservice', 'ServiceController@postService');
        Route::get('servicelist', 'ServiceController@servieList');
        Route::post('usedsparepartslist', 'ServiceController@usedSparePartsList');
        Route::post('searchprovidedservicelist', 'ServiceController@searchProvidedServiceList');
    }
);

Route::group(['middleware' => 'api', 'namespace'  => 'App\Http\Controllers', 'prefix'     => 'appdashboard'],
    function ($router) {
        Route::get('servicesummary', 'AppDashboardController@getServiceSummary');
    }
);

Route::group(['middleware' => 'api', 'namespace'  => 'App\Http\Controllers', 'prefix'     => 'admindashboard',],
    function ($router) {
        Route::get('users', 'AdminDashboardController@getAllUsers');
        Route::get('get-all-remarks', 'AdminDashboardController@getAllRemarks');
        Route::get('providedservicereport', 'ReportController@providedServiceReport');
        Route::get('engineerWiseCSI', 'ReportController@engineerWiseCSI');
        Route::get('providedmergedservicereport', 'ReportController@providedMergedServiceReport');
        Route::get('providedselfservicereport', 'ReportController@providedSelfServiceReport');
        Route::get('providedoutsourceservicereport', 'ReportController@providedOutsourceServiceReport');
        Route::get('providedserviceDetails', 'ReportController@providedServiceDetailReport');
        Route::get('exportServiceReport', 'ReportController@excelExport');
        Route::get('getImage/{id}/{type}', 'ReportController@getImage');
        Route::post('get-image-pdf', 'ReportController@getImagePDF');
        Route::get('exportSelfServiceReport', 'ReportController@excelSelfExport');
        Route::get('exportOutsourceServiceReport', 'ReportController@excelOutsourceExport');
        Route::get('exportServiceDetailReport', 'ReportController@excelExportDetailReport');
        Route::get('dashboardSummary', 'AdminDashboardController@getServiceSummary');
        Route::get('dailysparepartsreport', 'ReportController@dailySparePartsReport');
        Route::get('exportDailySparePartsReport', 'ReportController@excelExportDailySparePartsReport');
        Route::get('ttywisedailyservicechart', 'AdminDashboardController@getTTYWiseDailyServiceChartData');
        Route::get('ttywisemonthlyserviceratio', 'AdminDashboardController@getTTYWiseMonthlyServiceChartData');
        Route::get('splywiseservicecomparison', 'AdminDashboardController@getSplyWiseYearlyServiceChartData');
        Route::get('yearwisecompare', 'AdminDashboardController@yearwisecompare');
        Route::get('servicesummaryreport', 'ReportController@serviceSummaryReport');
        Route::post('updateSelfServiceTotalCost', 'ReportController@updateSelfServiceTotalCost');
        Route::post('updateOutsourceServiceTotalCost', 'ReportController@updateOutsourceServiceTotalCost');
    }
);

Route::group(
    [
        'namespace'  => 'App\Http\Controllers',
        'prefix'     => 'public',
    ],
    function ($router) {
        Route::get('dashboard', 'PublicController@dashboard');
    }
);

Route::group(
    [
        'middleware' => 'api',
        'namespace'  => 'App\Http\Controllers',
        'prefix'     => 'customercare',
    ],
    function ($router) {
        Route::post('addpoint', 'PointDetailsController@addPoint');
        Route::post('add-remarks', 'PointDetailsController@addRemarks');
    }
);

Route::group(
    [
        'middleware' => 'api',
        'namespace'  => 'App\Http\Controllers',
        'prefix'     => 'stock',
    ],
    function ($router) {
        Route::post('addstock', 'SparePartsStockController@addStock');
        Route::get('stockList', 'SparePartsStockController@stockList');
        Route::get('staffinfo', 'SparePartsStockController@getStaffIds');
        Route::get('exportStockReport', 'SparePartsStockController@excelExportStockReport');
    }
);
