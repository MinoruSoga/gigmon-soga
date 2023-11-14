@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('prohibited_word.Edit Prohibited Word') }}</h1>
    <form action="{{ route('admin.prohibited_words.update', $prohibited_word) }}" method="post">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label>{{ __('prohibited_word.Word') }}</label>
            <input type="text" name="word" value="{{ $prohibited_word->word }}" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">{{ __('common.Update') }}</button>
    </form>
</div>
@endsection