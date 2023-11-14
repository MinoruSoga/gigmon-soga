@extends('layouts.app')

@section('main-padding', '')

@section('content')
    @auth
        <div id="react-root" data-user={{ Auth::user()->name }} data-role={{ Auth::user()->role }} data-token={{ csrf_token() }}></div>
    @endauth

    <script>
        function getCsrfToken() {
            return '{{ csrf_token() }}';
        }
    </script>
@endsection
