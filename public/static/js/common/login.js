
layui.use(['jquery', 'form', 'arronUtil'], function () {
    let form = layui.form,
        arronUtil = layui.arronUtil,
        $ = layui.jquery;

    let controller = {
        listener: function () {
            // 登录过期的时候，跳出ifram框架
            if (top.location !== self.location) top.location = self.location;

            // 粒子线条背景
            $(document).ready(function () {
                $('.layui-container').particleground({
                    dotColor: '#7ec7fd',
                    lineColor: '#7ec7fd'
                });
            });

            // 进行登录操作
            form.on('submit(login)', function (data) {
                let params = data.field, option = { position: 'top' };
                if (params.username === '') {
                    option.title = '用户名不能为空'
                    arronUtil.Toast.fire(option)
                    return false
                }
                if (params.password === '') {
                    option.title = '密码不能为空'
                    arronUtil.Toast.fire(option)
                    return false
                }

                if (params.captcha === '') {
                    option.title = '验证码不能为空'
                    arronUtil.Toast.fire(option)
                    return false
                }

                $.post(arronUtil.url("/user/login"), params, function (res) {
                    option.title = res.msg
                    if (res.code) {
                        option.icon = 'success'
                        option.timer = 1500
                        option.didDestroy = function () {
                            window.location = res.url
                        }
                    } else {
                        $('.captcha-img img').click()
                    }

                    arronUtil.Toast.fire(option)
                });

                return false;
            })
        },
    }

    controller.listener()
});
