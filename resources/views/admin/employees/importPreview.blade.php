@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('employee.Edit Employee') }}</h1>
    @if(count($csv_errors) > 0)
    <div class="alert alert-danger">
        <ul>
            @foreach ($csv_errors as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <p>{{ __('employee.Importable Count', ['count' => count($validData)]) }}</p>
    <p>{{ __('employee.Unimportable Count', ['count' => $errorCount]) }}</p>

    <form action="{{ route('admin.employees.import') }}" method="post">
        @csrf
        <input type="hidden" name="data" value="{{ json_encode($validData) }}">
        <button type="submit" class="btn btn-primary">{{ __('employee.Register') }}</button>
    </form>
</div>
@endsection