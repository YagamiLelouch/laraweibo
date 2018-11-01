@extends('layouts.sign')
@section('title','登录')
@section('content')
<!--登录-->
 <div class="login">
     <form id="login">
         <div class="top">
             <input id="username" value="" class="input-text input" type="text" placeholder="用户名/手机号/邮箱" name="account" autocomplete="off" />
             <div id="usernameMsg" class="error dnone"></div>
         </div>
         <div>
             <input id="passWd" name="password" type="password" placeholder="请输入密码" class="input-pwd input">
             <div id="pwdMsg" class="error dnone"></div>
         </div>
         <div class="button">
             <input type="button" id="login_btn" value="登录">
         </div>

         <div class="bottom">
             <a href="{{changeUrl(route('signup'))}}" id="reg_link">注册新用户</a>
             <a href="{{changeUrl(route('forget'))}}">忘记密码？</a>
         </div>
     </form>
 </div>
@stop

@section('scripts')
    <script src="{{changeUrl(asset('js/login.js'))}}"></script>
    @stop
