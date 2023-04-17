layui.use(['form', 'table', 'arronUtil'], function () {
    let $ = layui.jquery,
        form = layui.form,
        arronUtil = layui.arronUtil,
        table = layui.table;

    let controller = {
        reloadTable: function (params = {}) {
            table.reload('userTable', {
                page: {
                    curr: 1
                },
                where: params
            })
        },

        listener: function () {

            table.init('currentTableFilter', {
                url: arronUtil.url("/user/getSubUserList") + '?company_id=' + company_id,
                id: 'userTable',
                page: {
                    limits: [10, 20, 30, 40],
                    limit: 10,
                },
                skin: 'line',
                even: true,
            })

            // 监听搜索操作
            form.on('submit(data-search-btn)', function (data) {
                if (data.field.username === "" && data.field.phone === "") {
                    arronUtil.Toast.fire({
                        title: '搜索内容不能为空'
                    })
                    return false
                }
                controller.reloadTable(data.field);
                return false;
            });

            $('button[type="reset"]').on('click', function () {
                document.searchForm.reset()
                controller.reloadTable()
            })
        },
    }

    controller.listener()
});
