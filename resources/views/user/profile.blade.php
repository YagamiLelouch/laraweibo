@extends('layouts.default')

@section('styles')
    <link rel="stylesheet" href="css/setting.css">

@stop

@section('title','个人资料')

@section('content')
    <div id="main">
    @include('layouts._aside')

    <div class="main_right">
        <h2>个人资料</h2>
        <dl class="profile">
            <dd>用户名：{{$info[0]['username']}}</dd>
            <dd>邮 箱：{{$info[0]['email']}}</dd>
            <dd>手机号: {{$info[0]['phone']}}</dd>
            <dd>注册日期: {{$info[0]['ctime']}}</dd>
            <dd><input type="button" class="button" value="修改资料"></dd>
        </dl>
        <dl class="edit" style="display: none;">
            <dd>用户名：<input type="text" name="username" value="{{$info[0]['username']}}" class="text"></dd>
            <dd>邮 箱：<input type="text" name="email" value="{{$info[0]['email']}}" class="text"></dd>
            <dd>手机号：<input type="text" name="phone" value="{{$info[0]['phone']}}" class="text"></dd>
            <dd><input type="submit" class="submit" value="提交修改"></dd>
        </dl>
    </div>
    </div>
    @stop

@section('scripts')
    <script src="js/setting.js"></script>
    @stop