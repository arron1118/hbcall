layui.use(['form', 'jquery', 'arronUtil'], function () {
    let form = layui.form,
        arronUtil = layui.arronUtil,
        $ = layui.jquery;

    //监听提交
    form.on('submit(saveBtn)', function (data) {
        $.post(arronUtil.url("/user/resetPassword"), data.field, function (res) {
            let option = { title: res.msg, position: 'top' }
            if (res.code) {
                option.icon = 'success'
                option.timer = 1500
                option.didDestroy = () => {
                    location.href = arronUtil.url("/user/logout")
                }
            }

            arronUtil.Toast.fire(option)
        })

        return false;
    });

});
