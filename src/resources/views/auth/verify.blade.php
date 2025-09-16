@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="verify-container">
    <p class="verify-message">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>

    <div class="verify-button-area">
        <a href="https://mailtrap.io/inboxes" target="_blank" class="btn-verify">
            認証はこちらから
        </a>
    </div>

    @if (session('resent'))
        <div class="alert-success">
            認証リンクを再送しました。
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="resend-form">
        @csrf
        <button type="submit" class="btn-resend">認証メールを再送する</button>
    </form>
</div>
@endsection
