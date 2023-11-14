@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('account.Edit Account') }}</h1>
    <form method="POST" action="{{ route('admin.accounts.update', $user) }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">{{ __('account.Name') }}</label>
            <input type="text" id="name" name="name" value="{{ $user->name }}" required class="form-control">
        </div>
        <div class="form-group">
            <label for="email">{{ __('account.Email') }}</label>
            <input type="email" id="email" name="email" value="{{ $user->email }}" required class="form-control">
        </div>
        <div class="form-group">
            <label for="password">{{ __('account.Password (leave blank to keep unchanged)') }}</label>
            <input type="password" id="password" name="password" class="form-control">
        </div>
        <div class="form-group">
            <label for="role">{{ __('account.Role') }}</label>
            <select name="role" id="role" class="form-control">
                <option value="1" {{ $user->role == 1 ? 'selected' : '' }}>{{ __('common.System Admin') }}</option>
                <option value="2" {{ $user->role == 2 ? 'selected' : '' }}>{{ __('common.Company Admin') }}</option>
                <option value="3" {{ $user->role == 3 ? 'selected' : '' }}>{{ __('common.User') }}</option>
            </select>
        </div>
        <div class="form-group">`
            <label for="company">{{ __('account.Company') }}</label>
            <select name="company_id" id="company" class="form-control">
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}" {{ $user->employee->company_id == $company->id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('common.Update') }}</button>
    </form>
</div>
@endsection