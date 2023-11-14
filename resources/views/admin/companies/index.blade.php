@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-start">
        <h1>{{ __('common.Companies') }}</h1>
        <a href="{{ route('admin.companies.create') }}" class="btn btn-primary">{{ __('company.Create Company') }}</a>
    </div>
    {{ $companies->links('pagination::bootstrap-4') }}
    <div class="rounded-table">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('common.ID') }}</th>
                <th>{{ __('company.Name') }}</th>
                <th>{{ __('company.Plan') }}</th>
                <th>{{ __('company.Created') }}</th>
                <th>{{ __('company.Unsubscribed At') }}</th>
                <th>{{ __('company.Number of users') }}</th>
                <th>{{ __('company.Number of words utilized this month') }}</th>
                <th>{{ auth()->user()->role == 2 ? __('common.Browse') : __('common.Edit') }}</th>
                <th>{{ auth()->user()->role == 2 ? __('company.Logon') : __('common.IPrestrict') }}</th>
                @if(auth()->user()->role != 2)
                    <th>{{ __('common.Delete') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($companies as $company)
                <tr>
                    <td>{{ $company->id }}</td>
                    <td>{{ $company->name }}</td>
                    @if(isset($company->plan))
                        <td>{{ $company->plan->name }}</td>
                    @else
                        <td>&nbsp;</td>
                    @endif
                    <td>{{ $company->created_at->format('Y/m/d') }}</td>
                    <td>{{ $company->unsubscribed_at }}</td>
                    <td>{{ $company->employees->count() }}</td>
                    <td>{{ $company->getWordsCount() }}</td>
                    <td>
                        @if(auth()->user()->role == 2)
                            <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-outline-primary">{{ __('common.Browse') }}</a>
                        @else
                            <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-outline-primary">{{ __('common.Edit') }}</a>
                        @endif
                    </td>
                    <td>
                        @if(auth()->user()->role == 2)
                            <a href="{{ route('switch.child', $company->id) }}"class="btn btn-outline-secondary">{{ __('company.Logon') }}</a>
                        @else
                            <a href="{{ route('admin.companies.security', $company) }}" class="btn btn-outline-secondary">{{ __('common.IPrestrict') }}</a>
                        @endif
                    </td>
                    @if(auth()->user()->role != 2)
                        <td>
                            <form action="{{ route('admin.companies.destroy', $company) }}" method="post">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">{{ __('common.Delete') }}</button>
                            </form>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    {{ $companies->links('pagination::bootstrap-4') }}
</div>
@endsection
