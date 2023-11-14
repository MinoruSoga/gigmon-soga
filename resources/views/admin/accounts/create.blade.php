@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('account.Create Account') }}</h1>
    <form method="POST" action="{{ route('admin.accounts.store') }}">
        @csrf
        <div class="form-group">
            <label for="name">{{ __('account.Name') }}</label>
            <input type="text" id="name" name="name" class="form-control">
        </div>
        <div class="form-group">
            <label for="email">{{ __('account.Email') }}</label>
            <input type="email" id="email" name="email" class="form-control">
        </div>
        <div class="form-group">
            <label for="password">{{ __('account.Password') }}</label>
            <input type="password" id="password" name="password" class="form-control">
        </div>
        <div class="form-group">
            <label for="role">{{ __('account.Role') }}</label>
            <select name="role" id="role" class="form-control">
                <option value="1">{{ __('common.System Admin') }}</option>
                <option value="2">{{ __('common.Company Admin') }}</option>
                <option value="3" selected>{{ __('common.User') }}</option>
            </select>
        </div>
        <div class="form-group">
            <label for="company">{{ __('account.Company') }}</label>
            <select name="company_id" id="company" class="form-control">
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('common.Create') }}</button>
    </form>
</div>
@endsection
