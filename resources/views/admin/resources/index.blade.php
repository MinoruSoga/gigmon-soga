@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-start">
        <h1>{{ __('common.Resources') }}</h1>
        @if(auth()->user()->role === 1 || auth()->user()->role === 2)
        <a href="{{ route('admin.resources.create') }}" class="btn btn-primary">{{ __('resource.Create Resource') }}</a>
        @endif
    </div>
    <p id="page_count" style="text-align: right;"></p>
    <div class="rounded-table">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('common.ID') }}</th>
                <th>{{ __('resource.Title') }}</th>
                <th>{{ __('resource.Type') }}</th>
                <th>{{ __('resource.Status') }}</th>
                <th>{{ __('common.Show') }}</th>
                @if(auth()->user()->role === 1 || auth()->user()->role === 2)
                <th>{{ __('common.Delete') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @php
                $sum = 0;
            @endphp
            @foreach($resources as $resource)
                @php
                    $sum += $resource['pageCount'] ?? 0;
                @endphp
                <tr>
                    <td>{{ $resource['id'] }}</td>
                    <td>{{ $resource['title'] }}</td>
                    <td>{{ $resource['type'] }}</td>
                    <td>{{ $resource['status'] }}</td>
                    <td>
                        <a href="{{ route('admin.resources.show', $resource['id']) }}" class="btn btn-outline-primary">{{ __('common.Show') }}</a>
                    </td>
                    @if(auth()->user()->role === 1 || auth()->user()->role === 2)
                    <td>
                        <form action="{{ route('admin.resources.destroy', $resource['id']) }}" method="post" onsubmit="return confirm('{{ __('common.Confirm Deletion') }}');">
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
    <script>
        setTimeout(function(){
            document.getElementById('page_count').innerHTML = '{{ __('resource.total_pages') }}' + '{{ $sum }}' + '/' + '{{ $total_source_pages }}';
        }, 1000);
    </script>
    </div>
</div>
@endsection
