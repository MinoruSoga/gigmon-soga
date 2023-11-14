@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-start">
        <h1>{{ __('common.Prompts') }}</h1>
        <a href="{{ route('admin.prompts.create') }}" class="btn btn-primary">{{ __('prompt.Create Prompt') }}</a>
    </div>
    {{ $prompts->links('pagination::bootstrap-4') }}
    <div class="rounded-table">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('common.ID') }}</th>
                @if (Auth::user()->role == 1)
                    <th>{{ __('prompt.Category') }}</th>
                @endif
                <th>{{ __('prompt.Title') }}</th>
                <th>{{ __('prompt.Content') }}</th>
                <th>{{ __('common.Edit') }}</th>
                <th>{{ __('common.Delete') }}</th>
                @php
                $exist = DB::table('companies')
                        ->where('parent_company_id', Auth::user()->company_id)
                        ->exists();
                @endphp
                @if ($exist && Auth::user()->role == 2)
                    <th>{{ __('common.Bulk Register') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($prompts as $prompt)
                <tr>
                    <td>{{ $prompt->id }}</td>
                    @if (Auth::user()->role == 1)
                        <td style="white-space:nowrap;">{{ __("common.Category." . $prompt->category->name) }}</td>
                    @endif
                    <td style="white-space:nowrap;">{{ $prompt->title }}</td>
                    <td>{{ $prompt->content }}</td>
                    <td>
                        <a href="{{ route('admin.prompts.edit', $prompt) }}" class="btn btn-outline-primary">{{ __('common.Edit') }}</a>
                    </td>
                    <td>
                        <form action="{{ route('admin.prompts.destroy', $prompt) }}" method="post" onsubmit="return confirm('{{ __('common.Confirm Deletion') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">{{ __('common.Delete') }}</button>
                        </form>
                    </td>
                    @if ($exist && Auth::user()->role == 2)
                        <td>
                            <form action="{{ route('admin.prompts.mass_store', $prompt) }}" method="post" onsubmit="return confirmBulkRegister()">
                                @csrf
                                <button type="submit" class="btn btn-primary">{{ __('common.Bulk Register') }}</button>
                            </form>
                        </td> 
                    @endif
               </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    {{ $prompts->links('pagination::bootstrap-4') }}
</div>
<script>
    function confirmBulkRegister() {
        var choice = confirm("{{ __('common.Prompt Registration') }}");
        if (choice) {
            return true;
        } else {
            return false;
        }
    }
</script>
@endsection
