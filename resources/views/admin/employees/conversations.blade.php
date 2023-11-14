@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1>{{ __('employee.Status') }}</h1>
            <div class="row">
                <div class="col-md-6">
                    <a class="btn btn-link" href="{{ route('admin.employees.conversations', ['employee' => $employee->id, 'year' => $date->copy()->subMonth()->year, 'month' => $date->copy()->subMonth()->month]) }}">{{ __('employee.Previous Month') }}</a>
                </div>
                <div class="col-md-6 text-right" style="text-align: right">
                    <a class="btn btn-link" href="{{ route('admin.employees.conversations', ['employee' => $employee->id, 'year' => $date->copy()->addMonth()->year, 'month' => $date->copy()->addMonth()->month]) }}">{{ __('employee.Next Month') }}</a>
                </div>
            </div>
            <div class="card">
                <div class="card-header">{!! __('common.Usage status') . '&nbsp;-&nbsp;' . $date->format('Y/m') !!}</div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('common.ChatGPT Mode') }}</th>
                                <th>{{ __('common.Knowledge Mode') }}</th>
                                <th>{{ __('common.Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ number_format($chatgpt) }}</td>
                                <td>{{ number_format($docsbot) }}</td>
                                <td>{{ number_format($chatgpt + $docsbot) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-header">{!! __('employee.Conversation History', ['date' => $date->format('Y/m')]) !!}</div>

                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('employee.Date') }}</th>
                                <th>{{ __('employee.Message') }}</th>
                                <th>{{ __('employee.Response') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($conversations as $conversation)
                            <tr>
                                <td>{{ $conversation['created_at'] }}</td>
                                <td>{{ $conversation['message'] }}</td>
                                <td>{{ $conversation['response'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <a class="btn btn-link" href="{{ route('admin.employees.conversations', ['employee' => $employee->id, 'year' => $date->copy()->subMonth()->year, 'month' => $date->copy()->subMonth()->month]) }}">{{ __('employee.Previous Month') }}</a>
                </div>
                <div class="col-md-6 text-right" style="text-align: right">
                    <a class="btn btn-link" href="{{ route('admin.employees.conversations', ['employee' => $employee->id, 'year' => $date->copy()->addMonth()->year, 'month' => $date->copy()->addMonth()->month]) }}">{{ __('employee.Next Month') }}</a>
                </div>
            </div>
            <div class="d-grid gap-2 col-6 mx-auto">
                <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary mt-2">
                    {{ __('employee.Back') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
