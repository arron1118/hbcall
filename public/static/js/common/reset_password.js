layui.use(['form', 'jquery', 'arronUtil'], function () {
    let form = layui.form,
        arronUtil = layui.arronUtil,
        $ = layui.jquery;

    //监听提交
    form.on('submit(saveBtn)', function (data) {
        $.post(arronUtil.url("/user/resetPassword"), data.field, function (res) {
            if (res.code) {
                arronUtil.Toast.fire({
                    icon: 'success',
                    text: res.msg
                }).then((val) => {
                    location.href = arronUtil.url("/user/logout")
                })
            } else {
                arronUtil.Toast.fire({
                    text: res.msg
                });
            }
        })

        return false;
    });

});
