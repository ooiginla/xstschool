@extends('layouts.index')
@section('title', 'Christ School Ado Ekiti - 98/04 Set')
@section('contentTitle', 'Christ School Ado Ekiti - Vote Now')
@section('content')
    <!--Begin content wrapper-->
    <style type="text/css">
        .bb{
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .bbl{
            margin-left: 40px;
        }
        .blue{
            color: blue;
        }
    </style>
    <div class="content-wrapper">

        <!--begin upcoming event-->
        <div class="program-upcoming-event">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <div class="area-img">
                            <img class="img-responsive animate zoomIn" src="{{ asset('images/bg77.jpg') }}" alt="">
                            <div id="time-event" class="animated fadeIn"></div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <div class="area-content">
                            <div class="area-top">
                                <div class="top-section animated lightSpeedIn">
                                    <h5 class="heading-light">UPCOMING EVENT</h5>
                                    <span class="dates text-white text-uppercase">FEBRUARY 3, 2024</span>
                                </div>
                                <h2 class="heading-bold animated rollIn">2024 ANNUAL EXECUTIVE ELECTIONS</h2>
                            <span class="animated fadeIn">
                                <span class="icon map-icon"></span>
                                <span class="text-place text-white">Website Online</span>
                            </span>
                            </div>
                            <div class="area-bottom animated zoomInLeft">
                                <a href="#" class="bnt bnt-theme join-now">RESULT BELOW</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end upcoming event-->

        <!--begin event calendar-->
        <div class="event-calendar">
            <div class="container">
                <div class="top-section text-center">
                    <h4>98/04 VOTING RESULT</h4>
                </div>
            <div>

            <div class="row">
                <ul>
                    <h1 class="blue bb">1. CHAIR-PERSON</h1>
                    @foreach($chairman as $cm)
                        <li class="bbl bb" style="font-size: 20px"> {{ strtoupper($cm['chairman'] ." - ". $cm['total']) }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="row">
                <ul>
                    <h1 class="blue bb">2. VICE CHAIR-PERSON</h1>
                    @foreach($vicechairman as $cm1)
                        <li class="bbl bb" style="font-size: 20px"> {{ strtoupper($cm1['vicechairman'] ." - ". $cm1['total']) }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="row">
                <ul>
                    <h1 class="blue bb">3. SECRETARY</h1>
                    @foreach($secretary as $cm2)
                        <li class="bbl bb" style="font-size: 20px"> {{ strtoupper($cm2['secretary'] ." - ". $cm2['total']) }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="row">
                <ul>
                    <h1 class="blue bb">4. ASS. SECRETARY</h1>
                    @foreach($ass_secretary as $cm3)
                        <li class="bbl bb" style="font-size: 20px"> {{ strtoupper($cm3['ass_secretary'] ." - ". $cm3['total']) }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="row">
                <ul>
                    <h1 class="blue bb">5. TREASURER</h1>
                    @foreach($treasurer as $cm4)
                        <li class="bbl bb" style="font-size: 20px"> {{ strtoupper($cm4['treasurer'] ." - ". $cm4['total']) }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="row">
                <ul>
                    <h1 class="blue bb">6. FIN SEC</h1>
                    @foreach($finsec as $cm5)
                        <li class="bbl bb" style="font-size: 20px"> {{ strtoupper($cm5['finsec'] ." - ". $cm5['total']) }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="row">
                <ul>
                    <h1 class="blue bb">7. P.R.O</h1>
                    @foreach($pro as $cm6)
                        <li class="bbl bb" style="font-size: 20px"> {{ strtoupper($cm6['pro'] ." - ". $cm6['total']) }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="row">
                <ul>
                    <h1 class="blue bb">8. LEGAL</h1>
                    @foreach($legal as $cm7)
                        <li class="bbl bb" style="font-size: 20px"> {{ strtoupper($cm7['legal'] ." - ". $cm7['total']) }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="row">
                <ul>
                    <h1 class="blue bb">8. WELFARE</h1>
                    @foreach($welfare as $cm8)
                        <li class="bbl bb" style="font-size: 20px"> {{ strtoupper($cm8['welfare'] ." - ". $cm8['total']) }}</li>
                    @endforeach
                </ul>
            </div>
        </div>  

    </div>
@endsection