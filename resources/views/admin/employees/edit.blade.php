@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('employee.Edit Employee') }}</h1>
    <form action="{{ route('admin.employees.update', $employee) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">{{ __('employee.Name') }}</label>
            <input type="text" id="name" name="name" value="{{ $employee->user->name }}" required class="form-control">
        </div>
        <div class="form-group">
            <label for="email">{{ __('employee.Email') }}</label>
            <input type="email" id="email" name="email" value="{{ $employee->user->email }}" required class="form-control">
        </div>
        <div class="form-group">
            <label for="password">{{ __('employee.Password (leave blank to keep unchanged)') }}</label>
            <input type="password" id="password" name="password" class="form-control">
        </div>
        <div class="form-group">
            <label for="role">{{ __('employee.Role') }}</label>
            <select name="role" id="role" class="form-control">
                <option value="2" {{ $employee->user->role == 2 ? 'selected' : '' }}>{{ __('common.Company Admin') }}</option>
                <option value="3" {{ $employee->user->role == 3 ? 'selected' : '' }}>{{ __('common.User') }}</option>
            </select>
        </div>

        <button type="button" class="btn btn-secondary" onclick="window.history.back();">{{ __('common.Cancel') }}</button>
        <button type="submit" class="btn btn-primary">{{ __('common.Update') }}</button>
    </form>
</div>
@endsection