layui.use(['jquery', 'table', 'arronUtil', 'dropdown'], function () {
    let $ = layui.jquery,
        arronUtil = layui.arronUtil,
        dropdown = layui.dropdown,
        table = layui.table;

    let controller = {
        reloadTable: function (params = {}, deep = false) {
            let finalParams = controller.getFilterParams()
            Object.assign(finalParams, params)
            table.reload('currentTable', {
                where: finalParams,
                page: {
                    curr: 1
                }
            }, deep)
        },

        getFilterParams: function () {
            let user = eval('(' + $('#filterUser li.layui-menu-item-checked').attr('lay-options') + ')');

            return {...user}
        },

        listener: function () {
            //菜单点击事件
            dropdown.on('click(filterUser)', function (options) {
                controller.reloadTable()
            });

            table.init('currentTableFilter', {
                url: arronUtil.url("/SigninLogs/companyLogs"),
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
