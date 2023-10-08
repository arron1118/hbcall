layui.use(['jquery', 'table', 'arronUtil'], function () {
    let $ = layui.jquery,
        arronUtil = layui.arronUtil,
        table = layui.table;

    let controller = {
        listener: function () {

            table.init('currentTableFilter', {
                url: arronUtil.url("/SigninLogs/userLogs"),
                id: 'currentTable',
                method: 'post',
                toolbar: true,
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
        },
    };

    controller.listener()
});
