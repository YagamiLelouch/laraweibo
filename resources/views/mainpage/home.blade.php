@extends('layouts.default')
@section('styles')
    <link rel="stylesheet" href="{{changeUrl(asset('css/index.css'))}}">
    <link rel="stylesheet" href="{{changeUrl(asset('css/tinyImgUpload.css'))}}">
    <link rel="stylesheet" href="{{changeUrl(asset('css/rl_exp.css'))}}">
    @stop
@section('title','主页')
@section('content')
    <div id="main">
        <div class="main_left">
            <!--发文输入框,表情,图片-->
            <div class="weibo_form">
                <span class="left">和大家分享一点新鲜事吧？</span>
                <span class="right weibo_num">可以输入<strong>140</strong>个字</span>
                <textarea class="weibo_text" id="rl_exp_input"></textarea>
                <!--表情-->
                   <a href="javascript:void(0);" class="weibo_face" id="rl_exp_btn">表情<span class="face_arrow_top"></span></a>
                <div class="rl_exp" id="rl_bq" style="display:none;">
                    <ul class="rl_exp_tab clearfix">
                        <li><a href="javascript:void(0);" class="selected">默认</a></li>
                        <li><a href="javascript:void(0);">拜年</a></li>
                        <li><a href="javascript:void(0);">浪小花</a></li>
                        <li><a href="javascript:void(0);">暴走漫画</a></li>
                    </ul>
                    <ul class="rl_exp_main clearfix rl_selected"></ul>
                    <ul class="rl_exp_main clearfix" style="display:none;"></ul>
                    <ul class="rl_exp_main clearfix" style="display:none;"></ul>
                    <ul class="rl_exp_main clearfix" style="display:none;"></ul>
                    <a href="javascript:void(0);" class="close">×</a>
                </div>

                <!--新图片上传-->
                <div id="upload" style="display: inline-block;">

                </div>
                <!--<button class="submit">submit</button>-->

                <input class="weibo_button submit" type="button" value="发布">
            </div>
            <div class="weibo_content">
                <!--<ul>
                    <li><a href="javascript:void(0)" class="selected">我关注的<i class="nav_arrow"></i></a></li>
                    <li><a href="javascript:void(0)">互听的</a></li>
                </ul>-->

                @foreach($data as $v)
                <div class="weibo_content_data">
                    <div class="card-head"><a href="{{route('people',['name' => $v['user']['username']])}}"><img src="{{changeUrl(asset('img/small_face.jpg'))}}" alt=""></a></div>
                    <div class="card-body">
                        <h4><a href="{{route('profile')}}">{{$v['user']['username']}}</a></h4>
                        <p>{!! $v['content'] !!}</p>
                        @if(!empty($v['images']))
                            @foreach($v['images'] as $image)
                            <div class="img"><img src="{{changeUrl(asset($image['smallImage']))}}" bigSrc="{{changeUrl(asset($image['bigImage']))}}" fileSrc="{{changeUrl(asset($image['image']))}}" alt=""></div>
                            @endforeach
                        @endif
                        <div class="footer">
                            <span class="time">{{$v['ctime']}}</span>
                            <span class="handler">
                                <a href="javascrip:;" class="comment" aid="{{$v['article_id']}}"  isopen="0" comCnt="{{count($v['comment'])}}">评论({{count($v['comment'])}})</a></span>
                                @if(isset($collections[$v['article_id']]))
                                <a href="javascrip:;" class="collection" aid="{{$v['article_id']}}" num="{{$v['collection_count']}}">已收藏({{$v['collection_count']}})</a></span>
                            @else
                                <a href="javascrip:;" class="collection active" aid="{{$v['article_id']}}" num="{{$v['collection_count']}}">收藏({{$v['collection_count']}})</a></span>
                                @endif
                            <div class="comment_frame" style="display: none;">
                                <p>表情、字数限制自行完成</p>
                                <textarea class="comment_text" name="commend"></textarea>
                                <input type="hidden" name="tid" value="{{$v['article_id']}}" />
                                <input class="com_button" type="button" value="评论">
                                <!--动态评论-->
                                <div class="comment_content">
                                    <!--动态分页-->
                                    <div class="ajaxPage" aid="{{$v['article_id']}}"></div>
                                </div>

                            </div>
                        </div>



                    </div>
                </div>
                    @endforeach
            </div>
            <div class="page">
                    @for($i=1;$i<$pages['pages']+1;$i++)
                    <a href="{{route('home', [ 'page' => $i])}}"class="{{$pages['page']==$i?'select':''}}">{{$i}}</a>
                @endfor
            </div>
            <div class="lock"></div>
            <div class="showBig">
                <div class="closeBig"><a href="javscript:;">关闭</a></div>
                <div class="bigImage"></div>
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

    <script src="{{changeUrl(asset('js/home.js'))}}"></script>
    <script src="{{changeUrl(asset('js/tinyImgUpload.js'))}}"></script>
    <script src="{{changeUrl(asset('js/rl_exp.js'))}}"></script>
    <script>
