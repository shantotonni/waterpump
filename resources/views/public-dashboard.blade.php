<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Water Pump Service</title>
    <link rel="icon" href="{{ asset('img/favicon.ico') }}" type="image/gif" sizes="16x16">
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/css/fontawesome.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/css/all.min.css"/>
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<style>
    #rating-row {
        padding: 10px 0;
        margin-bottom: 5px;
        background: #007bff14;
        border-radius: 10px;
    }

    .rating-panel {
        margin-top: 10px;
    }

    .fa-star {
        color: #d9a717;
    }

    #basic-bar {
        max-width: 100%;
        margin: 35px auto;
    }

    .info-box {
        padding: 1.5rem !important;
    }

    .info-box-icon {
        width: 120px !important;
    }

    .info-box .info-box-content {
        text-align: center;
    }

    .info-box .info-box-text {
        font-size: 20px;
        text-transform: uppercase;
    }

    .info-box .info-box-number {
        font-size: 45px;
        height: 90px;
    }

    .bg-gradient-warning {
        color: #ffffff !important;
    }

    .info-box-icon img {
        width: 80px;
        height: 80px;
    }

    #top-center {
        text-align: center;
        padding: 35px 0;
    }

    #top-center h2 {
        font-size: 32px;
        font-weight: bold;
        text-transform: uppercase;
    }

    #top-left img {
        width: 185px;
    }

    #top-right {
        text-align: right;
    }

    #top-right img {
        width: 150px;
    }

    @keyframes rotate {
        100% {
            transform: rotate(1turn);
        }
    }

    /*.info-box-first {*/
    /*    z-index: 0;*/
    /*    border-radius: 10px;*/
    /*    overflow: hidden;*/


    /*::before {*/
    /*    content: '';*/
    /*    position: absolute;*/
    /*    z-index: -2;*/
    /*    left: -50%;*/
    /*    top: -50%;*/
    /*    width: 200%;*/
    /*    height: 200%;*/
    /*    background-repeat: no-repeat;*/
    /*    background-size: 50% 50%, 50% 50%;*/
    /*    background-position: 0 0, 100% 0, 100% 100%, 0 100%;*/
    /*    background-image: linear-gradient(#0c515d, #146170), linear-gradient(#0ba9c2, #7de2f8), linear-gradient(#d53e33, #d53e33), linear-gradient(#377af5, #377af5);*/
    /*    animation: rotate 4s linear infinite;*/
    /*}*/

    /*::after {*/
    /*    content: '';*/
    /*    position: absolute;*/
    /*    z-index: -1;*/
    /*    left: 6px;*/
    /*    top: 6px;*/
    /*    width: calc(100% - 12px);*/
    /*    height: calc(100% - 12px);*/
    /*    background: #17a2b8;*/
    /*    border-radius: 5px;*/
    /*    animation: opacityChange 3s infinite alternate;*/
    /*}*/

    /*}*/

    /*.info-box-second {*/
    /*    z-index: 0;*/
    /*    border-radius: 10px;*/
    /*    overflow: hidden;*/

    /*::before {*/
    /*    content: '';*/
    /*    position: absolute;*/
    /*    z-index: -2;*/
    /*    left: -50%;*/
    /*    top: -50%;*/
    /*    width: 200%;*/
    /*    height: 200%;*/
    /*    background-repeat: no-repeat;*/
    /*    background-size: 50% 50%, 50% 50%;*/
    /*    background-position: 0 0, 100% 0, 100% 100%, 0 100%;*/
    /*    background-image: linear-gradient(#064413, #0b601e), linear-gradient(#1aaf3b, #74f38d), linear-gradient(#d53e33, #d53e33), linear-gradient(#377af5, #377af5);*/
    /*    animation: rotate 4s linear infinite;*/
    /*}*/

    /*::after {*/
    /*    content: '';*/
    /*    position: absolute;*/
    /*    z-index: -1;*/
    /*    left: 6px;*/
    /*    top: 6px;*/
    /*    width: calc(100% - 12px);*/
    /*    height: calc(100% - 12px);*/
    /*    background: #28a745;*/
    /*    border-radius: 5px;*/
    /*    animation: opacityChange 3s infinite alternate;*/
    /*}*/

    /*}*/

    /*.info-box-third {*/
    /*    z-index: 0;*/
    /*    border-radius: 10px;*/
    /*    overflow: hidden;*/

    /*::before {*/
    /*    content: '';*/
    /*    position: absolute;*/
    /*    z-index: -2;*/
    /*    left: -50%;*/
    /*    top: -50%;*/
    /*    width: 200%;*/
    /*    height: 200%;*/
    /*    background-repeat: no-repeat;*/
    /*    background-size: 50% 50%, 50% 50%;*/
    /*    background-position: 0 0, 100% 0, 100% 100%, 0 100%;*/
    /*    background-image: linear-gradient(#83680b, #9a7812), linear-gradient(#d9a717, #f5d883), linear-gradient(#d53e33, #d53e33), linear-gradient(#377af5, #377af5);*/
    /*    animation: rotate 4s linear infinite;*/
    /*}*/

    /*::after {*/
    /*    content: '';*/
    /*    position: absolute;*/
    /*    z-index: -1;*/
    /*    left: 6px;*/
    /*    top: 6px;*/
    /*    width: calc(100% - 12px);*/
    /*    height: calc(100% - 12px);*/
    /*    background: #ffc107;*/
    /*    border-radius: 5px;*/
    /*    animation: opacityChange 3s infinite alternate;*/
    /*}*/

    /*}*/

    /*.info-box-fourth {*/
    /*    z-index: 0;*/
    /*    border-radius: 10px;*/
    /*    overflow: hidden;*/

    /*::before {*/
    /*    content: '';*/
    /*    position: absolute;*/
    /*    z-index: -2;*/
    /*    left: -50%;*/
    /*    top: -50%;*/
    /*    width: 200%;*/
    /*    height: 200%;*/
    /*    background-repeat: no-repeat;*/
    /*    background-size: 50% 50%, 50% 50%;*/
    /*    background-position: 0 0, 100% 0, 100% 100%, 0 100%;*/
    /*    background-image: linear-gradient(#0a233f, #0d3b6e), linear-gradient(#196bc0, #7fb5ef), linear-gradient(#d53e33, #d53e33), linear-gradient(#377af5, #377af5);*/
    /*    animation: rotate 4s linear infinite;*/
    /*}*/

    /*::after {*/
    /*    content: '';*/
    /*    position: absolute;*/
    /*    z-index: -1;*/
    /*    left: 6px;*/
    /*    top: 6px;*/
    /*    width: calc(100% - 12px);*/
    /*    height: calc(100% - 12px);*/
    /*    background: #007bff;*/
    /*    border-radius: 5px;*/
    /*    animation: opacityChange 3s infinite alternate;*/
    /*}*/

    /*}*/
