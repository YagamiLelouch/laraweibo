<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{changeUrl('css/admin.css')}}">
    <title>Document</title>
</head>
<body>

<div tb="1" id="table1" class="content" style="margin:30px;">
    <table border="1">
        <thead>
        <tr>
            @foreach($fieldArticle as $article)
            <th>{{$article['c_field']}}</th>
                @endforeach
        </tr>
        </thead>
        <tbody class="addTb" >
        </tbody>
    </table>
    <div class="page">

    </div>
</div>

<div tb="2" id="table2" class="content" style="margin:30px;">
    <table border="1">
        <thead>
        <tr>
            @foreach($fieldUser as $user)
                <th>{{$user['c_field']}}</th>
            @endforeach
        </tr>
        </thead>
        <tbody class="addTb" >
        </tbody>
    </table>
    <div class="page">

    </div>
</div>


<script src="{{ changeUrl(asset('js/jquery-2.1.0.js')) }}"></script>
<script>
    $(function () {
        var a=$('.addTb');
        getInfo(1,5,a,1);
        //获取分页
        //页码/总条数/url/每页显示限制/界面显示的页面按钮个数
        getPages('{{ $pages['page'] }}', '{{ $pages['count'] }}', '{{ $pages['url'] }}', '{{ $pages['limit'] }}', '{{ $pages['num'] }}',1);
        getInfo(1,2,a,2);
        //获取分页
        getPages('{{ $pagesU['page'] }}', '{{ $pagesU['count'] }}', '{{ $pagesU['url'] }}', '{{ $pagesU['limit'] }}', '{{ $pagesU['num'] }}',2);

        $('.content').on('click', '.btn', function () {
            var page = $(this).text();
            var table = $(this).parent().parent().parent().find('.addTb');
            var tb = $(this).parent().parent().parent().attr('tb');
            getInfo(page, 5, table, tb);
            if(tb == '1'){
                getPages(page, '{{ $pages['count'] }}', '{{ $pages['url'] }}', '{{ $pages['limit'] }}', '{{ $pages['num'] }}',tb);
            }else if(tb == '2'){
                getPages(page, '{{ $pagesU['count'] }}', '{{ $pagesU['url'] }}', '{{ $pagesU['limit'] }}', '{{ $pagesU['num'] }}',tb);
            }

        })
        console.log(userInfo);
        console.log(fieldUser);


    });
    function getInfo(page, limit, table, tb) {
        if(tb == '1'){
            $.ajax({
                type: "post",
                url: "{{changeUrl(route('info'))}}",
                dataType: "json",
                data: {
                    page: page,
                    limit: limit,
                    tb: tb
                },
                success: function (data) {
                    var addTb = '';

                    $.each(data, function (k,v) {

                        addTb +='<tr>'
                        $.each(fieldArticle, function (i,n) {
                            addTb += '<td>'+v[n['e_field']]+'</td>'
                        });
                        addTb += '</tr>'
                        // $.each()
                        // addTb += '<tr><td>'+data[k].user.username+'</td><td>'+data[k].content+'</td><td>'+data[k].collection_count+'</td><td>'+data[k].ctime+'</td></tr>'
                    });
                    $('#table1 .addTb').html(addTb);
                }
            });
        } else if(tb == '2') {
            $.ajax({
                type: "post",
                url: "{{changeUrl(route('userAll'))}}",
                dataType: "json",
                data: {
                    page: page,
                    limit: limit,
                    tb: tb
                },
                success: function (data) {
                    console.log(data);
                    var addTb = '';

                    $.each(data, function (k,v) {

                        addTb +='<tr>'
                        $.each(fieldUser, function (i,n) {
                            addTb += '<td>'+v[n['e_field']]+'</td>'
                        });
                        addTb += '</tr>'
                        // $.each()
                        // addTb += '<tr><td>'+data[k].user.username+'</td><td>'+data[k].content+'</td><td>'+data[k].collection_count+'</td><td>'+data[k].ctime+'</td></tr>'
                    });
                    $('#table2 .addTb').html(addTb);
                }
            });
        } else {
            alert('操作错误');
        }

    }

    //当前页码.总条数.url.limit.页面显示页数
    function getPages(curPNum, allData, url, limit, showBtnNum,tb){


        var limit = limit||10;
        var curPNum = curPNum||1;
        var url = url||'';
        var showBtnAll = showBtnNum||10;

        //计算获取页面显示数的一半
        var show_half = Math.ceil(showBtnNum/2);
        //总页数
        var allPage = Math.ceil(allData/limit);
        //length为页面显示页数,currentNum为当前显示页数的一半,end\allPage为总页数
        //bp>0将显示"首页"按钮,lp>0将显示"尾页"按钮
        //lp为尾页标志,bp为首页标志
        var lp=0,bp=0,start=1,end=allPage,length=showBtnNum,currentNum=show_half;
        //总页数>页面显示页数
        if(allPage>showBtnNum){
            //parseInt() 函数可解析一个字符串，并返回一个整数。
            //当前页码+(显示页数-显示页数/2)>=总页数

            //到了尾页才执行,就是尾页可以全部显示的时候
            if((parseInt(curPNum)+parseInt(showBtnNum-show_half))>=allPage){
                lp = 0;
                bp = 1;
                //起始页码=当前页码-显示页数/2+1
                start = curPNum-show_half+1;
                end = allPage;
                //长度=显示页数/2+总页数-当前页码
                // console.log(show_half+'</br>');
                // console.log(allPage+'</br>');
                // console.log(curPNum+'</br>');
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
        // else{
        //     lp = 0;
        //     bp = 0;
        //     start = 1;
        //     end = allPage;
        //     length = allPage;
        //     currentNum = curPNum;
        // }
        var p = [lp, bp, start, end, length, currentNum];
        console.log(p);

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
            // //l为最大显示的页码数,s为其起始页码
            // for(var i=0; i<l; i++){
            //     // console.log('is'+l);
            //     // console.log(i);
            //     if(i==c-1){
            //         html += '<a href="javascript:;" target="_self" class="btn"><li class="active ">'+curPNum+'</li></a>';
            //     }else{
            //         html += '<a href="javascript:;" target="_self" class="btn"><li>'+(s+i)+'</li></a>';
            //     }
            // }

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

        html += "</ul>";
        if(tb == '1'){
            $("#table1 .page").html(html);
        }else if(tb == '2'){
            $('#table2 .page').html(html);
        }

    }


</script>



</body>
</html>