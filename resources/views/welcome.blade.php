@extends('layouts.app')

@section('content')
<div class="container h-100 w-75">
    <div class="row h-100 justify-content-center align-items-center">
        <div class="row h-50 w-100 justify-content-center align-items-center border border-primary">
            <div class="col-sm">

                <div class="mx-auto d-flex justify-content-center">
                    <img class="img-responsive rounded-circle" src="{{ asset('img/aci-water-pump.jpg') }}"
                        alt="Water Pump">
                </div>

          </div>
            <div class="col-sm">

                <div class="login-box mx-auto">
                    <div class="card h-100 card-outline card-primary">
                        <div class="card-header text-center">
                            {{ __('Sign in') }}
                        </div>
                        <div class="card-body">
                            {{-- <p class="login-box-msg">Sign in to start your session</p> --}}

                            <form action="../../index3.html" method="post">
                                <div class="input-group mb-3">
                                    <input type="email" class="form-control" placeholder="Email">
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <span class="fas fa-envelope"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="input-group mb-3">
                                    <input type="password" class="form-control" placeholder="Password">
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <span class="fas fa-lock"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-8">
                                        <div class="icheck-primary">
                                            <input type="checkbox" id="remember">
                                            <label for="remember">
                                                Remember Me
                                            </label>
                                        </div>
                                    </div>
                                    <!-- /.col -->
                                    <div class="col-4">
                                        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                                    </div>
                                    <!-- /.col -->
                                </div>
                            </form>

                            <p class="mb-1">
                                <a href="forgot-password.html">Forgot password?</a>
                            </p>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>

          </div>
        </div>

    </div>
  </div>

@endsection
