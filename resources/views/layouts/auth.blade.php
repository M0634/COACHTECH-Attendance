<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'COACHTECH')</title>

    @yield('css')
</head>
<body>

<header class="header" style="
    background-color: #000;
    height: 60px;
    display: flex;
    align-items: center;
">
    <div class="header-logo" style="
        margin-left: 20px;
    ">
        <img 
            src="{{ asset('images/COACHTECH-logo.png') }}" 
            alt="COACHTECHロゴ"
            style="height: 24px;"
        >
    </div>
</header>


<main>
    @yield('content')
</main>

</body>
</html>
