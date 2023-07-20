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
                    limits: [15, 30, 45],
                    limit: 15,
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

            table.on('tool(currentTableFilter)', function (obj) {
                if (obj.event === 'delete') {
                    arronUtil.Toast.fire({
                        title: '真的删除行么？删除后不可恢复',
                        icon: 'question',
                        toast: false,
                        showConfirmButton: true,
                        confirmButtonText: '确定',
                        timer: false,
                    }).then(function (val) {
                        if (val.isConfirmed) {
                            $.post(arronUtil.url("/User/delUser"), { id: obj.data.id, company_id: obj.data.company_id }, function (res) {
                                let option = { title: res.msg }
                                if (res.code === 1) {
                                    option.icon = 'success'
                                    controller.reloadTable()
                                }

                                arronUtil.Toast.fire(option)
                            })
                        }
                    })
                }
            });
        },
    }

    controller.listener()
});
