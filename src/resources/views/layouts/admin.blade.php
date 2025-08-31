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
  @if (!in_array(Route::currentRouteName(), ['login', 'register']))
    <header class="header">
      <div class="header__inner">
        <div class="header-utilities">
          <a class="header__logo" href="/admin/attendances">
            <img src="/images/logo.svg" alt="COACHTECH">
          </a>
          <nav>
            <ul class="header-nav">
              {{-- ログイン状態で切り替え --}}
              @if (Auth::check())
              <li class="header-nav__item">
                <a class="header-nav__link" href="/admin/attendances">勤怠一覧</a>
              </li>

              <li class="header-nav__item">
                <a class="header-nav__link" href="/admin/staff/list">スタッフ一覧</a>
              </li>
              <li class="header-nav__item">
                <a class="header-nav__link" href="/stamp_correction_request/list">申請一覧</a>
              </li>
              <li class="header-nav__item">
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit" class="header-nav__button">ログアウト</button>
                </form>
              </li>
              @else

            @endif
            </ul>
          </nav>
        </div>
      </div>
    </header>
    @else
    <header class="header">
      <div class="header__inner">
        <div class="header-utilities">
          <a class="header__logo" href="/attendance">
            <img src="/images/logo.svg" alt="COACHTECH">
          </a>
          <nav>
            <ul class="header-nav">
            </ul>
          </nav>
        </div>
      </div>
    </header>
  @endif

  <main>
    @yield('content')
  </main>
  @stack('scripts')
</body>

</html>
