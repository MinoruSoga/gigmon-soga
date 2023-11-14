<p>GiGMONのユーザー登録が完了しました。</p>
<br>
<p>■ GiGMON URL: {{ config('app.url') }}</p>
<p>■ ユーザー名: {{ $user->name }}</p>
<p>■ パスワード: {{ $plainPassword }}</p>
<br>
<p>※ メール情報の厳重な管理をお願いします。</p>
<br>
<p>不明点や質問がある場合、以下の連絡先やGiGMONのお問い合わせフォームをご利用ください。</p>
<p><a href="{{ config('app.url') }}/contact">{{ config('app.url') }}/contact</a></p>
<br>
@foreach($admins as $admin) <!-- 複数の管理者に対応するためのループ -->
    <p>{{ $admin->name }}</p>
    <p>{{ $admin->email }}</p>
    <br> <!-- 各管理者の間に改行を追加 -->
@endforeach
<p>※ このメールアドレスは配信専用です。直接の返信は受け付けておりません。</p>
