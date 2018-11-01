$(function () {
    //点击修改资料按钮,显示修改资料框,隐藏原来资料
    $('.button').button().click(function () {
        $(".profile").css('display','none');
        $(".edit").css('display','');
    });
        //提交资料
            $('.submit').button().click(function () {
                $.ajax({
                    headers:{
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url:"http://127.0.0.1:8088/edit",
                    dataType:"json",
                    type:"post",
                    data:{
                        "username" : $('input[name=username]').val(),
                        "email":$('input[name=email]').val(),
                        "phone":$('input[name=phone]').val()
                    },
                    cache: false,
                    // beforeSend : function () {
                    //     $('#loading').html('微博发布中...').dialog('open');
                    // },
                    success:function (data) {
                        if(data['meta']['code']==200){
                            location.reload();
                        }else{
                            alert(data);
                        }
                    }
                })
    });

            $('.change-password').button().click(function () {

                $.ajax({
                    headers:{
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url:"http://127.0.0.1:8088/change_password",
                    dataType:"json",
                    type:"post",
                    data:{
                        "password":$('input[name=password]').val(),
                        "newpassword":$('input[name=newPassowrd]').val()
                    },
                    cache: false,
                    // beforeSend : function () {
                    //     $('#loading').html('微博发布中...').dialog('open');
                    // },
                    success:function (data) {
                        if(data['meta']['code']==200){
                            // location.reload();
                        }else{
                            alert(data['meta']['message']);
                        }
                    }
                })
            })



});