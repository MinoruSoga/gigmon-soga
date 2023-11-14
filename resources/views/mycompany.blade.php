@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('common.Mycompany') }}</h1>
    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('mycompany.update') }}">
        @csrf
        <div class="card">
            <div class="card-header">{{ __('common.Company Information') }}</div>
            <div class="card-body">
                <div class="form-group">
                    <label for="name">{{ __('company.Name') }}</label>
                    <label>{{ $company->name }}</label>
                </div>
                <div class="form-group">
                    <label for="postal_code">{{ __('common.Company Postal Code') }}</label>
                    <input id="postal_code" class="form-control" type="text" name="postal_code" value="{{ $company->postal_code }}" required>
                </div>

                <div class="form-group">
                    <label for="prefecture">{{ __('common.Company Prefecture') }}</label>
                    <input id="prefecture" class="form-control" type="text" name="prefecture" value="{{ $company->prefecture }}" required>
                </div>

                <div class="form-group">
                    <label for="city">{{ __('common.Company City') }}</label>
                    <input id="city" class="form-control" type="text" name="city" value="{{ $company->city }}" required>
                </div>

                <div class="form-group">
                    <label for="address">{{ __('common.Company Address') }}</label>
                    <input id="address" class="form-control" type="text" name="address" value="{{ $company->address }}" required>
                </div>

                <div class="form-group">
                    <label for="building">{{ __('common.Company Building') }}</label>
                    <input id="building" class="form-control" type="text" name="building" value="{{ $company->building }}" >
                </div>
        {{--
                <div class="form-group">
                    <label for="email">{{ __('employee.Email') }}</label>
                    <label>{{ $user->email }}</label>
                </div>
                <div class="form-group">
                    <label for="password">{{ __('employee.Password (leave blank to keep unchanged)') }}</label>
                    <input type="password" id="password" name="password" class="form-control">
                </div>
                <div class="form-group">
                    <label for="password_confirmation">{{ __('employee.Password Confirmation') }}</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                </div>
        --}}
                {{-- <button type="submit" class="btn btn-primary">{{ __('common.Update') }}</button> --}}
            </div>

            <div class="card-header">{{ __('common.Agent Information') }}</div>
            <div class="card-body">
                <div class="form-group">
                    <label for="agency_code">{{ __('login.Agency Code') }}</label>
                    <label>{{ $company->agency_code }}</label>
                </div>
                <div class="form-group">
                    <label for="staff_code">{{ __('login.Staff Code') }}</label>
                    <label>{{ $company->staff_code }}</label>
                </div>
            </div>

            <div class="card-header">{{ __('common.Billing Information') }}</div>
            <div class="card-body">
                <div class="form-group">
                    <label for="accounting_email">{{ __('common.Accounting Email Address') }}</label>
                    <input id="accounting_email" type="text" name="accounting_email" value="{{ $company->accounting_email }}" class="form-control">
                </div>
            </div>

            <div class="card-header">{{ __('common.Usage status') }}</div>
            <div class="card-body">
                <div class="form-group">
                    <label for="staff_count">従業員数</label>
                    <label>{{ $company->employees->count() }}</label>
                </div>
                <div class="form-group">
                    <label for="staff_count">利用プラン</label>
                    <label>{{ $company->plan->name }}</label>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('common.Month') }}</th>
                            <th>{{ __('common.ChatGPT Mode') }}</th>
                            <th>{{ __('common.Knowledge Mode') }}</th>
                            <th>{{ __('common.Total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usages as $ym => $usage)
                        <tr>
                            <th scope="row">{{ $ym }}</th>
                            <td>{{ number_format($usage['chatgpt']) }}</td>
                            <td>{{ number_format($usage['docsbot']) }}</td>
                            <td>{{ number_format($usage['chatgpt'] + $usage['docsbot']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">{{ __('common.Update') }}</button>
    </form>
</div>
@endsection
