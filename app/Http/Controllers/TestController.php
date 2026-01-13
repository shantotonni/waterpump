<?php

namespace App\Http\Controllers;

use App\Models\test;
use App\Models\Test as ModelsTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TestController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->user = $this->guard()->user();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $test = $this->user->tests()->get(['title', 'body', 'completed', 'created_by']);
        return response()->json($test->toArray());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'title' => 'required',
            'body' => 'required|string',
            'completed' => 'required|boolean',

        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 400);
        }

        $test = new Test();
        $test->title = $request->title;
        $test->body = $request->body;
        $test->completed = $request->completed;

        if($this->user->tests()->save($test)){
            return response()->json([
                'status' => true,
                'testdata' => $test,
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\test  $test
     * @return \Illuminate\Http\Response
     */
    public function show(test $test)
    {
        return $test;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\test  $test
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, test $test)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\test  $test
     * @return \Illuminate\Http\Response
     */
    public function destroy(test $test)
    {
        //
    }

    protected function guard(){
        return Auth::guard();
    }
}
