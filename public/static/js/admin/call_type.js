layui.use(['jquery', 'table', 'arronUtil'], function () {
    let $ = layui.jquery,
        arronUtil = layui.arronUtil,
        table = layui.table;

    let controller = {
        listener: function () {

            table.init('currentTableFilter', {
                url: arronUtil.url("/XnumberStore/getNumberList"),
                id: 'XnumberStoreTable',
                toolbar: '#toolbarDemo',
                defaultToolbar: ['filter', 'exports', 'print', {
                    title: '提示',
                    layEvent: 'LAYTABLE_TIPS',
                    icon: 'layui-icon-tips'
                }],
                page: {
                    limits: [15, 30, 45, 60],
                    limit: 15,
                },
                skin: 'line',
                even: true,
            })
            /**
             * toolbar监听事件
             */
            table.on('toolbar(currentTableFilter)', function (obj) {
                if (obj.event === 'add') {  // 监听添加操作
                    arronUtil.Toast.fire({
                        toast: false,
                        timer: false,
                        input: 'text',
                        inputLabel: '号码',
                        inputPlaceholder: '请输入号码',
                        showConfirmButton: true,
                        confirmButtonText: '确定',
                        icon: '',
                        customClass: {
                            input: 'form'
                        }
                    }).then(res => {
                        if (res.isConfirmed) {
                            if (res.value.length !== 11 || !arronUtil.isPhone(res.value)) {
                                arronUtil.Toast.fire({ title: '请输入正确的号码' })
                                return false;
                            }

                            $.post(arronUtil.url("/XnumberStore/add"), { number: res.value }, function (r) {
                                let option = { title: r.msg }
                                if (r.code === 1) {
                                    option.icon = 'success'
                                    table.reload('XnumberStoreTable', {
                                        page: {
                                            curr: 1
                                        }
                                    });
                                }

                                arronUtil.Toast.fire(option)
                            })
                        }
                    })
                }
            })

            // 单元格编辑
            table.on('edit(currentTableFilter)', function (obj) {
                if (obj.value.length !== 11 || !arronUtil.isPhone(obj.value)) {
                    arronUtil.Toast.fire({ title: '请输入正确的号码' })
                    return false;
                }

                $.post(arronUtil.url("/XnumberStore/edit"), obj.data, function (res) {
                    let option = { title: res.msg }
                    if (res.code === 1) {
                        option.icon = 'success'
                    }

                    arronUtil.Toast.fire(option)
                })
            })
        },
    };

    controller.listener()
});
