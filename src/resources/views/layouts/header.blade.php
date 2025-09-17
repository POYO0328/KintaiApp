<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>勤怠アプリ</title>
  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/common.css') }}">
  @yield('css')
</head>

<body>
    <header class="header">
      <div class="header__inner">
        <div class="header-utilities">
          <a class="header__logo">
            <img src="/images/logo.svg" alt="COACHTECH">
          </a>
          <nav>
            <ul class="header-nav">
            </ul>
          </nav>
        </div>
      </div>
    </header>

  <main>
    @yield('content')
  </main>
  @stack('scripts')
</body>

</html>
