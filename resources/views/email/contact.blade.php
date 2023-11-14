<p>{{ __('mail.You received a message from: ') }}{{ $data['name'] }}</p>
<p>{{ __('mail.Email: ') }}{{ $data['email'] }}</p>
<p>{{ __('mail.Message: ') }}{!! nl2br(e($data['message'])) !!}</p>