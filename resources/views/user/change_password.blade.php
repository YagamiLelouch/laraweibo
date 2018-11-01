@extends('layouts.default')

@section('styles')
    <link rel="stylesheet" href="css/setting.css">

@stop

@section('title','修改密码')

@section('content')
    <div id="main">
        @include('layouts._aside')

        <div class="main_right">
            <h2>修改密码</h2>
            <dl class="change">
                <dd>原密码：<input type="password" name="password" value="" class="text" placeholder="请输入原密码"></dd>
                <dd>新密码：<input type="password" name="newPassowrd" value="" class="text" placeholder="请输入新密码"></dd>
                <dd>新密码确认：<input type="password" name="reNewPassword" value="" class="text" placeholder="请再次输入新密码"></dd>
                <dd><input type="button" class="change-password" value="修改密码"></dd>
            </dl>
        </div>
    </div>
@stop

@section('scripts')
    <script src="js/setting.js"></script>
@stop