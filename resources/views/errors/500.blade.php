@extends('layouts.app')

@section('content')
<div class="container">
    <h1>エラーが発生しました。</h1>
    <!-- 前の画面に戻るリンクを追加 -->
    <p>時間をおいて再度お試しください。</p>
    <a href="javascript:history.back();">前の画面に戻る</a>
</div>
@endsection