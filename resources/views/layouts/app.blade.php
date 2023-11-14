<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <script src="https://kit.fontawesome.com/8dbf256b0e.js" crossorigin="anonymous"></script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
    .active > .nav-link {
    border-bottom: 2px solid black;  /* Thickness and color of underline */
    padding-bottom: 1px; /* Distance from the text */
}
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-primary shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/home') }}">
                    <img src="{{ asset('image/logo.png') }}" alt="{{ config('app.name', 'Laravel') }}" style="max-height:40px;" />
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                    @auth
                        @if(auth()->user()->hasVerifiedEmail() && !auth()->user()->two_factor_code)
                        <li class="nav-item{{ url()->current() == route('react.chat') ? ' active' : '' }}">
                                <a class="nav-link nav-link-spacing nav-link-first" href="{{ route('react.chat') }}">
                                    <i class="fa-solid fa-comments"></i>&nbsp;{{ __('common.Chat') }}
                                </a>
                            </li>
                            <!-- <li class="nav-item{{ Request::is('home', '/react-prompts') ? ' active' : '' }}">
                            <a class="nav-link" href="{{ route('react.prompts') }}">{{ __('Prompts') }}</a>
                            </li> -->
                            @php
                                $company = App\Models\Company::where('parent_company_id', auth()->user()->company_id)->first();
                                //dump($company);
                                //dump(auth()->user()->company_id);
                            @endphp
                            @if(auth()->user()->role === 1 || (auth()->user()->role === 2 && !is_null($company)))
                                <li class="nav-item{{ url()->current() == route('admin.companies.index') ? ' active' : '' }}">
                                    <a class="nav-link nav-link-spacing" href="{{ route('admin.companies.index') }}">
                                        <i class="fa-solid fa-building"></i>&nbsp;{{ __('common.Companies') }}
                                    </a>
                                </li>
                            @endif
                            @if(auth()->user()->role === 1 || auth()->user()->role === 2)
                            <li class="nav-item{{ url()->current() == route('admin.employees.index') ? ' active' : '' }}">
                                <a class="nav-link nav-link-spacing" href="{{ route('admin.employees.index') }}">
                                    <i class="fa-solid fa-users"></i>&nbsp;{{ __('common.Employees') }}
                                </a>
                            </li>
                            <li class="nav-item{{ url()->current() == route('admin.prompts.index') ? ' active' : '' }}">
                                <a class="nav-link nav-link-spacing" href="{{ route('admin.prompts.index') }}">
                                    <i class="fa-solid fa-book-atlas"></i>&nbsp;{{ __('common.Prompts') }}
                                </a>
                            </li>
                            @endif
                            <li class="nav-item{{ url()->current() == route('admin.resources.index') ? ' active' : '' }}">
                                <a class="nav-link nav-link-spacing" href="{{ route('admin.resources.index') }}">
                                    <i class="fa-solid fa-file-lines"></i>&nbsp;{{ __('common.Resources') }}
                                </a>
                            </li>
                            @if(auth()->user()->role === 1 || auth()->user()->role === 2)
                            <li class="nav-item{{ url()->current() == route('admin.prohibited_words.index') ? ' active' : '' }}">
                                <a class="nav-link nav-link-spacing" href="{{ route('admin.prohibited_words.index') }}">
                                    <i class="fa-solid fa-ban"></i>&nbsp;{{ __('common.Prohibited Words') }}
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->role == 1)
                            <li class="nav-item{{ url()->current() == route('admin.plans.index') ? ' active' : '' }}">
                                <a class="nav-link nav-link-spacing" href="{{ route('admin.plans.index') }}">
                                    <i class="fa-solid fa-handshake"></i>&nbsp;{{ __('common.Plans') }}
                                </a>
                            </li>
                            <li class="nav-item{{ url()->current() == route('admin.accounts.index') ? ' active' : '' }}">
                                <a class="nav-link nav-link-spacing" href="{{ route('admin.accounts.index') }}">
                                    <i class="fa-solid fa-building-user"></i>&nbsp;{{ __('common.Accounts') }}
                                </a>
                            </li>
                            @endif
                            @if(Auth::user()->role === 1)
                            <li class="nav-item{{ url()->current() == route('admin.notifications.index') ? ' active' : '' }}">
                                <a class="nav-link nav-link-spacing" href="{{ route('admin.notifications.index') }}">
                                    <i class="fa-solid fa-building-user"></i>&nbsp;{{ __('common.Notifications') }}
                                </a>
                            </li>
                            @endif
                        @endif
                    @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        @if(!empty($companyName)) 
                            <div class="hide-on-mobile" style="height: 3.6em; width: 100px; overflow: hidden; color: black; font-size: 12px; display: flex; flex-direction: column; justify-content: center; word-wrap: break-word;">
                                {{ $companyName }}
                            </div>
                        @endif
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link nav-link-spacing" href="{{ route('login') }}">{{ __('login.Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link nav-link-spacing" href="{{ route('register') }}">{{ __('login.Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle hide-on-mobile" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('mypage') }}">
                                        <i class="fa-solid fa-user"></i>&nbsp;{{ __('common.Profile') }}
                                    </a>

                                    <a class="dropdown-item" href="{{ route('contact') }}">
                                        <i class="fa-solid fa-envelope"></i>&nbsp;{{ __('common.Contact') }}
                                    </a>

                                    <a class="dropdown-item" href="https://gigmon.ai/terms" target="_blank">
                                        <i class="fa-solid fa-file-lines"></i>&nbsp;{{ __('common.Terms') }}
                                    </a>

                                    <a class="dropdown-item" href="https://www.gig.co.jp/policy/privacy" target="_blank">
                                        <i class="fa-solid fa-eye"></i>&nbsp;{{ __('common.Privacy Policy') }}
                                    </a>

                                    @if(Auth::user()->role === 2)
                                        <a class="dropdown-item" href="{{ route('mycompany') }}">
                                            <i class="fa-solid fa-building"></i>&nbsp;{{ __('common.Mycompany') }}
                                        </a>
                                        <a class="dropdown-item" href="{{ route('react.payments') }}">
                                            <i class="fa-solid fa-credit-card"></i>&nbsp;{{ __('common.Payments') }}
                                        </a>
                                    @endif

                                    <!-- <hr />
                                    @foreach ((array)Config::get('languages') as $lang => $language)
                                        <a class="dropdown-item" href="{{ route('switch-language', $lang) }}">
                                            <i class="fa-solid fa-globe"></i>&nbsp;{{ __('languages.' . $lang) }}
                                        </a>
                                    @endforeach -->

                                    <hr />
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        <i class="fa-solid fa-right-from-bracket"></i>&nbsp;{{ __('login.Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                            <li class="nav-item{{ url()->current() == route('admin.notifications.index') ? ' active' : '' }}">
                                <a href="{{ route('admin.notifications.index') }}" class="nav-link">
                                    <i class="fa-solid fa-bell"></i><span class="d-md-none">&nbsp;{{ __('common.Notifications') }}</span>
                                </a>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        @if (session('warning'))
             <div class="alert alert-warning">
                {{ session('warning') }}
            </div>
        @endif

        <main class="@yield('main-padding', 'py-4')">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
