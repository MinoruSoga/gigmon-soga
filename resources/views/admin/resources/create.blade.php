@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('resource.Create Resource') }}</h1>
    <form action="{{ route('admin.resources.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label>{{ __('resource.Title') }}</label>
            <input type="text" name="title" required class="form-control">
        </div>
        <div class="form-group">
            <label>{{ __('resource.Type') }}</label>
            <select name="type" required class="form-control">
                <option value="url">url</option>
                <option value="document">document</option>
                {{-- <option value="sitemap">sitemap</option> --}}
                {{-- <option value="wp">wp</option> --}}
                {{-- <option value="urls">urls</option> --}}
                {{-- <option value="csv">csv</option> --}}
            </select>
        </div>
        <div class="form-group">
            <label>{{ __('resource.Url') }}</label>
            <input type="text" name="url" class="form-control">
        </div>
        <div class="form-group">
            <input type="file" name="file">
        </div>
        <div class="btn-group" role="group">
            <button type="submit" name="upload" value="single" class="btn btn-primary rounded" style="margin-right: 10px;">{{ __('common.Create') }}</button>
            @php
            $exist = DB::table('companies')
                    ->where('parent_company_id', Auth::user()->company_id)
                    ->exists();
            @endphp

            @if ($exist && Auth::user()->role == 2)
            <button type="submit" name="upload" value="multiple" class="btn btn-primary rounded">{{ __('common.Bulk Register') }}</button>
            @endif
        </div>
    </form>
</div>
@endsection