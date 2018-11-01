$(function () {
    $("body").delegate("#mailRegBtn", "click", function() {
        mailReg();
    });

    $("body").delegate("#mobileRegBtn", "click", function() {
        mobileReg();
    });

    $("body").delegate("#findBtn", "click", function() {
        mobileFind();
    });

    $("body").delegate("#findBtn2", "click", function() {
        mailFind();
    });


    //点击发送手机验证码
    $('.smsbtn').on('click',function(){
        if ($(this).attr('viewname') == 'request') {
            var action = 'reset_password';
        } else {
            var action = 'sign_up'
        }
        $.ajax({
            headers:{
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type:"POST",
            url:"http://127.0.0.1:8088/sendCode",
            dataType:"json",
            data:{
                "phone":$("#mobilephone").val(),
                "action":action,
            },
            cache: false,
            success:function (data) {
                console.log(data);
                //如果正确
                if (data['meta'].code == 200) {
                    layer.msg('验证码已发送');
                    //如果错误
                } else if (data['meta'].code == 401 || data['meta'].code == 400) {
                    //hasOwnProperty() 方法会返回一个布尔值，指示对象自身属性中是否具有指定的属性
                    if (data.hasOwnProperty('data')) {
                        $.each(data['data'], function (k, v) {
                            if (v.indexOf('手机') >= 0) {
                                $('#mobilephone').next().html(v).show();
                            } else {
                                $('#codeNum').nextAll('.error').html(v).show();
                            }
                        });
                    } else {
                        $('#codeNum').nextAll('.error').html(data['meta'].message).show();
                    }
                    return false;
                } else if (data['meta'].code == 500) {
                    $('#codeNum').nextAll('.error').html(data['meta'].message).show();
                }
            }
        })
    });

    //点击注册按钮的邮箱注册
    function mailReg() {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: "POST",
            url:  js_host+laroute.route('signup'),
            dataType: "json",
            data: {
                "method":'email',
                "username": $("#mailUsername").val(),
                "email": $("#email").val(),
                "password": $("#mailPassword").val(),
                // "check": $("#mailChecked").val(),
            },
            cache: false,
            success: function(data) {
                if (data['meta'].code == 401 || data['meta'].code == 400 ) {
                    if (data.hasOwnProperty('meta')) {
                        $.each(data['meta']['message'], function(k, v){
                            if (v.indexOf('邮箱') >= 0) {
                                $('input[type=tel]').next().html(v).css('display', 'block');
                            } else if (v.indexOf('用户名') >= 0) {
                                $('input[name=username]').next().html(v).css('display', 'block');
                            } else if (v.indexOf('密码') >= 0) {
                                $('input[name=password]').next().html(v).css('display', 'block');
                            }
                        });
                    }
                    return false;
                }
                // if(data['meta']['code']==400){
                //     $('.error').html('');
                //     var html = '<span>'+data['meta']['message']+'</span>'
                //     $('.error').eq(0).append(html);
                // }
                if (data['meta']['code']==200) {
                    window.location.href="http://127.0.0.1:8088";
                }
            }
        })
    }

    //点击注册是手机的注册
    function mobileReg() {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: "POST",
            url: "http://127.0.0.1:8088/signup",
            dataType: "json",
            data: {
                "method":'phone',
                "username": $("#username").val(),
                "phone": $("#mobilephone").val(),
                "password": $("#password").val(),
                // "check": $("#checked").val(),
                "code": $("#codeNum").val(),
            },
            cache: false,
            success: function(data) {
                if(data['meta']['code']==400){
                    $('.error').html('');
                    var html = '<span>'+data['meta']['message']+'</span>'
                    $('.error').eq(3).append(html);
                } else if (data['meta']['code']==200) {
                    alert('手机注册成功');
                    window.location.href="http://127.0.0.1:8088";
                }
            }
        })
    }

    //点击通过手机号重置
    function mobileFind() {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: "POST",
            url: "http://127.0.0.1:8088/reset/phone",
            dataType: "json",
            data: {
                "phone": $("#mobilephone").val(),
                "code": $("#codeNum").val(),
            },
            cache: false,
            success: function(data) {
                if (data['meta'].code == 401 || data['meta'].code == 400 ) {
                    var html = '<span>'+data['meta']['message']+'</span>';
                    $('.error').eq(0).append(html);
                }
                if (data['meta'].code == 200) {
                    window.location.href=laroute.route('password.reset', { code : data['data'].code });
                }
            }
        })
    }
    //点击发送重置邮件
    function mailFind() {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: "POST",
            url: "http://127.0.0.1:8088/reset/email",
            dataType: "json",
            data: {
                "email": $('#mail').val(),
                "action":"reset_password"
            },
            cache: false,
            success: function(data) {
                if (data['meta'].code == 401 || data['meta'].code == 400 ) {
                    var html = '<span>'+data['meta']['message']+'</span>';
                    $('.error').eq(2).append(html);
                }
                if (data['meta'].code == 200) {
                        alert('发送成功');
                        window.location.href=laroute.route('home');
                }
            }
        })
    }

});