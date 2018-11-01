@extends('layouts.default')

@section('styles')
    <link rel="stylesheet" href="css/setting.css">

@stop

@section('title','我的收藏')

@section('content')
    <div id="main">
        @include('layouts._aside')
        <div class="main_right">
            <h2>我的收藏</h2>
            @foreach($contents as $content)
            <dl class="weibo_content_data">
                <dt><a href="javascript:void(0)"><img src="img/small_face.jpg" alt=""></a></dt>
                <dd>
                    <h4><a href="javascript:void(0)">{{$content['user']['username']}}</a></h4>
                    <p>{{$content['content']}}</p>
                    <div class="footer">
                        <span class="time">{{$content['ctime']}}</span>
                        <span class="handler">@if(isset($collections[$content['article_id']]))
                                <a href="javascrip:;" class="collection" aid="{{$content['article_id']}}" num="{{$content['collection_count']}}">已收藏({{$content['collection_count']}})</a></span>
                        @else
                            <a href="javascrip:;" class="collection active" aid="{{$content['article_id']}}" num="{{$content['collection_count']}}">收藏({{$content['collection_count']}})</a></span>
                            @endif</span>
                    </div>
                </dd>
            </dl>
        @endforeach
        </div>
    </div>
@stop

@section('scripts')
    <script src="js/setting.js"></script>
@stop