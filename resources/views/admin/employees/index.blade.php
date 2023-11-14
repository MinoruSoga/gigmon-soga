@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-start">
        <h1>{{ __('common.Employees') }}</h1>
        <div class='text-right'>
            <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">{{ __('employee.Create Employee') }}</a>
            <a href="{{ route('admin.employees.importForm') }}" class="btn btn-primary">{{ __('employee.Import CSV') }}</a>
        </div>
    </div>
    <form action="{{ route('admin.employees.index') }}" method="get">
        <div class="form-group">
            <input type="text" name="search" value="{{ $search }}" class="form-inline" placeholder="{{ __('employee.Enter username or email address') }}">
            <button type="submit" class="btn btn-primary">{{ __('employee.Search') }}</button>
            <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">{{ __('employee.Clear') }}</a>
        </div>
    </form>
    {{ $employees->links('pagination::bootstrap-4') }}
    <div class="rounded-table">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('common.ID') }}</th>
                <th>
                    <a href="{{ route('admin.employees.index', ['search' => session('search'), 'sort' => 'name', 'direction' => session('direction') === 'asc' && session('sort') === 'name' ? 'desc' : 'asc']) }}">▲</a>
                    &nbsp;{{ __('employee.Name') }}&nbsp;
                    <a href="{{ route('admin.employees.index', ['search' => session('search'), 'sort' => 'name', 'direction' => session('direction') === 'desc' && session('sort') === 'name' ? 'asc' : 'desc']) }}">▼</a>
                </th>
                <th>
                    <a href="{{ route('admin.employees.index', ['search' => session('search'), 'sort' => 'email', 'direction' => session('direction') === 'asc' && session('sort') === 'name' ? 'desc' : 'asc']) }}">▲</a>
                    &nbsp;{{ __('employee.Email') }}&nbsp;
                    <a href="{{ route('admin.employees.index', ['search' => session('search'), 'sort' => 'email', 'direction' => session('direction') === 'desc' && session('sort') === 'name' ? 'asc' : 'desc']) }}">▼</a>
                </th>
                <th>{{ __('employee.Role') }}</th>
                <th>
                    <a href="{{ route('admin.employees.index', ['search' => session('search'), 'sort' => 'words_count', 'direction' => session('direction') === 'asc' && session('sort') === 'name' ? 'desc' : 'asc']) }}">▲</a>
                    &nbsp;{{ __('employee.Words Count') }}&nbsp;
                    <a href="{{ route('admin.employees.index', ['search' => session('search'), 'sort' => 'words_count', 'direction' => session('direction') === 'desc' && session('sort') === 'name' ? 'asc' : 'desc']) }}">▼</a>
                </th>
                <th>{{ __('employee.Active Status') }}</th>
                <th>{{ __('common.Edit') }}</th>
                <th>{{ __('common.Activate') }}</th>
                <th>{{ __('employee.Last Login Date and Time') }}</th>
                <th>{{ __('common.Delete') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $employee)
                <tr>
                    <td>{{ $employee->id }}</td>
                    <td>{{ $employee->user->name }}</td>
                    <td>{{ $employee->user->email }}</td>
                    <td>@switch($employee->user->role)
                        @case(1)
                            {{ __('common.System Admin') }}
                            @break;
                        @case(2)
                            {{ __('common.Company Admin') }}
                            @break;
                        @case(3)
                            {{ __('common.User') }}
                            @break;
                    @endswitch</td>
                    <td>@php
                        $month = date('m');
                    @endphp
                    <a href="{{ route('admin.employees.conversations', $employee->id) }}">
                        <span class="employee-tooltip">
                            <span class="employee-tooltip-text">{!!
                                __('common.ChatGPT Mode') .
                                number_format(
                                    $employee->user->conversations()->where('conversation_system_id', 1)->whereMonth('created_at', $month)->sum(DB::raw('CHAR_LENGTH(message)')) +
                                    $employee->user->conversations()->where('conversation_system_id', 1)->whereMonth('created_at', $month)->sum(DB::raw('CHAR_LENGTH(response)'))
                                ) .
                                '&nbsp;/&nbsp;' . __('common.Knowledge Mode') .
                                number_format(
                                    $employee->user->conversations()->where('conversation_system_id', 2)->whereMonth('created_at', $month)->sum(DB::raw('CHAR_LENGTH(message)')) +
                                    $employee->user->conversations()->where('conversation_system_id', 2)->whereMonth('created_at', $month)->sum(DB::raw('CHAR_LENGTH(response)'))
                                )
                            !!}</span>
                            {{ number_format($employee->words_count) }}
                        </span>
                    </a></td>
                    <td>{{ $employee->active ? __('common.Active') : __('common.Inactive') }}</td>
                    <td>
                        <a href="{{ route('admin.employees.edit', $employee->id) }}" class="btn btn-outline-primary">{{ __('common.Edit') }}</a>
                    </td>
                    <td>
                        @if ($employee->active)
                            <form action="{{ route('admin.employees.deactivate', $employee->id) }}" method="post">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-outline-danger">{{ __('common.Deactivate') }}</button>
                            </form>
                        @else
                            <form action="{{ route('admin.employees.activate', $employee->id) }}" method="post">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-outline-success">{{ __('common.Activate') }}</button>
                            </form>
                        @endif
                    </td>
                    <td>{{ $employee->user->last_login_at }}</td>
                    <td>
                        @if (!$employee->active)
                            <form action="{{ route('admin.employees.destroy', $employee) }}" method="post" onsubmit="return confirm('{{ __('common.Confirm Deletion') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">{{ __('common.Delete') }}</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    {{ $employees->links('pagination::bootstrap-4') }}
</div>
<style>
.employee-tooltip{
    position:relative;
    cursor:pointer
}

.employee-tooltip:hover .employee-tooltip-text{
    opacity:1;
    visibility:visible
}

.employee-tooltip-text{
    opacity:0;
    visibility:hidden;
    position:absolute;
    left:50%;
    transform:translateX(-50%);
    bottom:-35px;
    display:inline-block;
    padding:5px;
    white-space:nowrap;
    font-size:10.5px;
    line-height:1.3;
    background:#333;
    color:#fff;
    border-radius:3px;
    transition:0.3s ease-in;
    box-shadow:0 1px 2px rgba(0,0,0,0.3)
}

.employee-tooltip-text:before{
    content:'';
    position:absolute;
    top:-13px;
    left:50%;
    margin-left:-7px;
    border:7px solid transparent;
    border-bottom:7px solid #333
}
</style>
@endsection
