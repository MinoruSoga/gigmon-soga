@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('prompt.Create Prompt') }}</h1>
    <form method="POST" action="{{ route('admin.prompts.store') }}">
        @csrf
        @if (Auth::user()->role == 1)
        <div class="form-group">
            <label for="category">{{ __('prompt.Category') }}</label>
            <select id="category" name="category_id" class="form-control">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ __("common.Category." . $category->name) }}</option>
                @endforeach
            </select>
        </div>
        @else
        <input type="hidden" name="category_id" value="0" />
        @endif
        <div class="form-group">
            <label for="title">{{ __('prompt.Title') }}</label>
            <input id="title" type="text" name="title" required class="form-control">
        </div>
        <div class="form-group">
            <label for="content">{{ __('prompt.Content') }}</label>
            <textarea id="content" name="content" required class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('common.Create') }}</button>
    </form>
</div>
@endsection