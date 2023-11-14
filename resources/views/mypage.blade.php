@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('common.Mypage') }}</h1>
    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif
    <form method="POST" action="{{ route('mypage.update') }}">
        @csrf
        <div class="form-group">
            <label for="name">{{ __('employee.Name') }}</label>
            <label>{{ $user->name }}</label>
        </div>
        <div class="form-group">
            <label for="email">{{ __('employee.Email') }}</label>
            <label>{{ $user->email }}</label>
        </div>
        <div class="form-group">
            <label for="password">{{ __('employee.Password (leave blank to keep unchanged)') }}</label>
            <input type="password" id="password" name="password" class="form-control">
        </div>
        <div class="form-group">
            <label for="password_confirmation">{{ __('employee.Password Confirmation') }}</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">{{ __('common.Update') }}</button>
    </form>
</div>
@endsection