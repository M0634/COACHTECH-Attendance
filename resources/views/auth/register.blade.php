@extends('layouts.auth')

@section('title', '会員登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="title">会員登録</h1>

    <form class="register-form" method="POST" action="/register">
        @csrf

        {{-- 名前 --}}
        <div class="form-group">
            <label>名前</label>
            <input type="text" name="name" value="{{ old('name') }}">

            @error('name')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        {{-- メールアドレス --}}
        <div class="form-group">
            <label>メールアドレス</label>
            <input type="email" name="email" value="{{ old('email') }}">

            @error('email')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        {{-- パスワード --}}
        <div class="form-group">
            <label>パスワード</label>
            <input type="password" name="password">

            @error('password')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        {{-- パスワード確認 --}}
        <div class="form-group">
            <label>パスワード確認</label>
            <input type="password" name="password_confirmation">
        </div>

        <button type="submit" class="btn-submit">登録する</button>
    </form>

    <div class="login-link">
        <a href="/login">ログインはこちら</a>
    </div>
</div>
@endsection
