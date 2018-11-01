<div class="main_left">
    <ul>
        <li class="{{ active_class(if_route('profile')) }}"><a href="{{route('profile')}}">个人资料</a></li>
        <li class="{{ active_class(if_route('password')) }}"><a href="{{route('password')}}">修改密码</a></li>
        <li class="{{ active_class(if_route('collection')) }}"><a href="{{route('collection')}}">我的收藏</a></li>
        <!--<li><a href="{:U('Setting/refer')}">@提及到我</a></li>
        <li><a href="{:U('Setting/approve')}" >申请认证</a></li>-->
    </ul>
</div>