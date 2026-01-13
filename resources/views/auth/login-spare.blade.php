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
                                <form action="{{ route('login') }}" method="post">
                                    @csrf
                                    <div class="input-group mb-3">
                                        <input type="text" name="staffid"
                                            class="form-control {{ $errors->has('staffid') ? 'is-invalid' : '' }}"
                                            value="{{ old('staffid') }}" placeholder="Staff ID" autofocus>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-envelope"></span>
                                            </div>
                                        </div>
                                        @error('staffid')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="input-group mb-3">
                                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                            placeholder="Password" required autocomplete="current-password">
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-lock"></span>
                                            </div>
                                        </div>
                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="icheck-primary">
                                                <input type="checkbox" id="remember"
                                                    {{ old('remember') ? 'checked' : '' }}>
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

                                    <p class="mb-1">
                                        @if (Route::has('password.request'))
                                            <a href="{{ route('password.request') }}">Forgot password?</a>
                                        @endif
                                    </p>
                                </form>
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
