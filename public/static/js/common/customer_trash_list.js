layui.use(['layer', 'miniTab', 'jquery', 'table', 'laydate', 'arronUtil'], function () {
    let $ = layui.jquery,
        layer = layui.layer,
        arronUtil = layui.arronUtil,
        miniTab = layui.miniTab,
        laydate = layui.laydate,
        table = layui.table;

    let controller = {
        listener: function () {

            miniTab.listen();
            // 客户列表
            table.init('currentTableFilter', {
                id: 'currentTableId',
                url: arronUtil.url("/Customer/trash_list"),
                method: 'post',
                where: {
                    type: type,
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
                switch (obj.event) {
                    case 'add':
                        layer.open({
                            type: 2,
                            title: '添加',
                            area: ['700px', '600px'],
                            content: arronUtil.url('/CustomerRecord/add') + '?customer_id=' + customer_id
                        });
                        break;
                }
            })
        },
    }

    controller.listener()
});
