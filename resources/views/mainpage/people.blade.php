@extends('layouts.default')
@section('styles')
    <link rel="stylesheet" href="{{ changeUrl(asset('css/index.css')) }}">
@stop
@section('title','个人主页')
@section('content')
    <div id="main">

        <div class="main_left">
            <div class="weibo_content">
                @foreach($contents as $content)
                    <dl class="weibo_content_data">
                        <dt><a href="javascript:void(0)"><img src="{{changeUrl(asset('img/small_face.jpg'))}}" alt=""></a></dt>
                        <dd>
                            <h4><a href="javascript:void(0)">{{$username}}</a></h4>
                            <p>{{$content['content']}}</p>
                            <div class="footer">
                                <span class="time">{{$content['ctime']}}</span>
                                <span class="handler">收藏</span>
                            </div>
                        </dd>
                    </dl>
                @endforeach
            </div>

            <div class="page">
                @for($i=1;$i<$pages['pages']+1;$i++)
                    <a href="{{route('people',['username' => json_decode(\Cookie::get('ZLINK_USERINFO'))->username])}}?page={{$i}}" class="{{$pages['page']==$i?'select':''}}">{{$i}}</a>
                @endfor
            </div>

        </div>


        <!--右栏-->
        <div class="main_right">
            <!--头像-->
            <img src="https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png" alt="" class="face">
            <!--username-->
            <span class="user">
                 @if(App\Model\User::isLogin())
                    <a href="{{route('profile')}}">{{ json_decode(\Cookie::get('ZLINK_USERINFO'))->username }}</a>
                @else
                    <a href="javascript:void(0);">未登录</a>
                @endif

		</span>
        </div>

    </div>

@stop
@section('scripts')
    <script src="{{ changeUrl(asset('js/home.js')) }}"></script>
@stop