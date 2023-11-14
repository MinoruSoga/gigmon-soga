@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('plan.Edit Plan') }}</h1>
    <form action="{{ route('admin.plans.update', $plan) }}" method="post">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label>{{ __('plan.Name') }}</label>
            <input type="text" name="name" value="{{ $plan->name }}" class="form-control">
        </div>
        <div class="form-group">
        <label>{{ __('plan.Max Users') }}</label>
            <input type="number" name="max_users" value="{{ $plan->max_users }}" class="form-control">
        </div>
        <div class="form-group">
            <label>{{ __('plan.Max Prompts') }}</label>
            <input type="number" name="max_prompts" value="{{ $plan->max_prompts }}" class="form-control">
        </div>
        <div class="form-group">
            <label>{{ __('plan.Knowledge Q&A') }}</label>
            <select name="knowledge_base_enabled" class="form-control">
                <option value="1" {{ $plan->knowledge_base_enabled ? 'selected' : '' }}>{{ __('common.Yes') }}</option>
                <option value="0" {{ !$plan->knowledge_base_enabled ? 'selected' : '' }}>{{ __('common.No') }}</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('common.Update') }}</button>
    </form>
</div>
@endsection