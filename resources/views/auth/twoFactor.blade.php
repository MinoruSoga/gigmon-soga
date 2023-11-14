@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('login.Two Factor Verification') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('login.A fresh verification link has been sent to your email address.') }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('verify.store') }}">
                        {{ csrf_field() }}
                        <p class="text-muted">
                        {{ __('login.You have received an email which contains two factor login code.') }} 
                        <a href="{{ route('verify.resend') }}">{{ __('login.click here to request another') }}</a>.
                        </p>
                        @if(session()->has('message'))
                            <p class="alert alert-info">
                                {{ session()->get('message') }}
                            </p>
                        @endif
                        <div class="input-group mb-3">
                            <input name="two_factor_code" type="text" 
                                class="form-control {{ $errors->has('two_factor_code') ? ' is-invalid' : '' }}" 
                                required autofocus placeholder="{{ __('login.Two Factor Code') }}">
                            @if($errors->has('two_factor_code'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('two_factor_code') }}
                                </div>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <button type="submit" class="btn btn-primary px-4">
                                    {{ __('login.Verify') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
