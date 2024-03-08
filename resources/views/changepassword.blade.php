@extends('layouts.admin')
@section('title', 'User Dashboard')
@section('contentTitle', 'Change Password')
@section('content')
    <form method="POST" action="{{ route('changePassword') }}">
        @csrf
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" />
    <br />
        <label class="form-label">Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control" />
        <br />
       
        <input type="hidden" name="userRequest" value="true" />
        <input type="submit" name="submit" value="Change Password" class="form-control btn-primary" />
    </form>

    @if(isset($data))
        <div class="col-xl-12">
            <div class="card mg-b-20">
                <div class="card-body">
                    <div class="table-responsive">
                    <h3>Result</h3>
                    @if(isset($data))
                        Status: {{ ($data->status) ? "Successful": "Failed" }}
                    @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
