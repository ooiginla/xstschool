<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
   <link rel="icon" href="favicon.ico" type="image/ico" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/animate.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/owl.carousel.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/styles.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/meanmenu.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/font-awesome.min.css') }}" />
    <link rel="stylesheet" href="https://cdn.linearicons.com/free/1.0.0/icon-font.min.css">
    <script src="{{ asset('js/libs/modernizr.custom.js') }}"></script>
    <title>Christ School Ado Ekiti - 98/04 Set</title>
</head>
<body>

<div class="main-wrapper">

    <div class="header-wrapper header-position">
        @include('pages.header')
    </div>

    <div class="content-wrapper">
        @yield('content')
    </div>

    <div class="footer-wrapper type2">
    @include('pages.footer')
    </div>

</div>

<script src="{{ asset('js/libs/jquery-2.2.4.min.js') }}"></script>
<script src="{{ asset('s/libs/bootstrap.min.js') }}"></script>
<script src="{{ asset('js/libs/owl.carousel.min.js') }}"></script>
<script src="{{ asset('js/libs/jquery.meanmenu.js') }}"></script>
<script src="{{ asset('js/libs/jquery.syotimer.js') }}"></script>
<script src="{{ asset('js/libs/parallax.min.js') }}"></script>
<script src="{{ asset('js/libs/jquery.waypoints.min.js') }}"></script>
<script src="{{ asset('js/custom/main.js') }}"></script>
<script>
    jQuery(document).ready(function () {
        $('#time').syotimer({
            year: 2023,
            month: 12,
            day: 31,
            hour: 7,
            minute: 7,
        });
    });
</script>
</body>
</html>