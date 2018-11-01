$(function () {
    $("body").delegate("#login_btn", "click", function() {
        login();
    });

    function login(){
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "POST",
                url: "http://127.0.0.1:8088/login",
                dataType: "json",
                data: {
                    "account": $("#username").val(),
                    "password": $("#passWd").val(),
                },
                cache: false,
                success: function(data) {
                    if (data['meta'].code == 400) {
                       var html = '<span>'+data['meta'].message+'</span>'
                        $('#usernameMsg').append(html);
                    } else if(data['meta'].code == 200){
                        window.parent.location.href='http://127.0.0.1:8088';
                    } else {
                        var html = '<span>未知错误</span>'
                        $('#usernameMsg').append(html);
                    }

                    // window.parent.location.href='http://127.0.0.1:8088'
                    // if ($("#alert").val() == 1) {
                    //     window.parent.location.href=$("#referer").val();
                    // } else {
                    //     window.location.href=$("#referer").val();
                    // }
                }
            })
    }
});