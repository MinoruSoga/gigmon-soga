@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('prohibited_word.Create Prohibited Word') }}</h1>
    <form action="{{ route('admin.prohibited_words.store') }}" method="post">
        @csrf
        <div class="form-group">
            <label>{{ __('prohibited_word.Word') }}</label>
            <input type="text" name="word" required class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">{{ __('common.Create') }}</button>
    </form>
</div>
@endsection