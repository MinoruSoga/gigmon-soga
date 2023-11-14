@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('employee.Create Employee') }}</h1>

    <form action="{{ route('admin.employees.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">{{ __('employee.Name') }}</label>
            <input type="text" id="name" name="name" required class="form-control">
        </div>
        <div class="form-group">
            <label for="email">{{ __('employee.Email') }}</label>
            <input type="email" id="email" name="email" required class="form-control">
        </div>
        <div class="form-group">
            <label for="password">{{ __('employee.Password') }}</label>
            <input type="password" id="password" name="password" required class="form-control">
        </div>
        <div class="form-group">
            <label for="role">{{ __('employee.Role') }}</label>
            <select name="role" id="role" class="form-control">
                <option value="2">{{ __('common.Company Admin') }}</option>
                <option value="3" selected>{{ __('common.User') }}</option>
            </select>
        </div>
        <button type="button" class="btn btn-secondary" onclick="window.history.back();">{{ __('common.Cancel') }}</button>
        <button type="submit" class="btn btn-primary">{{ __('common.Create') }}</button>
    </form>
</div>
@endsection