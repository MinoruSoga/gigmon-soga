@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('plan.Create Plan') }}</h1>
    <form action="{{ route('admin.plans.store') }}" method="post">
        @csrf
        <div class="form-group">
            <label>{{ __('plan.Name') }}</label>
            <input type="text" name="name" required class="form-control">
        </div>
        <div class="form-group">
            <label>{{ __('plan.Max Users') }}</label>
            <input type="number" name="max_users" required class="form-control">
        </div>
        <div class="form-group">
            <label>{{ __('plan.Max Prompts') }}</label>
            <input type="number" name="max_prompts" required class="form-control">
        </div>
        <div class="form-group">
            <label>{{ __('plan.Knowledge Q&A') }}</label>
            <select name="knowledge_base_enabled" required class="form-control">
                <option value="1">{{ __('common.Yes') }}</option>
                <option value="0">{{ __('common.No') }}</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('common.Create') }}</button>
    </form>
</div>
@endsection