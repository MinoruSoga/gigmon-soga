@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('notification.Edit Notification') }}</h1>
    <form method="POST" action="{{ route('admin.notifications.update', $notification) }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="title">{{ __('notification.Title') }}</label>
            <input id="title" type="text" name="title" value="{{ $notification->title }}" required class="form-control">
        </div>
        <div class="form-group">
            <label for="content">{{ __('notification.Content') }}</label>
            <textarea id="content" name="content" required class="form-control">{{ $notification->content }}</textarea>
        </div>
        <div class="form-group">
            <label for="display_from">{{ __('notification.Display From') }}</label>
            <input id="display_from" type="date" name="display_from" value="{{ $notification->display_from }}" class="form-control">
        </div>
        <div class="form-group">
            <label for="display_to">{{ __('notification.Display To') }}</label>
            <input id="display_to" type="date" name="display_to" value="{{ $notification->display_to }}" class="form-control">
        </div>
        <div class="form-group">
            <input id="priority_flag" type="checkbox" name="priority_flag" value="1" {{ $notification->priority_flag ? 'checked' : '' }} class='me-1'>
            <label for="priority_flag">{{ __('notification.Priority Flag') }}</label>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('common.Update') }}</button>
    </form>
</div>
@endsection