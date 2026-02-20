<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'COACHTECH')</title>

    {{-- ページ別CSS --}}
    @yield('css')
</head>
<body>

<header class="header" style="
    background-color: #000;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: space-between;
">
    {{-- ロゴ --}}
    <div class="header-logo" style="margin-left: 20px;">
        <img 
            src="{{ asset('images/COACHTECH-logo.png') }}" 
            alt="COACHTECHロゴ"
            style="height: 24px;"
        >
    </div>

    {{-- ナビゲーション --}}
    <nav style="margin-right: 20px;">
        <ul style="
            display: flex;
            gap: 20px;
            list-style: none;
            margin: 0;
            padding: 0;
        ">
            <li><a href="/attendance" style="color:#fff;">勤怠</a></li>
            <li><a href="{{ route('attendance.requests.index') }}" style="color:#fff;">勤怠一覧</a></li>
            <li><a href="/application" style="color:#fff;">申請</a></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" style="
                        background:none;
                        border:none;
                        color:#fff;
                        cursor:pointer;
                        padding:0;
                    ">
                        ログアウト
                    </button>
                </form>
            </li>
        </ul>
    </nav>
</header>

<main>
    @yield('content')
</main>

{{-- ページ別JS --}}
@yield('js')

</body>
</html>
