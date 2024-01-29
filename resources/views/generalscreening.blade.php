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
                                <h2 class="heading-bold animated rollIn">2024 ANNUAL EXECUTIVE ELECTIONS</h2>
                            <span class="animated fadeIn">
                                <span class="icon map-icon"></span>
                                <span class="text-place text-white">Website Online</span>
                            </span>
                            </div>
                            <div class="area-bottom animated zoomInLeft">
                                <a href="#" class="bnt bnt-theme join-now">VIEW LIST</a>
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
                    <h1>GENERAL REGISTRATION LIST</h1>
                </div>
                <div>
                   <table class="table table-striped">
                        <thead>
                            <tr>
                                <td>SN</td>
                                <td>First Name</td>
                                <td>Last Name</td>
                                <td>Gender</td>
                                <td>Phone</td>
                                <td>Email</td>
                                <td>Location</td>
                                <td>Status</td>
                            </tr>
                            @php
                                $counter = 1;
                            @endphp

                            @foreach($users as $user)
                            <tr>
                                <td>{{ $counter++ }}</td>
                                <td>{{ strtoupper($user->lastname) }}</td>
                                <td>{{ strtoupper($user->firstname) }}</td>
                                <td>{{ strtoupper($user->gender) }}</td>
                                <td>{{ substr($user->phone,0, 3)."******".substr($user->phone,-3) }}</td>
                                <td>{{ strtolower($user->email) }}</td>
                                <td>{{ strtoupper($user->location) }}</td>
                                <td>
                                    @if($user->status === NULL)
                                    <strong class="text-primary">{{ 'AWAITING SCREENING' }}</strong>
                                    @elseif($user->status === 0)
                                    <strong class="text-danger">{{ 'DENIED' }}</strong>
                                    @else
                                        <strong class="text-success">{{ 'APPROVED' }}</strong>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </thead>
                   </table>
                 </div>
            </div>
        </div>

    </div>
@endsection