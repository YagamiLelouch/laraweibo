@extends('layouts.default')
@section('styles')
    <link rel="stylesheet" href="{{changeUrl(asset('css/index.css'))}}">
@stop
@section('title', '搜索结果')
@section('content')
    <div id="main">
        <div class="main_left">
            <div class="weibo_content">
                @foreach($contents as $content)
                    <dl class="weibo_content_data">
                        <dt><a href="javascript:void(0)"><img src="{{changeUrl(asset('img/small_face.jpg'))}}" alt=""></a></dt>
                        <dd>
                            <h4><a href="javascript:void(0)">{{$content['username']}}</a></h4>
                            <p>{{$content['content']}}</p>
                            <div class="footer">
                                <span class="time">{{$content['ctime']}}</span>
                                <span class="handler">收藏</span>
                            </div>
                        </dd>
                    </dl>
                @endforeach
            </div>

        </div>

        <div class="main_right">
            <div class="from"></div>
            <div class="from"></div>
        </div>
    </div>
@stop

