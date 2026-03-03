@extends('layouts.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="title">ログイン</h1>

    <form class="login-form" method="POST" action="{{ route('login') }}">
        @csrf

        {{-- ログイン情報不一致エラー --}}
        @if ($errors->has('login'))
            <p class="error-message">
                {{ $errors->first('login') }}
            </p>
        @endif

        {{-- メールアドレス --}}
        <div class="form-group">
            <label>メールアドレス</label>
            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                autofocus
            >

            @error('email')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        {{-- パスワード --}}
        <div class="form-group">
            <label>パスワード</label>
            <input
                type="password"
                name="password"
            >

            @error('password')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-submit">
            ログインする
        </button>

        <div class="login-link">
            <a href="{{ route('register') }}">会員登録はこちら</a>
        </div>
    </form>
</div>
@endsection
