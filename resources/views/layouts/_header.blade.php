<div id="header">
    <!--包括可见区域的所有-->
    <div class="header_main">
        <div class="logo">Blog</div>
        <!--导航-->
        <nav class="nav">
            <ul>
                <li class="{{ active_class(if_route('home')) }}"><a href="{{changeUrl(route('home'))}}">首页</a></li>
                @if(App\Model\User::isLogin())
                <li class="{{ active_class(if_route('people')) }}"><a href="{{changeUrl(route('people',['username' => json_decode(\Cookie::get('ZLINK_USERINFO'))->username]))}}">个人中心</a></li>
                <!--<li><a href="#">图片</a></li>
                <li><a href="#">找人</a></li>-->
                    @endif

            </ul>
        </nav>

        <div class="person">
            <ul>
                @if(App\Model\User::isLogin())

                <li class="user">
                    <a href="{{route('profile')}}">{{ json_decode(\Cookie::get('ZLINK_USERINFO'))->username }}</a>
                </li>
                    <li class="user">
                        <a href="{{route('logout')}}">退出</a>
                    </li>
                @else
                    <li class="user">
                        <a href="{{route('login')}}">登录</a>
                    </li>
                    <li class="user">
                        <a href="{{route('signup')}}">注册</a>
                    </li>
                @endif
                <!--消息栏-->
                <!--<li class="app">消息
                    <!--下拉-->
                    <!--<dl class="list">
                        <!--提醒-->
                    <!-- <dd><a href="">@提到我
                         </a></dd>
                     <dd><a href="#">收到的评论</a></dd>
                     <dd><a href="#">发出的评论</a></dd>
                     <dd><a href="#">我的私信</a></dd>
                     <dd><a href="#">系统消息</a></dd>
                     <dd><a href="#" class="line">发私信»</a></dd>
                 </dl>
             </li>
             <!--账号管理栏-->
                    <!--<li class="app">帐号
                        <!--下拉-->
                <!--<dl class="list">
                        <dd><a href="">个人设置</a></dd>
                        <dd><a href="#">排行榜</a></dd>
                        <dd><a href="#">申请认证</a></dd>
                        <dd><a href="{{route('logout')}}" class="line">退出»</a></dd>
                    </dl>
                </li>-->

            </ul>
        </div>
        <!--搜索-->
        <div class="search">
            <form method="get" action="{{changeUrl(route('search'))}}">
                <input type="text" id="search" name="q" placeholder="请输入微博关键字">
                <a href="javascript:void(0)"></a>
            </form>
        </div>
    </div>
</div>