$(function () {
    //点击出现评论
    $('.comment').on('click', function () {
        console.log($(this).attr('isopen'));
        if ($(this).attr('isopen') == 0) {
            $(this).attr('isopen', '1');
            var comCnt = $(this).attr('comCnt');
            var page = $('.ajaxPage .active').html();
            var aid = $(this).attr('aid');
            $(this).parent().parent().find('.comment_frame').css('display', 'block');
            var isThis = this;
            getComment(1, aid, isThis);
            //点击评论自动加载分页
            ajaxPagination(page, comCnt, 5, 5, isThis);
        } else if ($(this).attr('isopen') == 1) {
            $(this).attr('isopen', '0');
            $(this).find('.comment_list').html('');
            $(this).parent().parent().find('.comment_frame').css('display', 'none');
        }
    });

    //写入评论
    $('.com_button').on('click', function () {
        var uid = 2;
        var aid = $(this).parent().find("input[name='tid']").val();
        var content = $(this).parent().find(".comment_text").val();
        var _this = $(this);
        $.ajax({
            type: "post",
            url: js_host+laroute.route('pComment'),
            dataType: "json",
            data: {
                uid: uid,
                aid: aid,
                content: content
            },
            cache: false,
            success: function (data) {
                $(_this).parent().find(".comment_text").html('');
            }
        })
    });



// //点击分页按钮分页
//     $('.ajaxPage .btn li').on('click', function () {
//         alert(1);
//         var page = $(this).val();
//         var isThis = $(this).parent().parent().parent().parent().parent();
//         getComment(page, aid, isThis);
//     });
//
//     $('.ajaxPage ul').click(function () {
//         alert(1);
//     })
//     $('.ajaxPage').find('li').on('click', function () {
//         alert(1);
//     });

    $('body').on('click', '.ajaxPage .btn li', function () {
        var page = $(this).html();
        var isThis = $(this).parent().parent().parent();

        var aid = $(this).parent().parent().parent().attr('aid');
        // var aid = isThis.attr('aid');
        getComment(page, aid, isThis);
    })




    //分页数据函数
    function getComment(page, aid ,isThis) {
        $(isThis).parent().parent().find('.comment_list').html('');
        $(isThis).parent().parent().find('.ajaxPage').html('');
        var page = page||1;
        $.ajax({
            type: "post",
            url: js_host+laroute.route('getComment'),
            dataType: "json",
            data: {
                page: page,
                aid: aid
            },
            cache: false,
            success: function (data) {
                var addList = '';
                $.each(data, function (k,v) {
                    addList += '<div class="comment_list"><div class="comment_head"><a href="">'+v.username+'</a>:'+v.comment_content+'</div><div class="comment_bottom">'+v.ctime+'</div></div>'
                });
                $(isThis).parent().parent().find('.comment_content').append(addList);
    }

        })
    }


    //点击评论自动加载
    //动态显示分页
    function ajaxPagination(curPNum, allData, limit, showBtnAll, isThis) {
        var limit = limit||10;
        var curPNum = curPNum||1;
        // var url = url||'';
        var showBtnAll = showBtnAll||10;

        //计算获取页面显示数的一半
        var show_half = Math.ceil(showBtnAll/2);
        //总页数
        var allPage = Math.ceil(allData/limit);
        var lp=0,bp=0,start=1,end=allPage,length=showBtnAll,currentNum=show_half;
        console.log('alllPage'+allPage+',showBtnAll'+showBtnAll);

        if(allPage>showBtnAll){

            //parseInt() 函数可解析一个字符串，并返回一个整数。
            //当前页码+(显示页数-显示页数/2)>=总页数

            //到了尾页才执行,就是尾页可以全部显示的时候
            if((parseInt(curPNum)+parseInt(showBtnAll-show_half))>=allPage){
                lp = 0;
                bp = 1;
                //起始页码=当前页码-显示页数/2+1
                start = curPNum-show_half+1;
                end = allPage;
                //长度=显示页数/2+总页数-当前页码
                length = show_half+(allPage-curPNum);
                //为限制的一半
                currentNum = show_half;


                //尾页不能显示的时候
            }else{
                lp = 1;
                //当前页码-显示页码/2>0
                if(curPNum-show_half>0){
                    bp = 1;
                    start = curPNum-show_half+1;
                }else{
                    bp = 0;
                    //为当前页码
                    currentNum = curPNum;
                    start = 1;
                }
                end = parseInt(curPNum)+(parseInt(showBtnAll)-parseInt(show_half));
                console.log(end);

                length = showBtnAll;
            }
            //总页数小于页面显示页数则全部显示
        }
        else{
            lp = 0;
            bp = 0;
            start = 1;
            end = allPage;
            length = allPage;
            currentNum = curPNum;
        }
        var p = [lp, bp, start, end, length, currentNum];

        var html = "<ul>";
        var lp = p[0],bp = p[1],s = p[2],e = p[3],l = p[4],c = p[5];
        //当前页码>总页码/只有一页
        if(curPNum>allPage || allPage == 1){
            html+='';
        }else{
            //存在首页,显示首页
            if(bp){
                html += '<a href="javacript:;" target="_self" class="btn"><li>1</li></a><li>...</li>';
            }
            //使用end来尝试
            for (s; s<=e; s++) {
                if(s==c){
                    html += '<a href="javascript:;" target="_self" class="btn"><li class="active ">'+curPNum+'</li></a>';
                }else{
                    html += '<a href="javascript:;" target="_self" class="btn"><li>'+s+'</li></a>';
                }
            }
            //存在尾页显示尾页
            if(lp){
                html += '<li>...</li><a href="javascript:;" target="_self" class="btn"><li>'+allPage+'</li></a>';
            }
        }

        $(isThis).parent().parent().find('.ajaxPage').append(html);

    }

})




        // $('.weibo_button').click(function () {
        //     // var value = $('#rl_exp_input').val();
        //     // console.log(value);
        //     alert();
        // })

        $('.collection').on('click',function () {
            var self=$(this);
            var aid=self.attr('aid');
            var num=self.attr('num');
            if(self.hasClass('active')){
                $.ajax({
                    headers:{
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url:'http://127.0.0.1:8088/addcollection?article_id='+aid,
                    dataType:"json",
                    type:"post",
                    data: {_method : 'PUT'},
                    cache: false,
                    async : false,
                    success:function (data) {
                        self.text('已收藏('+(++num)+')');
                        self.removeClass('active');
                    }
                })
            }else{
                $.ajax({
                    headers:{
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url:'http://127.0.0.1:8088/deletecollection?article_id='+aid,
                    dataType:"json",
                    type:"post",
                    data: {_method : 'DELETE'},
                    cache: false,
                    async : false,
                    success:function (data) {
                        self.text('收藏('+(--num)+')');
                        self.addClass('active')
                    }
                })
            }

        })

        // document.documentElement.style.fontSize = document.documentElement.clientWidth*0.1+'px';

        var options = {
            path: '{{changeUrl(route('upload'))}}',
            onSuccess: function (res) {
                console.log(res);
            },
            onFailure: function (res) {
                console.log(res);
            }
        }

        var upload = tinyImgUpload('#upload', options);
        document.getElementsByClassName('submit')[0].onclick = function (e) {
            upload();
        }


    </script>
    @stop