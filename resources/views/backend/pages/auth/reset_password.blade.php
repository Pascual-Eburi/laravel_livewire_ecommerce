@extends('backend.layout.auth-layout')
@section('pageTitle', $pageTitle ?? 'Reset Password')
@section('image')
    <img src="/backend/vendors/images/forgot-password.png" alt="" />
@endsection
@section('content')
    <div class="login-box bg-white box-shadow border-radius-10">
        <div class="login-title">
            <h2 class="text-center text-primary">Reset Password</h2>
        </div>
        <h6 class="mb-20">Enter your new password, confirm and submit</h6>
        <form method="POST" action="{{ route('auth.resetPasswordHandler', ['token' => request()->token]) }}">
            @csrf
            @if(Session::get('fail'))
                <x-backend.notification.alert type='warning' message="{{ Session::get('fail') }}" />
            @endif

            @if(Session::get('success'))
                <x-backend.notification.alert type='success' message="{{ Session::get('success') }}" />
            @endif
            <div class="input-group custom">
                <input type="text" class="form-control form-control-lg" placeholder="New Password" name="new_password" value="{{ old('new_password') }}">
                <div class="input-group-append custom">
                    <span class="input-group-text"><i class="dw dw-padlock1"></i></span>
                </div>
            </div>
            @error('new_password')
            <div class="d-block text-danger" style="margin-top: -25px; margin-bottom: 15px;">
                {{ $message }}
            </div>
            @enderror
            <div class="input-group custom">
                <input type="text" class="form-control form-control-lg" placeholder="Confirm New Password" name="password_confirm" value="{{ old('password_confirm') }}">
                <div class="input-group-append custom">
                    <span class="input-group-text"><i class="dw dw-padlock1"></i></span>
                </div>
            </div>
            @error('password_confirm')
            <div class="d-block text-danger" style="margin-top: -25px; margin-bottom: 15px;">
                {{ $message }}
            </div>
            @enderror
            <div class="row align-items-center">
                <div class="col-5">
                    <div class="input-group mb-0">
                            <input class="btn btn-primary btn-lg btn-block" type="submit" value="Submit">
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
