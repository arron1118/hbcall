
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
                let params = data.field;
                if (params.username === '') {
                    arronUtil.Toast.fire({
                        text: '用户名不能为空'
                    });
                    return false;
                }
                if (params.password === '') {
                    arronUtil.Toast.fire({
                        text: '密码不能为空'
                    });
                    return false;
                }

                if (params.captcha === '') {
                    arronUtil.Toast.fire({
                        text: '验证码不能为空'
                    });
                    return false;
                }

                $.post(arronUtil.url("/user/login"), params, function (res) {
                    if (res.code) {
                        arronUtil.Toast.fire({icon: 'success', text: res.msg}).then(function () {
                            window.location = res.url;
                        });
                    } else {
                        $('input[name="__token__"]').val(res.data.token);
                        arronUtil.Toast.fire({
                            text: res.msg
                        });
                    }
                });

                return false;
            })
        },
    }

    controller.listener()
});
