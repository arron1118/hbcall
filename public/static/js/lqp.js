(function ($, window, undefined) {
    // 动画效果
    new WOW().init();

    let host = window.location.host;
    let prefix = '';
    if (host === 'localhost' || '192.168.0.200') {
        // prefix = '/hbcalling';
    }
    // console.log(window.location);

    let navInit = function () {
        // 配置文件
        let menuUrl = prefix + '/static/api/portalMenu.json';

        let url = window.location.href;//获取浏览器地址
        let res = url.lastIndexOf("\/");
        let str = url.substring(res + 1, url.length);//从后面截取最后一个/后面的内容
        let pageName = str.substring(0, str.indexOf('.'));//去掉后缀，预防不同的后缀匹配不到（html,xhtml）
        // console.log('今天',pageName)
        $.getJSON(menuUrl, function (res) {
            // 头部导航
            $.each(res.menuList, function (index, item) {
                let aClass = res["menuClass"] + ' ' + item["class"];
                if (pageName === item.name) {//用浏览器地址和自定义绑定的数据data-url或许自己命名：name。两个对比
                    aClass += " nav-active ";
                }

                let li = $('<li />', {
                    class: ' nav-item mx-3 lqp-hoverline text-nowrap'
                }).append($('<a />', {
                    class: aClass,
                    href: item.href,
                }).text(item.title));
                $('.lqp-inner-ul').append(li);
            });
            //登录
            $.each(res.loginList, function (index2, item2) {
                let alClass = " btn btn-shadow btn-sm me-2 px-4 " + item2["class"];
                let a = $('<a />', {
                    class: alClass,
                    href: item2.href,
                }).text(item2.title);
                $('.navbar-toolbar').append(a);
            });

            //底部
            //隐藏客户模块
            let pages = [
                'solution',
                'about',
                'cooperate',
                'news',
                'buy',
                'detailIndex',
                'case'
            ];
            //如果pageName 在pages数组里面
            if (pages.includes(pageName)) {
                $('.lqp-custorm').addClass('d-none');
            }

            //隐藏试用
            let pages2 = [
                'buy',
                'detailIndex'
            ];

            if (pages2.includes(pageName)) {
                $('.lqp-user').addClass('d-none');
            }
            // 底部导航
            $.each(res.bottomNav, function (index1, item1) {
                let bottomNav = $('<div />', {
                    class: "col-6"
                }).append($('<h6 />', {
                    class: "text-white mb-3"
                }).text(item1.title)).append($('<ul />', {
                    class: "nav nav-sm nav-x-0  flex-column"
                }));
                $.each(res.menuList, function (index, item) {
                    if (item.showInBottom && item.bottomId === item1.id) {
                        bottomNav.children('ul').append($('<li />', {class: "mb-3"}).append($('<a />', {
                            class: "text-secondary l-bottom",
                            href: item.href
                        }).text(item.title)));
                    }
                });
                $('.lqp-bottom-nav').append(bottomNav);
            });

            //
            $('.l-bottom').hover(
                function () {
                    $(this).removeClass('text-secondary').addClass('text-primary');
                },
                function () {
                    $(this).removeClass('text-primary').addClass('text-secondary');
                }
            );
        });

    };
    // 初始化导航
    navInit();

    //经过卡片给shadow
    $('.shadowitems').hover(
        function () {
            $(this).addClass('shadow-lg');
        },
        function () {
            $(this).removeClass('shadow-lg');
        },
    );
    //轮播图
    $('.lqp-carousel').hover(
        function () {
            $('.carousel-control-prev').css({left: 0})
            $('.carousel-control-next').css({right: 0})
        }
        , function () {
            $('.carousel-control-prev').css({left: "-15%"})
            $('.carousel-control-next').css({right: "-15%"})
        }
    );
    //监听滚动条位置
    let scrollBar = function () {
        let scroll_top = 0;
        if (document.documentElement && document.documentElement.scrollTop) {
            scroll_top = document.documentElement.scrollTop;
        } else if (document.body) {
            scroll_top = document.body.scrollTop;
        }
        if (scroll_top > 800) {
            $('.lqp-top').removeClass('d-none');
        } else {
            $('.lqp-top').addClass('d-none');
        }
    }
    scrollBar();
    $(window).scroll(function () {
        scrollBar();
    });


    // 申请试用
    $('.apply').on('click', function () {
        let paramsData = $(this).closest('form[name="applyForm"]').serializeArray();
        let flag = false;
        // console.log(paramsData);
        $(paramsData).each(function (index, item) {
            if (['mc', 'mz', 'sj', 'em'].includes(item.name)) {
                if ($.trim(item.value) !== '') {
                    // 正则
                    function code(phone) {
                        let telVerify = /^1[3456789]{1}\d{9}$/;
                        return telVerify.test(phone)
                    }

                    // 手机格式验证
                    if (item.name === "sj") {
                        if (!code(item.value)) {
                            layer.msg('手机格式不正确！')
                            return false;
                        } else {
                            flag = true;
                        }
                    }
                    // flag = true;
                } else {
                    flag = false;
                    layer.msg('必填项不能为空！');
                    return false;
                }
            }
        });
        if (flag) {
            getAjax(paramsData);
        }
    });
    let getAjax = function (p) {
        $.ajax({
            url: 'http://hbcrm.local.com/home/index/send_apply',
            data: p,
            type: 'post',
            dataType: 'json',
            success: function (res) {
                console.log(1);
                console.log(res);
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                console.log(2);
                console.log(textStatus);
                // console.log(HTTP Status Code);
                console.log(errorThrown);
            },
            complete: function (XMLHttpRequest, textStatus) {
                console.log(3);
                console.log(textStatus);
            },
            statusCode: {
                200: function () {
                    layer.msg('申请成功!', function () {
                        let a = $('form[name="applyForm"]');
                        for (let i = 0; i < a.length; i++) {
                            $(a)[i].reset();
                        }
                        // $('#myform').reset()   这样是错误的 JQuery中没有reset方法
                        // 需要转成dom 再用reset方法即 $('#myform')[0].reset()
                    });
                }
            }
        })
    };
})(jQuery, window);
