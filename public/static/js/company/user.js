layui.use(['form', 'table', 'arronUtil', 'jquery'], function () {
    let $ = layui.jquery,
        arronUtil = layui.arronUtil,
        form = layui.form,
        table = layui.table;

    let controller = {
        reloadTable: function (params) {
            let p = Object.assign({}, form.val('searchForm'), params)
            table.reload('userTable', {
                page: {
                    curr: 1
                },
                where: p
            })
        },

        updateUserField: function (param) {
            $.post(arronUtil.url("/User/updateUser"), param, function (res) {
                let option = { title: res.msg }
                if (res.code === 1) {
                    option.icon = 'success'
                }

                arronUtil.Toast.fire(option)
            })
        },

        listener: function () {

            table.init('currentTableFilter', {
                url: arronUtil.url("/user/getUserList"),
                id: 'userTable',
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

            // 监听搜索操作
            form.on('submit(data-search-btn)', function (data) {
                if (data.field.username === "" && data.field.phone === "") {
                    layer.msg('搜索内容不能为空', { icon: 0 })
                    return false
                }
                controller.reloadTable();
                return false;
            });

            form.on('select(tryUserFilter)', function (data) {
                controller.reloadTable();
            })

            $('button[type="reset"]').on('click', function () {
                document.searchForm.reset()
                controller.reloadTable()
            })

            form.on('switch(statusFilter)', function (obj) {
                let status = obj.elem.checked ? 1 : 0,
                    params = { id: obj.value, status: status }
                if (!status) {
                    arronUtil.Toast.fire({
                        title: '禁止后，该账号将无法正常使用',
                        timer: false,
                        showConfirmButton: true,
                        confirmButtonText: '确定',
                        showCancelButton: true,
                        cancelButtonText: '取消',
                        toast: false,
                        icon: 'question',
                    }).then((r) => {
                        if (r.isConfirmed) {
                            controller.updateUserField(params)
                        } else {
                            obj.elem.checked = true
                            obj.othis.addClass('layui-form-onswitch').html('<em>正常</em><i></i>')
                        }
                    })
                } else {
                    controller.updateUserField(params)
                }
            })

            /**
             * toolbar监听事件
             */
            table.on('toolbar(currentTableFilter)', function (obj) {
                if (obj.event === 'add') {  // 监听添加操作
                    $.post(arronUtil.url("/User/checkLimitUser"), {}, function (res) {
                        if (res.code === 1) {
                            let index = layer.open({
                                title: '开通账号',
                                type: 2,
                                shade: 0.2,
                                maxmin: true,
                                shadeClose: true,
                                anim: 2,
                                area: ['100%', '100%'],
                                content: arronUtil.url("/user/add"),
                            });
                            $(window).on("resize", function () {
                                layer.full(index);
                            });
                        } else {
                            arronUtil.Toast.fire({
                                title: res.msg
                            })
                        }
                    })
                }
            });

            table.on('tool(currentTableFilter)', function (obj) {
                if (obj.event === 'edit') {
                    let index = layer.open({
                        title: '编辑账号',
                        type: 2,
                        shade: 0.2,
                        maxmin:true,
                        shadeClose: true,
                        area: ['100%', '100%'],
                        content: arronUtil.url("/user/edit") + '?id=' + obj.data.id,
                    });
                    $(window).on("resize", function () {
                        layer.full(index);
                    });
                } else if (obj.event === 'delete') {
                    arronUtil.Toast.fire({
                        title: '真的删除行么？删除后不可恢复',
                        icon: 'question',
                        toast: false,
                        showConfirmButton: true,
                        confirmButtonText: '确定',
                        timer: false,
                    }).then(function (val) {
                        if (val.isConfirmed) {
                            $.post(arronUtil.url("/User/del"), { id: obj.data.id }, function (res) {
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
        }
    }

    controller.listener()
});
