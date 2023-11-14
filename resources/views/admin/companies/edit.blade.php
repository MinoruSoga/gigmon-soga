@extends('layouts.app')

@section('content')
<div class="container">
    <h2>{{ __('company.Edit Company') }}</h2>

    @php
        $isRoleTwo = auth()->user()->role === 2;
    @endphp

    <form method="POST" action="{{ route('admin.companies.update', $company) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">{{ __('company.Name') }}</label>
            <input id="name" class="form-control" type="text" name="name" value="{{ $company->name }}" required {{$isRoleTwo ? 'disabled' : ''}}>
        </div>

        <div class="form-group">
            <label for="postal_code">{{ __('common.Company Postal Code') }}</label>
            <input id="postal_code" class="form-control" type="text" name="postal_code" value="{{ $company->postal_code }}" required {{$isRoleTwo ? 'disabled' : ''}}>
        </div>

        <div class="form-group">
            <label for="prefecture">{{ __('common.Company Prefecture') }}</label>
            <input id="prefecture" class="form-control" type="text" name="prefecture" value="{{ $company->prefecture }}" required {{$isRoleTwo ? 'disabled' : ''}}>
        </div>

        <div class="form-group">
            <label for="city">{{ __('common.Company City') }}</label>
            <input id="city" class="form-control" type="text" name="city" value="{{ $company->city }}" required {{$isRoleTwo ? 'disabled' : ''}}>
        </div>

        <div class="form-group">
            <label for="address">{{ __('common.Company Address') }}</label>
            <input id="address" class="form-control" type="text" name="address" value="{{ $company->address }}" required {{$isRoleTwo ? 'disabled' : ''}}>
        </div>

        <div class="form-group">
            <label for="building">{{ __('common.Company Building') }}</label>
            <input id="building" class="form-control" type="text" name="building" value="{{ $company->building }}" {{$isRoleTwo ? 'disabled' : ''}}>
        </div>

        <div class="form-group">
            <label for="phone_number">{{ __('common.Company Phone Number') }}</label>
            <input id="phone_number" class="form-control" type="tel" name="phone" value="{{ $company->phone_number }}" required {{$isRoleTwo ? 'disabled' : ''}}>
        </div>

        <div class="form-group">
            <label for="plan">{{ __('company.Plan') }}</label>
            <select id="plan" class="form-control" name="plan_id" required {{$isRoleTwo ? 'disabled' : ''}}>
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}" {{ $company->plan_id == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="parent_company_id">{{ __('company.Parent Company') }}</label>
            <select class="form-control" id="parent_company_id" name="parent_company_id" {{$isRoleTwo ? 'disabled' : ''}}>
                <option value=""></option>
                @foreach($companies as $companyItem)
                    <option value="{{ $companyItem->id }}" {{ old('parent_company_id', $company->parent_company_id) == $companyItem->id ? 'selected' : '' }}>
                        {{ $companyItem->id }} - {{ $companyItem->name }}
                    </option>
                @endforeach
            </select>
        </div>

        @if(!$isRoleTwo)
            <div class="form-group">
                <label for="docsbot_team_id">{{ __('company.DocsBot Team ID') }}</label>
                <input id="docsbot_team_id" class="form-control" type="text" name="docsbot_team_id" value="{{ $company->docsbot_team_id }}" >
            </div>

            <div class="form-group">
                <label for="docsbot_bot_id">{{ __('company.DocsBot Bot ID') }}</label>
                <input id="docsbot_bot_id" class="form-control" type="text" name="docsbot_bot_id" value="{{ $company->docsbot_bot_id }}" >
            </div>

            <div class="form-group">
                <label for="docsbot_api_key">{{ __('company.DocsBot API Key') }}</label>
                <input id="docsbot_api_key" class="form-control" type="text" name="docsbot_api_key" value="{{ $company->docsbot_api_key }}" >
            </div>
        @endif
        <div class="form-group">
            <label for="agency_code">{{ __('login.Agency Code') }}</label>
            <input id="agency_code" class="form-control" type="text" name="agency_code" value="{{ $company->agency_code }}" {{$isRoleTwo ? 'disabled' : ''}}>
        </div>

        <div class="form-group">
            <label for="staff_code">{{ __('login.Staff Code') }}</label>
            <input id="staff_code" class="form-control" type="text" name="staff_code" value="{{ $company->staff_code }}" {{$isRoleTwo ? 'disabled' : ''}}>
        </div>
        @if(!$isRoleTwo)
            <button type="submit" class="btn btn-primary">{{ __('common.Update') }}</button>
        @endif        
    </form>
</div>
@endsection