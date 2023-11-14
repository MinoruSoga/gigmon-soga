@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('common.Contact') }}</h1>
    @if (Session::has('success'))
        <div class="alert alert-success">
            {{ Session::get('success') }}
        </div>
    @endif
    @if (Session::has('error'))
        <div class="alert alert-danger">
            {{ Session::get('error') }}
        </div>
    @endif
    <form method="POST" action="{{ route('contact.submit') }}">
        @csrf
        <div class="form-group">
            <label for="name">{{ __('common.Name') }}</label>
            <input type="text" id="name" name="name" value="{{ Auth::user()->name }}" required class="form-control">
        </div>
        <div class="form-group">
            <label for="email">{{ __('common.Email') }}</label>
            <input type="email" id="email" name="email" value="{{ Auth::user()->email }}" required class="form-control">
        </div>
        <div class="form-group">
            <label for="message">{{ __('common.Message') }}</label>
            <textarea id="message" name="message" required class="form-control"></textarea>
            @if (Auth::user()->role === 3)
            <p class="help-block">※こちらのお問合せは御社の管理者の方に送信されます。</p>
            @endif
        </div>
        <button type="submit" class="btn btn-primary">{{ __('common.Submit') }}</button>
    </form>
</div>
@endsection
