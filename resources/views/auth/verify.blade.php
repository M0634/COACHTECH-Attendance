@extends('layouts.auth')

@section('title', 'メール認証 | COACHTECH')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify.css') }}">
@endsection

@section('content')
<div class="verify-container">
    <p class="verify-message">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>

    <a href="http://localhost:8025" class="verify-button" target="_blank" rel="noopener">
        認証メールを確認する
    </a>


    <form method="POST" action="{{ route('verification.resend') }}">
        @csrf
        <button type="submit" class="resend-link">
            認証メールを再送する
        </button>
    </form>
</div>
@endsection
