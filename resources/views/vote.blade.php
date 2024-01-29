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
                                <a href="#" class="bnt bnt-theme join-now">VOTE BELOW</a>
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
                    <h4>98/04 VOTING FORM</h4>
                </div>
                <div>
                    <form method="post" action="">

                    <div class="row">

                        @csrf

                        @if($errors->any())
                            <h4 style="color: red">{{$errors->first()}}</h4><br />
                        @endif
                        
                        <input type="hidden" name="code" value="{{ $user->code }}" />
                        <!-- chairman -->
                        <div class="row">
                            <ul>
                                <h1 class="blue">1. CHAIR-PERSON</h1>
                                <br />

                                <li>
                                    <div class="form-check bb bbl" style="font-size: 20px">
                                        <input class="form-check-input" type="radio" name="chairman" id="dotun" value="dotun">
                                        <label class="form-check-label" for="dotun">
                                            DOTUN OMOTOSHO
                                        </label>
                                    </div>
                                </li>

                                <li>
                                    <div class="form-check bb bbl" style="font-size: 20px">
                                        <input class="form-check-input" type="radio" name="chairman" id="teniola" value="teniola">
                                        <label class="form-check-label" for="teniola">
                                            TENIOLA MAKINDE
                                        </label>
                                    </div>
                                </li>
                                
                            </ul>
                        </div>

                         <!-- vice chairman -->
                         <div class="row bb">
                            <ul>
                                <h1 class="blue">2. VICE CHAIR-PERSON</h1>
                                <br />
                                
                                <li>
                                    <div class="form-check bb bbl" style="font-size: 20px">
                                        <input class="form-check-input" type="radio" name="vicechairman" id="bimbo" value="bimbo">
                                        <label class="form-check-label" for="bimbo">
                                            OLOWOMEYE ABIMBOLA 
                                        </label>
                                    </div>
                                </li>
                            </ul>
                        </div>

                         <!-- Secretary -->
                         <div class="row bb">
                            <ul>
                                <h1 class="blue">3. SECRETARY</h1>
                                <br />
                                
                                <li>
                                    <div class="form-check bb bbl" style="font-size: 20px">
                                        <input class="form-check-input" type="radio" name="secretary" id="bolatito" value="bolatito">
                                        <label class="form-check-label" for="bolatito">
                                            AKILO BOLATITO 
                                        </label>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <!-- Treasurer -->
                        <div class="row bb">
                            <ul>
                                <h1 class="blue">4. TREASURER</h1>
                                <br />
                                
                                <li>
                                    <div class="form-check bb bbl" style="font-size: 20px">
                                        <input class="form-check-input" type="radio" name="treasurer" id="kehinde" value="kehinde">
                                        <label class="form-check-label" for="kehinde">
                                            OLADIMEJI KEHINDE 
                                        </label>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <!-- PRO -->
                        <div class="row bb">
                            <ul>
                                <h1 class="blue">5. PUBLIC RELATIONS OFFICER (P.R.O)</h1>
                                <br />
                                
                                <li>
                                    <div class="form-check bb bbl" style="font-size: 20px">
                                        <input class="form-check-input" type="radio" name="pro" id="ayodeji" value="ayodeji">
                                        <label class="form-check-label" for="ayodeji">
                                            AYODEJI DARAMOLA 
                                        </label>
                                    </div>
                                </li>

                                <li>
                                    <div class="form-check bb bbl" style="font-size: 20px">
                                        <input class="form-check-input" type="radio" name="pro" id="seun" value="seun">
                                        <label class="form-check-label" for="seun">
                                            SEUN OYEBANJI 
                                        </label>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <!-- Welfare -->
                        <div class="row bb">
                            <ul>
                                <h1 class="blue">6. WELFARE</h1>
                                <br />
                                
                                <li>
                                    <div class="form-check bb bbl" style="font-size: 20px">
                                        <input class="form-check-input" type="radio" name="welfare" id="adejoke" value="adejoke">
                                        <label class="form-check-label" for="adejoke">
                                            ADEJOKE OLUMIDE 
                                        </label>
                                    </div>
                                </li>
                            </ul>
                        </div>


                        <!-- Legal -->
                        <div class="row bb">
                            <ul>
                                <h1 class="blue">7. LEGAL</h1>
                                <br />
                                
                                <li>
                                    <div class="form-check bb bbl" style="font-size: 20px">
                                        <input class="form-check-input" type="radio" name="legal" id="babatope" value="babatope">
                                        <label class="form-check-label" for="babatope">
                                            BABATOPE ADEBIYI 
                                        </label>
                                    </div>
                                </li>
                            </ul>
                        </div>


                        <div class="row bb">
                            <ul>
                               
                                
                                <li>
                                    <div class="form-check bb bbl" style="font-size: 20px">
                                        <input type="submit" class="btn btn-primary" name="submit" value="CAST MY VOTE: {{ $user->firstname.' '.$user->lastname }}">
                                    </div>
                                </li>
                            </ul>
                        </div>

                    </form>
                 </div>
            </div>
        </div>
        <!--end event calendar-->

    </div>
@endsection