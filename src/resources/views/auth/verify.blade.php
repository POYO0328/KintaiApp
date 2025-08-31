@extends('layouts.app')

@section('content')
<div class="container">
    <h1>メール認証が必要です</h1>
    <p>ご登録のメールアドレスに確認メールを送信しました。</p>
    <p>届いていない場合は、以下のボタンから再送してください。</p>

    @if (session('resent'))
        <div class="alert alert-success">
            認証リンクを再送しました。
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-primary">認証メールを再送</button>
    </form>
</div>
@endsection
