@extends('layouts.sign')
@section('title','忘记密码')
@section('content')
<!--手机重置-->
<div id="forget">
    <div id="select">
        <div class="selectEmail choose">邮箱重置</div>
        <div class="selectPhone choose">手机重置</div>
    </div>
    <form name="registerForm" id="mobileForm" class="regForm" autocomplete="off" onsubmit="return false;" style="display:none;">
        <div>
        <input type="tel" class="regInput" name="mobilephone" value="" id="mobilephone" placeholder="手机号码" autocomplete="off">
            <div class="error dnone"></div>
        </div>
        <div>
            <input type="text" class="regInput" name="codeNum" value="" id="codeNum" placeholder="手机验证码" autocomplete="off" style="width: 200px">
            <input type="button" class="smsbtn" viewname="request" value="发送验证码" style="width: 200px">
            <div class="error dnone"></div>
        </div>
        <div>
            <input type="button" class="regBtn" id="findBtn" value="找回密码">
        </div>
    </form>


<!--邮箱重置-->
    <form name="registerForm" id="mailForm" class="regForm" autocomplete="off" onsubmit="return false;">
       <div>
           <input type="tel" class="regInput" name="email" value="" id="mail" placeholder="你的常用邮箱" autocomplete="off" tabindex="2">
           <div class="error dnone"></div>
       </div>

        <div>
            <input type="button" class="regBtn mt-10" id="findBtn2" value="找回密码">
            <div class="error dnone"></div>
        </div>

    </form>
</div>
    @stop

@section('scripts')
    <script>
        $(function () {
            $('.selectEmail').click(function () {
                $('#mailForm').css('display', 'block');
                $('#mobileForm').css('display', 'none');
                $('.error').html('').css('display', 'none')
            });
            $('.selectPhone').click(function () {
                $('#mailForm').css('display', 'none');
                $('#mobileForm').css('display', 'block');
                $('.error').html('').css('display', 'none')
            })
        })
    </script>
    @stop