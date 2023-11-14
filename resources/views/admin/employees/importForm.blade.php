@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('employee.Edit Employee') }}</h1>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <a href="{{ route('admin.employees.export') }}" class="btn btn-secondary mb-3">{{ __('employee.Download CSV') }}</a>

    <form action="{{ route('admin.employees.importPreview') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="csv_file">{{ __('employee.CSV File') }}</label>
            <input type="file" name="csv_file" id="csv_file" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">{{ __('employee.Confirm') }}</button>
    </form>
</div>
@endsection