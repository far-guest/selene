<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('selene.title', 'Selene') }}</title>
    <link href="/selene/css/index.css" rel="stylesheet">
    @routes('selene')
    <script defer>
        window.user = @json(auth()->user());
        window.selene = @json(Selene::export());
        window.lang = @json(__('selene'));

        window.addEventListener('load', function (e) {
            $('#msbo').on('click', function () {
                $('body').toggleClass('show-sidebar-xs').toggleClass('show-sidebar-lg')
            });
            $('.main-content-wrapper').on('click', function () {
                $('body').removeClass('show-sidebar-xs');
            });
        })
    </script>
    <script src="/selene/js/index.js" defer></script>
</head>
<body class="rtl show-sidebar-lg">
<div id="app" style="display: none">
    <nav class="main-navbar navbar navbar-dark bg-dark navbar-expand-md fixed-top">
        <div class="container-fluid justify-content-start">
            <a href="javascript:void(0)" id="msbo" class="text-muted">
                <i class="ic fa fa-bars"></i>
            </a>

            <a class="navbar-brand" href="{{ url('/') }}">
                {{ ucfirst(config('app.name')) }}
            </a>
        </div>

        <div id="app-loading" v-if="isLoading">
            <div class="load-bar">
                <div class="bar"></div>
                <div class="bar"></div>
                <div class="bar"></div>
            </div>
        </div>
    </nav>

    <nav class="main-sidebar">
        <div class="user d-flex align-items-start">
            <div class="avatar">
                <img src="https://www.gravatar.com/avatar/{{ md5(strtolower(trim(auth()->user()->email))) }}?s=40"
                     alt="">
            </div>
            <div>
                <div class="username">{{ auth()->user()->name }}</div>
                <div class="title">{{ auth()->user()->email }}</div>
            </div>
        </div>

        <selene-menu></selene-menu>
    </nav>

    <div role="main" class="main-content-wrapper px-4">
        <router-view :key="$route.path"></router-view>
    </div>
</div>
</body>
</html>