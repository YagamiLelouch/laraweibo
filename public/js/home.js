$(function () {


    //微博输入内容计算字个数
    $('.weibo_text').on('keydown', weibo_num);
    //微博输入内容得到交单计算字个数
    $('.weibo_text').on('focus', function () {
        setTimeout(function () {
            weibo_num();
        }, 50);
    });

    //140字检测
    function weibo_num() {
        //可以输入总数
        var total = 280;
        var len = $('.weibo_text').val().length;
        //输入的数量
        var temp = 0;
        if (len > 0) {
            for (var i = 0; i < len; i++) {
                if ($('.weibo_text').val().charCodeAt(i) > 255) {
                    temp += 2;
                } else {
                    temp ++;
                }
            }
            //还可以输入的数量
            var result = parseInt((total - temp)/2 - 0.5);
            if (result >= 0) {
                $('.weibo_num').html('您还可以输入<strong>' + result + '</strong>个字');
                return true;
            } else {
                $('.weibo_num').html('已经超过了<strong class="red">' + result + '</strong>个字');
                return false;
            }
        }
    }

    // 发布内容
    // $('.weibo_button').button().click(function () {
    //    if($('.weibo_text').val().length==0){
    //        $('#error').html('内容不能为空').dialog('open');
    //        setTimeout(function () {
    //            $('#error').html('...').dialog('close');
    //            $('.weibo_text').focus();
    //        }, 1000);
    //    }else{
    //        if(weibo_num()){
    //             $.ajax({
    //                 headers:{
    //                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //                 },
    //                 url:"http://127.0.0.1:8088/publish",
    //                 dataType:"json",
    //                 type:"post",
    //                 data:{
    //                     "content" : $('.weibo_text').val()
    //                 },
    //                 cache: false,
    //                 // beforeSend : function () {
    //                 //     $('#loading').html('微博发布中...').dialog('open');
    //                 // },
    //                 success:function (data) {
    //                     if(data['meta']['code']==200){
    //                         $('.weibo_text').val('');
    //                     }else{
    //                         alert(data);
    //                     }
    //                 }
    //             })
    //        }
    //    }
    // });

    //显示大图
    $('.img').click(function () {
        $('#main .main_left .lock').css('display','block');
        var bigUrl = $(this).children('img').attr('bigSrc');
        var html = "<img src=\""+bigUrl+"\">";
        var originUrl = $(this).children('img').attr('fileSrc');
        var fileLink = "<div class='originImage'><a href=\'"+originUrl+"\'>查看原图</a></div>"
        $('#main .main_left .showBig .bigImage').append(html);
        $('#main .main_left .showBig').append(fileLink);
        $('#main .main_left .showBig .closeBig')
        $('#main .main_left .showBig').css('display','block');
    })
    $('.closeBig').click(function () {
        $('#main .main_left .showBig').css('display','none');
        $('#main .main_left .lock').css('display','none');
        $('#main .main_left .showBig .bigImage').html('');
        $('#main .main_left .showBig .originImage').remove();

    });

    // function getComment(page, aid ,isThis) {
    //     $.ajax({
    //         type: "post",
    //         url: js_host+laroute.route('getComment'),
    //         dataType: "json",
    //         data: {
    //             page: page,
    //             aid: aid
    //         },
    //         cache: false,
    //         success: function (data) {
    //             var addList = '';
    //             $.each(data, function (k,v) {
    //                 addList += '<div class="comment_list"><div class="comment_head"><a href="">'+v.username+'</a>:'+v.comment_content+'</div><div class="comment_bottom">'+v.ctime+'</div></div>'
    //             });
    //             $(isThis).parent().parent().find('.comment_content').append(addList);
    //         }
    //
    //     })
    // }


    //点击评论自动加载
    //动态显示分页
    // function ajaxPagination(curPNum, allData, limit, showBtnAll) {
    //     var limit = limit||10;
    //     var curPNum = curPNum||1;
    //     // var url = url||'';
    //     var showBtnAll = showBtnAll||10;
    //
    //     //计算获取页面显示数的一半
    //     var show_half = Math.ceil(showBtnNum/2);
    //     //总页数
    //     var allPage = Math.ceil(allData/limit);
    //     var lp=0,bp=0,start=1,end=allPage,length=showBtnAll,currentNum=show_half;
    //
    //     if(allPage>showBtnAll){
    //         //parseInt() 函数可解析一个字符串，并返回一个整数。
    //         //当前页码+(显示页数-显示页数/2)>=总页数
    //
    //         //到了尾页才执行,就是尾页可以全部显示的时候
    //         if((parseInt(curPNum)+parseInt(showBtnAll-show_half))>=allPage){
    //             lp = 0;
    //             bp = 1;
    //             //起始页码=当前页码-显示页数/2+1
    //             start = curPNum-show_half+1;
    //             end = allPage;
    //             //长度=显示页数/2+总页数-当前页码
    //             length = show_half+(allPage-curPNum);
    //             //为限制的一半
    //             currentNum = show_half;
    //
    //
    //             //尾页不能显示的时候
    //         }else{
    //             lp = 1;
    //             //当前页码-显示页码/2>0
    //             if(curPNum-show_half>0){
    //                 bp = 1;
    //                 start = curPNum-show_half+1;
    //             }else{
    //                 bp = 0;
    //                 //为当前页码
    //                 currentNum = curPNum;
    //                 start = 1;
    //             }
    //             end = parseInt(curPNum)+(parseInt(showBtnAll)-parseInt(show_half));
    //             console.log(end);
    //
    //             length = showBtnAll;
    //         }
    //         //总页数小于页面显示页数则全部显示
    //     }
    //     else{
    //         lp = 0;
    //         bp = 0;
    //         start = 1;
    //         end = allPage;
    //         length = allPage;
    //         currentNum = curPNum;
    //     }
    //     var p = [lp, bp, start, end, length, currentNum];
    //     console.log(p);
    //
    //     var html = "<ul>";
    //     var lp = p[0],bp = p[1],s = p[2],e = p[3],l = p[4],c = p[5];
    //     //当前页码>总页码/只有一页
    //     if(curPNum>allPage || allPage == 1){
    //         html+='';
    //     }else{
    //         //存在首页,显示首页
    //         if(bp){
    //             html += '<a href="javacript:;" target="_self" class="btn"><li>1</li></a><li>...</li>';
    //         }
    //         //使用end来尝试
    //         for (s; s<=e; s++) {
    //             if(s==c){
    //                 html += '<a href="javascript:;" target="_self" class="btn"><li class="active ">'+curPNum+'</li></a>';
    //             }else{
    //                 html += '<a href="javascript:;" target="_self" class="btn"><li>'+s+'</li></a>';
    //             }
    //         }
    //         //存在尾页显示尾页
    //         if(lp){
    //             html += '<li>...</li><a href="javascript:;" target="_self" class="btn"><li>'+allPage+'</li></a>';
    //         }
    //     }
    //
    //     $('.ajaxPage').append(html);
    // }

})