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
                    <h4>98/04 VOTING RESULT BOARD</h4>
                </div>

                <table class="table table-primary">
                    <!-- PRESIDENT -->
                    <tr>
                        <th colspan="2" style="text-align: center; font-weight: bold; font-size: 15px" class="text-primary">CHAIRMAN</th>
                    <tr>
                    <tr class="text-danger">
                        <th>Candidates</th>
                        <th>Votes</th>
                    </tr>
                    @foreach($chairman as $cm)
                        <tr>
                            <td>{{ strtoupper($cm['chairman']) }}</td>
                            <td>{{ $cm['total'] }}<td>
                        </tr>
                    @endforeach

                    <!-- VICE -->
                    <tr>
                        <th colspan="2" style="text-align: center; font-weight: bold; font-size: 15px" class="text-primary">VICE CHAIRMAN</th>
                    <tr>
                    @foreach($vicechairman as $cm1)
                        <tr>
                            <td>{{ strtoupper($cm1['vicechairman']) }}</td>
                            <td>{{ $cm1['total'] }}<td>
                        </tr>
                    @endforeach

                     <!-- SECRETARY -->
                     <tr>
                        <th colspan="2" style="text-align: center; font-weight: bold; font-size: 15px" class="text-primary">SECRETARY</th>
                     <tr>
                    @foreach($secretary as $cm2)
                        <tr>
                            <td>{{ strtoupper($cm2['secretary']) }}</td>
                            <td>{{ $cm2['total'] }}<td>
                        </tr>
                    @endforeach

                    <!-- 4. ASS. SECRETARY -->
                     <tr>
                        <th colspan="2" style="text-align: center; font-weight: bold; font-size: 15px" class="text-primary">ASS. SEC.</th>
                     <tr>
                    @foreach($ass_secretary as $cm3)
                        <tr>
                            <td>{{ strtoupper($cm3['ass_secretary']) }}</td>
                            <td>{{ $cm3['total'] }}<td>
                        </tr>
                    @endforeach

                    <!-- 5. TREASURER -->
                    <tr>
                        <th colspan="2" style="text-align: center; font-weight: bold; font-size: 15px" class="text-primary">TREASURER</th>
                     <tr>
                    @foreach($treasurer as $cm4)
                        <tr>
                            <td>{{ strtoupper($cm4['treasurer']) }}</td>
                            <td>{{ $cm4['total'] }}<td>
                        </tr>
                    @endforeach

                     <!-- 6. FIN SEC -->
                     <tr>
                        <th colspan="2" style="text-align: center; font-weight: bold; font-size: 15px" class="text-primary">FINANCIAL SEC.</th>
                     <tr>
                    @foreach($finsec as $cm5)
                        <tr>
                            <td>{{ strtoupper($cm5['finsec']) }}</td>
                            <td>{{ $cm5['total'] }}<td>
                        </tr>
                    @endforeach

                    <!-- 7. P.R.O -->
                    <tr>
                        <th colspan="2" style="text-align: center; font-weight: bold; font-size: 15px" class="text-primary">P.R.O</th>
                     <tr>
                    @foreach($pro as $cm6)
                        <tr>
                            <td>{{ strtoupper($cm6['pro']) }}</td>
                            <td>{{ $cm6['total'] }}<td>
                        </tr>
                    @endforeach

                     <!-- 8. LEGAL-->
                     <tr>
                        <th colspan="2" style="text-align: center; font-weight: bold; font-size: 15px" class="text-primary">LEGAL</th>
                     <tr>
                    @foreach($legal as $cm7)
                        <tr>
                            <td>{{ strtoupper($cm7['legal']) }}</td>
                            <td>{{ $cm7['total'] }}<td>
                        </tr>
                    @endforeach

                    <!-- 9. WELFARE-->
                    <tr>
                        <th colspan="2" style="text-align: center; font-weight: bold; font-size: 15px" class="text-primary">WELFARE</th>
                     <tr>
                    @foreach($welfare as $cm8)
                        <tr>
                            <td>{{ strtoupper($cm8['welfare']) }}</td>
                            <td>{{ $cm8['total'] }}<td>
                        </tr>
                    @endforeach
                <table>
        </div>  

    </div>
@endsection