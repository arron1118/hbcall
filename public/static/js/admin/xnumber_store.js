layui.use(['jquery', 'form', 'table', 'arronUtil'], function () {
    let $ = layui.jquery,
        form = layui.form,
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
                parseData: function (res) {
                    return {
                        'code': 0,
                        'msg': '',
                        'data': res.rows,
                        'count': res.total
                    }
                },
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
                switch (obj.event) {  // 监听添加操作
                    case 'add':
                        layer.prompt({title: '输入号码'}, function (input, index) {
                            if (input.length !== 11 || !arronUtil.isPhone(input)) {
                                arronUtil.Toast.fire({ title: '请输入正确的号码' })
                                return false;
                            }

                            $.post(arronUtil.url("/XnumberStore/add"), { number: input }, function (res) {
                                let option = { title: res.msg }
                                if (res.code === 1) {
                                    option.icon = 'success'
                                    layer.close(index)
                                    table.reload('XnumberStoreTable', {
                                        page: {
                                            curr: 1
                                        }
                                    });
                                }

                                arronUtil.Toast.fire(option)
                            })
                        });
                        break;
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
