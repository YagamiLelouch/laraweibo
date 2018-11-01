@extends('layouts.sign')
@section('title','登录')
@section('content')
    <div id="register">
    <div id="select">
        <div class="selectEmail choose">邮箱注册</div>
        <div class="selectPhone choose">手机注册</div>
    </div>
    <div id="formEmail">
    <form>
        <div class="row">
            <input class="register" type="text" name="username" value="" id="mailUsername" placeholder="用户名（3-20位字母数字）">
            <div class="error dnone"></div>
        </div>
        <div class="row">
            <input type="tel" class="register" name="email" value="" id="email" placeholder="你的常用邮箱">
            <div class="error dnone"></div>
        </div>
        <div class="row">
            <input type="password" class="register" name="password" value="" id="mailPassword" placeholder="密码（不少于6位）">
            <div class="error dnone"></div>
        </div>
        <div><input type="button" id="mailRegBtn" value="注册"></div>
    </form>
    </div>


    <!--手机注册-->
    <div id="formPhone" style="display:none;">
    <form>
        <div class="row">
            <input type="text" name="username" class="register" value="" id="username" placeholder="用户名（3-20位字母数字）">
            <div class="error dnone"></div>
        </div>
        <div class="row">
            <input type="tel" class="register" name="mobilephone" value="" id="mobilephone" placeholder="手机号码">
            <div class="error dnone"></div>
        </div>

        <div class="row">
            <input type="text" class="register" name="codeNum" value="" id="codeNum" placeholder="手机验证码" style="width: 200px;">
            <input type="button" class="smsbtn" value="发送验证码" style="width: 200px;">
            <div class="error dnone"></div>
        </div>
        <div>
            <input type="password"class="register" name="password" value="" id="password" placeholder="密码（不少于6位）">
            <div class="error dnone"></div>
        </div>
        <div>
            <input type="button" id="mobileRegBtn" value="注册">
        </div>
    </form>
    </div>
    </div>
@stop

@section('scripts')
    <script>
        $(function () {
            $('.selectEmail').click(function () {
                $('#formEmail').css('display', 'block');
                $('#formPhone').css('display', 'none');
                $('.error').html('').css('display', 'none')
            });
            $('.selectPhone').click(function () {
                $('#formEmail').css('display', 'none');
                $('#formPhone').css('display', 'block');
                $('.error').html('').css('display', 'none')
            })
        })
    </script>
@stop