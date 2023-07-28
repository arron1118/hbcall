layui.use(['layer', 'miniTab', 'jquery', 'table', 'laydate', 'arronUtil'], function () {
    let $ = layui.jquery,
        layer = layui.layer,
        arronUtil = layui.arronUtil,
        miniTab = layui.miniTab,
        laydate = layui.laydate,
        table = layui.table;

    const REQUEST_CONFIG = {
        RESTORE_URL: arronUtil.url('/customer/restore'),
    }

    let controller = {
        listener: function () {

            miniTab.listen();
            // 客户列表
            table.init('currentTableFilter', {
                id: 'customerTable',
                url: arronUtil.url("/Customer/recycle_list"),
                method: 'post',
                where: {
                    type: type,
                    user_id: userId,
                },
                height: 750,
                toolbar: '#currentTableBar',
                // height: 680,
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
            });

            table.on('toolbar(currentTableFilter)', function (obj) {
                let checkStatus = table.checkStatus('customerTable').data;
                let ids = '',
                    temp = [];

                if (checkStatus.length <= 0) {
                    arronUtil.Toast.fire({ title: '请选择' + typeText, timer: 1500 });
                    return false;
                }

                $.each(checkStatus, (index, item) => {
                    temp.push(item.id)
                })

                if (temp.length > 0) {
                    ids = temp.join(',')
                }

                if (obj.event === 'restore') {
                    let params = { ids: ids }

                    $.post(REQUEST_CONFIG.RESTORE_URL, params, function (res) {
                        let op = { title: res.msg }
                        if (res.code === 1) {
                            op.icon = 'success'
                            op.timer = 1500
                            op.didDestroy = () => {
                                table.reload('customerTable')
                            }
                        }

                        arronUtil.Toast.fire(op)
                    })
                }
            });
        },
    }

    controller.listener()
});
