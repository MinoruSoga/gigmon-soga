@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-start">
        <h1>{{ __('common.Plans') }}</h1>
        <a href="{{ route('admin.plans.create') }}" class="btn btn-primary">{{ __('plan.Create Plan') }}</a>
    </div>
    <div class="rounded-table">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('common.ID') }}</th>
                <th>{{ __('plan.Name') }}</th>
                <th>{{ __('plan.Max Users') }}</th>
                <th>{{ __('plan.Max Prompts') }}</th>
                <th>{{ __('plan.Knowledge Q&A') }}</th>
                <th>{{ __('common.Edit') }}</th>
                <th>{{ __('common.Delete') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($plans as $plan)
                <tr>
                    <td>{{ $plan->id }}</td>
                    <td>{{ $plan->name }}</td>
                    <td>{{ $plan->max_users }}</td>
                    <td>{{ $plan->max_prompts }}</td>
                    <td>{{ $plan->knowledge_base_enabled ? __('common.Yes') : __('common.No') }}</td>
                    <td>
                        <a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-outline-primary">{{ __('common.Edit') }}</a>
                    </td>
                    <td>
                        <form action="{{ route('admin.plans.destroy', $plan) }}" method="post">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">{{ __('common.Delete') }}</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
@endsection
