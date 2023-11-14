@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-start">
        <h1>{{ __('common.Notifications') }}</h1>
        @if (Auth::user()->role == 1)
        <a href="{{ route('admin.notifications.create') }}"
            class="btn btn-primary">{{ __('notification.Create Notification') }}</a>
        @endif
    </div>


    {{ $notifications->links('pagination::bootstrap-4') }}
    @if (Auth::user()->role == 1)
    <div class="rounded-table">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('common.ID') }}</th>
                        <th>{{ __('notification.Title') }}</th>
                        <th>{{ __('notification.Content') }}</th>
                        <th>{{ __('notification.Created') }}</th>
                        @if (Auth::user()->role == 1)
                        <th>{{ __('notification.Displaying?') }}</th>
                        <th>{{ __('notification.Priority?') }}</th>
                        <th>{{ __('common.Edit') }}</th>
                        <th>{{ __('common.Delete') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($notifications as $notification)
                    <tr>
                        <td>{{ $notification->id }}</td>
                        <td style="white-space:nowrap;">{{ $notification->title }}</td>
                        <td class="text-start">{!! nl2br(e($notification->content)) !!}</td>
                        <td>{{ $notification->created_at->format('Y/m/d') }}</td>
                        @if (Auth::user()->role == 1)
                        <td>
                            @if(is_null($notification->display_from) || $notification->display_from <= now())
                                @if(is_null($notification->display_to) || $notification->display_to >= now())
                                {{ __('notification.Display') }}
                                @else
                                {{ __('notification.Not Display') }}
                                @endif
                                @else
                                {{ __('notification.Not Display') }}
                                @endif
                        </td>
                        <td>
                            {{ $notification->priority_flag ? __('notification.Priority') : ''}}
                        </td>
                        <td>
                            <a href="{{ route('admin.notifications.edit', $notification) }}"
                                class="btn btn-outline-primary">{{ __('common.Edit') }}</a>
                        </td>
                        <td>
                            <form action="{{ route('admin.notifications.destroy', $notification) }}" method="post"
                                onsubmit="return confirm('{{ __('common.Confirm Deletion') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">{{ __('common.Delete') }}</button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else


    <div class="list-group">
        @foreach($notifications as $notification)
        <div class="list-group-item py-3">
            <h5 class="mb-1 text-break">{{ $notification->title }}</h5>
            <p class="mb-1 text-break">{!! nl2br(e($notification->content)) !!}</p>
            <small>{{ $notification->created_at->format('Y年m月d日 H:i') }}</small>
        </div>
        @endforeach
    </div>
    @endif
    {{ $notifications->links('pagination::bootstrap-4') }}
</div>
@endsection
