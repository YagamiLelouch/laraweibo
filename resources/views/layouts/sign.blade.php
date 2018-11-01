<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="{{changeUrl(asset('css/login.css'))}}">
    @yield('styles')
    <title>@yield('title','Blog')</title>
</head>
<body>

@yield('content')


<script src="{{changeUrl(asset('js/jquery-2.1.0.js')) }}"></script>
<script src="{{changeUrl(asset('js/jquery.validate.js'))}}"></script>
<script src="{{changeUrl(asset('js/laroute.js'))}}"></script>
<script src="{{changeUrl(asset('js/register.js'))}}"></script>
<script>
    var js_host = "{{ config('layouts.host') }}";
</script>
@yield('scripts')
</body>
</html>