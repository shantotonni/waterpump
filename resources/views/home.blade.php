{{-- @extends('adminlte::page') --}}
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                {{-- <li class="nav-item d-none d-sm-inline-block">
                    <a href="index3.html" class="nav-link">Home</a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="#" class="nav-link">Contact</a>
                </li> --}}
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <ul class="navbar-nav ml-auto">
                    <!-- Authentication Links -->
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                        @endif

                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest
                </ul>
                <!-- Navbar Search -->
                <li class="nav-item">
                    <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                        <i class="fas fa-search"></i>
                    </a>
                    <div class="navbar-search-block">
                        <form class="form-inline">
                            <div class="input-group input-group-sm">
                                <input class="form-control form-control-navbar" type="search" placeholder="Search"
                                    aria-label="Search">
                                <div class="input-group-append">
                                    <button class="btn btn-navbar" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </li>


                <!-- Notifications Dropdown Menu -->
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="index3.html" class="brand-link">
                <img src="{{ asset('img/aci-water-pump.jpg') }}" alt="WaterPump Logo"
                    class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light"><b>Admin Panel</b></span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <li class="nav-header">Main Navigation</li>
                        <li class="nav-item">
                            <router-link :to="{name : 'CustomerCareDashboard'}" class="nav-link active">
                                <i class="nav-icon fas fa-home"></i>
                                <p>Dashboard</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link :to="{name : 'Home'}" class="nav-link active">
                                <i class="nav-icon fas fa-home"></i>
                                <p>Home</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link :to="{name : 'Employee'}" class="nav-link">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Employee List</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link :to="{name : 'Spares'}" class="nav-link">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>Spare Parts</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link :to="{name : 'Report'}" class="nav-link">
                                <i class="nav-icon fas fa-file"></i>
                                <p>Report</p>
                            </router-link>
                        </li>
                        <li class="nav-header">Account Settings</li>
                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-cog"></i>
                                <p>
                                    Settings
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview" style="display: none;">
                                <li class="nav-item">
                                    <router-link :to="{name : 'ChangePassword'}" class="nav-link">
                                        <i class="nav-icon fas fa-lock"></i>
                                        <p>Change Password</p>
                                    </router-link>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <main-app></main-app>

        <footer class="main-footer">
            <strong>Copyright &copy; <?= Date('Y') ?>
         <a href="https://acimotors-bd.com/products/agricultural-machineries/aci-pump" title="ACI Water Pump">ACI Motors</a>.</strong>
          All rights reserved.
          <div class="float-right d-none d-sm-inline-block">
          </div>
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
          <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
      </div>

@endsection