</style>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper" style="padding: 10px 35px;">
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4" id="top-left">
                    <img src="{{asset('img/waterpump.png')}}" alt="waterpump">
                </div>
                <div class="col-md-4" id="top-center">
                    <h2>Water Pump Dashboard</h2>
                </div>
                <div class="col-md-4" id="top-right">
                    <img src="{{asset('img/dashboard/waterpump.png')}}" alt="waterpump">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 col-sm-6 col-12 total-service">
                    <div class="info-box info-box-first bg-gradient-info" id="total-service">
                        {{--                        <span class="info-box-icon">--}}
                        {{--                            <img src="{{asset('img/dashboard/customer-service.png')}}" alt="service">--}}
                        {{--                        </span>--}}
                        <div class="info-box-content">
                            <span class="info-box-number" id="total-service-count">Loading...</span>
                            <span class="info-box-text">Yearly Service</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12 current-month">
                    <div class="info-box info-box-second bg-gradient-success" id="current-month">
                        {{--                        <span class="info-box-icon">--}}
                        {{--                            <img src="{{asset('img/dashboard/calendar.png')}}" alt="month">--}}
                        {{--                        </span>--}}
                        <div class="info-box-content">
                            <span class="info-box-number count" id="this-month-count">Loading...</span>
                            <span class="info-box-text">Current Month Service</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12 current-day">
                    <div class="info-box info-box-third bg-gradient-warning" id="current-day">
                        {{--                        <span class="info-box-icon">--}}
                        {{--                            <img src="{{asset('img/dashboard/day.png')}}" alt="day">--}}
                        {{--                        </span>--}}
                        <div class="info-box-content">
                            <span class="info-box-number count" id="current-day-count">Loading...</span>
                            <span class="info-box-text">Today Service</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12 average-csi">
                    <div class="info-box info-box-fourth bg-gradient-primary" id="average-csi">
                        {{--                        <span class="info-box-icon">--}}
                        {{--                            <img src="{{asset('img/dashboard/rate.png')}}" alt="rate">--}}
                        {{--                        </span>--}}
                        <div class="info-box-content">
                            <span class="info-box-number count" id="average-csi-count">Loading...</span>
                            <span class="info-box-text">CSI </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" style="margin-top: 50px;">
                <div class="col-md-8 col-sm-12 col-12 mb-3">
                    <span style="font-size: 20px;text-transform: uppercase;"><b>Compare Services Between Years</b></span>
                    <hr>
                    <div id="basic-bar">
                    </div>
                </div>
                <div class="col-md-4 col-sm-12 col-12 mb-3">
                    <div id="rating-bar">
                        <span style="font-size: 20px;text-transform: uppercase;"><b>Star Performers</b></span>
                        <hr>
                        <div class="rating-panel">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    ajaxCall()

    function ajaxCall() {
        $.ajax({
            url: "<?= url('api/public/dashboard')?>",
            dataType: "JSON",
            type: "GET",
            success: function (response) {
                // header data
                $(".total-service .info-box-number").html("<span>" + response.TotalServiceGiven + "</span>");
                $(".current-month .info-box-number").html("<span>" + response.CurrentMonthServiceGiven + "</span>");
                $(".current-day .info-box-number").html("<span>" + response.CurrentDayServiceGiven + "</span>");
                $(".average-csi .info-box-number").html("<span>" + response.AvgCSIRating + "%</span> ");
                // chart data
                var options = {
                    chart: {
                        type: 'bar',
                        height: 500,
                        toolbar: {
                            show: false
                        }
                    },
                    series: response.yearCompare.data,
                    xaxis: {
                        categories: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
                    }
                }
                var chart = new ApexCharts(document.querySelector("#basic-bar"), options);
                chart.render();
                // DATA ANIMATION
                animateFirst();
                startMechanics(response.starMechanics);
            }
        });
    }

    let firstTime = 50
    let secondTime = 5000

    function animateFirst() {
        $("#total-service-count span").hide()
        $("#total-service-count span").show()
        // $("#total-service-count span").slideDown(1000)
        countUp("total-service-count span")
        setTimeout(()=>{
            animateSecond()
        },5000)
    }

    function animateSecond() {
        $("#this-month-count span").hide()
        $("#this-month-count span").show()
        countUp("this-month-count span")
        setTimeout(()=>{
            animateThird()
        },5000)
    }

    function animateThird() {
        $("#current-day-count span").hide()
        $("#current-day-count span").show()
        countUp("current-day-count span")
        setTimeout(()=>{
            animateFourth()
        },3000)
    }

    function animateFourth() {
        $("#average-csi-count span").hide()
        $("#average-csi-count span").slideDown(1000)
        // countUp("average-csi-count span")
        setTimeout(()=>{
            ajaxCall()
        },5000)
    }

    function countUp(id) {
        $("#" + id).each(function () {
            $(this)
                .prop("Counter", 0)
                .animate(
                    {
                        Counter: $(this).text(),
                    },
                    {
                        duration: 2000,
                        easing: "swing",
                        step: function (now) {
                            now = Number(Math.ceil(now)).toLocaleString('en');
                            $(this).text(now);
                        },
                    }
                );
        });
    }

    function startMechanics(mechanics) {
        let output = '';
        mechanics.forEach((mechanic) => {
            output += `<div class="row" id="rating-row">
                            <div class="col-3" style="text-align: center;">
                                <img src="{{asset('img/')}}/staff/${mechanic.avatar}" style="border-radius: 50%;" alt="avatar" width="80" height="80">
                            </div>
                            <div class="col-9">
                                <h4><b>${mechanic.Name}</b></h4>
                                <p style="font-size: 20px;font-weight: 500;">Rating: `;
            for (let i = 1; i <= mechanic.Score; i++) {
                output += `<span class="fa fa-star checked"></span>`
            }
            output += `</p>
                            </div>
                        </div>`
        })
        $('.rating-panel').html(output)
    }
</script>
</html>