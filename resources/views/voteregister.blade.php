@extends('layouts.index')
@section('title', 'Christ School Ado Ekiti - 98/04 Set')
@section('contentTitle', 'Christ School Ado Ekiti - 98/04 Set')
@section('content')
   
    <!--Begin content wrapper-->
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
                                <h2 class="heading-bold animated rollIn">2023 ANNUAL EXECUTIVE ELECTIONS</h2>
                            <span class="animated fadeIn">
                                <span class="icon map-icon"></span>
                                <span class="text-place text-white">Website Online</span>
                            </span>
                            </div>
                            <div class="area-bottom animated zoomInLeft">
                                <a href="event-single.html" class="bnt bnt-theme join-now">Register Below</a>
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
                    <h1>VERIFICATION FORM</h1>
                </div>
                <div>
                    <form method="post" action="">
                        @csrf

                    @if($errors->any())
                        <h4 style="color: red">{{$errors->first()}}</h4><br />
                    @endif

                    @if(session()->has('smsg'))
                        <h4 style="color: green">{{  session()->get('smsg') }}</h4><br />
                    @endif
                        <div class="row">
                            <div class="col-md-12">
                                <label><h3>ENTER YOUR UNIQUE CODE</h3></label>
                            </div>  
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                    <input type="text" class="form-control" id="uniquecode" name="uniquecode" placeholder="XYZ-3294">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <input type="submit" class="btn btn-primary" id="verify" name="verify" value="VERIFY IDENTITY">
                            </div>
                        </div>

                    </form>
                 </div>
            </div>
        </div>
        <!--end event calendar-->

    </div>
@endsection