@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('company.Create Company') }}</h1>
    <form method="POST" action="{{ route('admin.companies.store') }}">
        @csrf

        <div class="form-group">
            <label for="name">{{ __('company.Name') }}</label>
            <input id="name" class="form-control" type="text" name="name" required class="form-control">
        </div>

        <div class="form-group">
            <label for="postal_code">{{ __('common.Company Postal Code') }}</label>
            <input id="postal_code" class="form-control" type="text" name="postal_code" required class="form-control">
        </div>

        <div class="form-group">
            <label for="prefecture">{{ __('common.Company Prefecture') }}</label>
            <input id="prefecture" class="form-control" type="text" name="prefecture" required class="form-control">
        </div>

        <div class="form-group">
            <label for="city">{{ __('common.Company City') }}</label>
            <input id="city" class="form-control" type="text" name="city" required class="form-control">
        </div>

        <div class="form-group">
            <label for="address">{{ __('common.Company Address') }}</label>
            <input id="address" class="form-control" type="text" name="address" required class="form-control">
        </div>

        <div class="form-group">
            <label for="building">{{ __('common.Company Building') }}</label>
            <input id="building" class="form-control" type="text" name="building" class="form-control">
        </div>

        <div class="form-group">
            <label for="phone_number">{{ __('common.Company Phone Number') }}</label>
            <input id="phone_number" class="form-control" type="tel" name="phone_number" required class="form-control">
        </div>

        <div class="form-group">
            <label for="plan">{{ __('company.Plan') }}</label>
            <select id="plan" class="form-control" name="plan_id" required class="form-control">
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="docsbot_team_id">{{ __('company.DocsBot Team ID') }}</label>
            <input id="docsbot_team_id" class="form-control" type="text" name="docsbot_team_id" class="form-control">
        </div>

        <div class="form-group">
            <label for="docsbot_bot_id">{{ __('company.DocsBot Bot ID') }}</label>
            <input id="docsbot_bot_id" class="form-control" type="text" name="docsbot_bot_id" class="form-control">
        </div>

        <div class="form-group">
            <label for="docsbot_api_key">{{ __('company.DocsBot API Key') }}</label>
            <input id="docsbot_api_key" class="form-control" type="text" name="docsbot_api_key" class="form-control">
        </div>

        <div class="form-group">
            <label for="agency_code">{{ __('login.Agency Code') }}</label>
            <input id="agency_code" class="form-control" type="text" name="agency_code" class="form-control">
        </div>

        <div class="form-group">
            <label for="staff_code">{{ __('login.Staff Code') }}</label>
            <input id="staff_code" class="form-control" type="text" name="staff_code" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">{{ __('common.Create') }}</button>
    </form>
</div>
@endsection