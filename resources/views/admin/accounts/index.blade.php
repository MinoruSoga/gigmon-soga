@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-start">
        <h1>{{ __('common.Accounts') }}</h1>
        <a href="{{ route('admin.accounts.create') }}" class="btn btn-primary">{{ __('account.Create Account') }}</a>
    </div>
    {{ $users->links('pagination::bootstrap-4') }}
    <div class="rounded-table">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('common.ID') }}</th>
                <th>{{ __('account.Name') }}</th>
                <th>{{ __('account.Email') }}</th>
                <th>{{ __('account.Company') }}</th>
                <th>{{ __('common.Edit') }}</th>
                <th>{{ __('common.Delete') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->employee->company->name }}</td>
                    <td>
                        <a href="{{ route('admin.accounts.edit', $user) }}" class="btn btn-outline-primary">{{ __('common.Edit') }}</a>
                    </td>
                    <td>
                        <form action="{{ route('admin.accounts.destroy', $user) }}" method="post">
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
    {{ $users->links('pagination::bootstrap-4') }}
</div>
@endsection
