@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('resource.Show Resource') }}</h1>
    <form>
        <div class="form-group">
            <label>{{ __('resource.Id') }}</label>&nbsp;:&nbsp;
            <label>{{ $resource['id'] }}</label>
        </div>
        <div class="form-group">
            <label>{{ __('resource.Title') }}</label>&nbsp;:&nbsp;
            <label>{{ $resource['title'] }}</label>
        </div>
        <div class="form-group">
            <label>{{ __('resource.Type') }}</label>&nbsp;:&nbsp;
            <label>{{ $resource['type'] }}</label>
        </div>
        <div class="form-group">
            <label>{{ __('resource.Url') }}</label>&nbsp;:&nbsp;
            <label>{{ $resource['url'] }}</label>
        </div>
        <div class="form-group">
            <label>{{ __('resource.Created At') }}</label>&nbsp;:&nbsp;
            <label>{{ $resource['createdAt'] }}</label>
        </div>
        <div class="form-group">
            <label>{{ __('resource.Page Count') }}</label>&nbsp;:&nbsp;
            <label>{{ $resource['pageCount'] }}</label>
        </div>
        <div class="form-group">
            <label>{{ __('resource.Chunk Count') }}</label>&nbsp;:&nbsp;
            <label>{{ $resource['chunkCount'] }}</label>
        </div>
        <div class="form-group">
            <label>{{ __('resource.Status') }}</label>&nbsp;:&nbsp;
            <label>{{ $resource['status'] }}</label>
        </div>
    </form>
</div>
@endsection