@extends('layouts.sign')
@section('title','忘记密码')
@section('content')
    <div id="reset">
    <form name="registerForm" id="mobileForm" class="regForm" autocomplete="off">
        <div class="reg_desc">
            <div class="reg_desc_big mobile">重置密码</div>
        </div>
        <div class="reg_input">
            <div class="reg_input_para">
                <div class="reg_border"></div>
                <input type="password" class="regInput" name="password" value="" id="resetPwd" placeholder="新密码（不少于6位）" autocomplete="off">
                <div class="error dnone "></div>
            </div>
            <div class="reg_input_para">
                <div class="reg_border"></div>
                <input type="password" class="regInput" name="password" value="" id="resetPwd2" placeholder="再次输入密码" autocomplete="off" >
                <div class="error dnone"></div>
            </div>
            <div class="reg_input_para">
                <input type="button" class="regBtn" id="restPwdBtn" value="重置密码">
            </div>
        </div>
    </form>
    </div>
@stop

@section('scripts')
    <script>
        $(function () {
            $("body").delegate("#restPwdBtn", "click", function() {
                resetPassword();
            });

            //ajax进行密码修改
            function resetPassword() {
               $.ajax({
                   headers: {
                       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                   },
                   type: "POST",
                   url:"http://127.0.0.1:8088/password/reset",
                   dataType: "json",
                   data: {
                       "password": $('#resetPwd').val(),
                       "password_confirmation":$('#resetPwd2').val(),
                       "code": "{{$code}}"
                   },
                   cache: false,
                   success:function (data) {
                       if(data){
                          window.location=laroute.route('home');
                       }
                   }
               })
            }
        })
    </script>
    @stop

