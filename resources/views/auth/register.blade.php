@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('login.Register') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <h3>{{ __('common.Company Information') }}</h3>

                        <div class="row mb-3">
                            <label for="company_name" class="col-md-4 col-form-label text-md-end">{{ __('common.Company Name') }}</label>

                            <div class="col-md-6">
                                <input id="company_name" type="text" class="form-control @error('company_name') is-invalid @enderror" name="company_name" value="{{ old('company_name') }}" required autocomplete="company_name" autofocus>

                                @error('company_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="company_postal_code" class="col-md-4 col-form-label text-md-end">{{ __('common.Company Postal Code') }}</label>

                            <div class="col-md-6">
                                <input id="company_postal_code" type="text" class="form-control @error('company_postal_code') is-invalid @enderror" name="company_postal_code" value="{{ old('company_postal_code') }}" required autocomplete="company_postal_code" autofocus>

                                @error('company_postal_code')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="company_prefecture" class="col-md-4 col-form-label text-md-end">{{ __('common.Company Prefecture') }}</label>

                            <div class="col-md-6">
                                <input id="company_prefecture" type="text" class="form-control @error('company_prefecture') is-invalid @enderror" name="company_prefecture" value="{{ old('company_prefecture') }}" required autocomplete="company_prefecture" autofocus>

                                @error('company_prefecture')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="company_city" class="col-md-4 col-form-label text-md-end">{{ __('common.Company City') }}</label>

                            <div class="col-md-6">
                                <input id="company_city" type="text" class="form-control @error('company_city') is-invalid @enderror" name="company_city" value="{{ old('company_city') }}" required autocomplete="company_city" autofocus>

                                @error('company_city')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="company_address" class="col-md-4 col-form-label text-md-end">{{ __('common.Company Address') }}</label>

                            <div class="col-md-6">
                                <input id="company_address" type="text" class="form-control @error('company_address') is-invalid @enderror" name="company_address" value="{{ old('company_address') }}" required autocomplete="company_address" autofocus>

                                @error('company_address')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="company_building" class="col-md-4 col-form-label text-md-end">{{ __('common.Company Building') }}</label>

                            <div class="col-md-6">
                                <input id="company_building" type="text" class="form-control @error('company_building') is-invalid @enderror" name="company_building" value="{{ old('company_building') }}" autocomplete="company_building" autofocus>

                                @error('company_building')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="company_phone_number" class="col-md-4 col-form-label text-md-end">{{ __('common.Company Phone Number') }}</label>

                            <div class="col-md-6">
                                <input id="nacompany_phone_numberme" type="text" class="form-control @error('company_phone_number') is-invalid @enderror" name="company_phone_number" value="{{ old('company_phone_number') }}" required autocomplete="company_phone_number" autofocus>

                                @error('company_phone_number')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <h3>{{ __('common.Administrator Information') }}</h3>

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('login.Name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('login.Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('login.Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('login.Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <h3>{{ __('common.Agent Information') }}</h3>

                        <p><small>{{ __('common.If you are coming through an agency, please enter.') }}</small></p>

                        <div class="row mb-3">
                            <label for="agency_code" class="col-md-4 col-form-label text-md-end">{{ __('login.Agency Code') }}</label>

                            <div class="col-md-6">
                                <input id="agency_code" type="text" class="form-control @error('agency_code') is-invalid @enderror" name="agency_code" value="{{ old('agency_code') }}" autocomplete="agency_code" autofocus>

                                @error('agency_code')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="staff_code" class="col-md-4 col-form-label text-md-end">{{ __('login.Staff Code') }}</label>

                            <div class="col-md-6">
                                <input id="staff_code" type="text" class="form-control @error('staff_code') is-invalid @enderror" name="staff_code" value="{{ old('staff_code') }}" autocomplete="staff_code" autofocus>

                                @error('staff_code')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <h3>{{ __('common.Billing Information') }}</h3>

                        <div class="row mb-3">
                            <label for="accounting_email" class="col-md-4 col-form-label text-md-end">{{ __('common.Accounting Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="accounting_email" type="email" class="form-control @error('accounting_email') is-invalid @enderror" name="accounting_email" value="{{ old('accounting_email') }}" autocomplete="accounting_email">

                                @error('accounting_email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- 利用規約に同意するチェックボックス -->
                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="terms" value="1" id="terms" {{ old('terms') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="terms">{!! __('login.Agree to the terms of service') !!}</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('login.Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('js/address.js') }}"></script>
@endsection
