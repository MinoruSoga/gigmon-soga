@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-start">
        <h1>{{ __('common.Prohibited Words') }}</h1>
        <a href="{{ route('admin.prohibited_words.create') }}" class="btn btn-primary">{{ __('prohibited_word.Create Prohibited Word') }}</a>
    </div>
    <div class="rounded-table">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('common.ID') }}</th>
                <th>{{ __('prohibited_word.Word') }}</th>
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
            @foreach($prohibited_words as $prohibited_word)
                <tr>
                    <td>{{ $prohibited_word->id }}</td>
                    <td>{{ $prohibited_word->word }}</td>
                    <td>
                        <a href="{{ route('admin.prohibited_words.edit', $prohibited_word) }}" class="btn btn-outline-primary">{{ __('common.Edit') }}</a>
                    </td>
                    <td>
                        <form action="{{ route('admin.prohibited_words.destroy', $prohibited_word) }}" method="post"onsubmit="return confirm('{{ __('common.Confirm Deletion') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">{{ __('common.Delete') }}</button>
                        </form>
                    </td>
                    @if ($exist && Auth::user()->role == 2)
                        <td>
                            <form action="{{ route('admin.prohibited_words.mass_store', $prohibited_word) }}" method="post" onsubmit="return confirmBulkRegister()">
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
</div>
<script>
    function confirmBulkRegister() {
        var choice = confirm("{{ __('common.Prohibited Word Registration') }}");
        if (choice) {
            return true;
        } else {
            return false;
        }
    }
</script>
@endsection
