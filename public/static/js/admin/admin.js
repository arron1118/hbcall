layui.use(['jquery', 'form', 'table', 'arronUtil'], function () {
    let $ = layui.jquery,
        form = layui.form,
        table = layui.table,
        arronUtil = layui.arronUtil;

    let controller = {
        reloadTable: function (params = {}) {
            let defaultParams = {},
                finalParams = Object.assign({}, defaultParams, form.val('searchForm'), params)
            //执行搜索重载
            table.reload('userTable', {
                page: {
                    curr: 1
                },
                where: finalParams
            }, false);
        },

        updateUserField: function (param) {
            $.post(arronUtil.url("/Admin/updateUser"), param, function (res) {
                let option = { title: res.msg };
                if (res.code === 1) {
                    option.icon = 'success'
                }

                arronUtil.Toast.fire(option)
            })
        },

        listener: function () {

            $('#offcanvas').on('show.bs.offcanvas', e => {
                let id = $(e.relatedTarget).data('id')
                let f = document.formEditor, title = ''

                $('#newsImgBox').html('')
                if (id) {
                    title = '编辑'
                    $.get(arronUtil.url("/Admin/edit"), { id: id }, res => {
                        if (res.code === 1) {
                            for (const element of f.elements) {
                                let name = element.name,
                                    nodeName = element.nodeName,
                                    el = $(e.currentTarget).find('[name="' + name + '"]');

                                el.val(res.data['userInfo'][name])

                                if (['username'].includes(name)) {
                                    el.prop('readonly', true)
                                }
                            }
                        } else {
                            arronUtil.Toast.fire({ title: res.msg })
                        }
                    })
                } else {
                    title = '添加'
                    f.reset()
                    $('[name="id"]').val('')
                    $('input[name=username]').prop('readonly', false)
                }

                $('.offcanvas-title').text(title)
            })

            $('form[name="formEditor"]').submit(function () {
                let formData = $(this).serializeArray(),
                    id = $('[name="id"]').val(),
                    url = id ? arronUtil.url("/Admin/edit") : arronUtil.url("/Admin/add");

                $.post(url, formData, function (res) {
                    let option = { title: res.msg }
                    if (res.code === 1) {
                        option.icon = 'success'
                        option.timer = 2000
                        option.didDestroy = function () {
                            if (id) {
                                table.reload('userTable', {
                                    where: form.val('searchForm')
                                })
                            } else {
                                controller.reloadTable()
                            }

                            $('[data-bs-dismiss="offcanvas"]').click()
                        }
                    }

                    arronUtil.Toast.fire(option)
                })

                return false
            })

            table.init('adminTableFilter', {
                url: arronUtil.url("/Admin/index"),
                id: 'userTable',
                method: 'post',
                toolbar: '#toolbarAdmin',
                defaultToolbar: ['filter', 'exports', 'print', {
                    title: '提示',
                    layEvent: 'LAYTABLE_TIPS',
                    icon: 'layui-icon-tips'
                }],
                page: {
                    limits: [15, 30, 45, 60],
                    limit: 15,
                },
                height: 725,
                skin: 'line',
                even: true,
            })

            // 监听搜索操作
            form.on('submit(data-search-btn)', function (data) {
                //执行搜索重载
                controller.reloadTable()

                return false
            });
            form.on('select(userStatusFilter)', function (data) {
                controller.reloadTable();
            })

            form.on('switch(statusFilter)', function (obj) {
                let status = obj.elem.checked ? 1 : 0;
                let params = { id: obj.value, status: status };
                if (!status) {
                    arronUtil.Toast.fire({
                        title: '确认禁止吗？',
                        text: '禁止后，该账号将无法正常使用',
                        toast: false,
                        showConfirmButton: true,
                        confirmButtonText: '确定',
                        timer: false,
                        showCloseButton: true,
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

            table.on('tool(adminTableFilter)', function (obj) {
                switch (obj.event) {
                    case 'del':
                        arronUtil.Toast.fire({
                            title: '确认删除吗？',
                            text: '删除账号后，将无法恢复',
                            toast: false,
                            showConfirmButton: true,
                            confirmButtonText: '确定',
                            timer: false,
                            showCloseButton: true,
                        }).then((r) => {
                            if (r.isConfirmed) {
                                $.post(arronUtil.url('/Admin/del'), {id: obj.data.id}, function (res) {
                                    let option = { title: res.msg };
                                    if (res.code === 1) {
                                        option.icon = 'success'
                                        controller.reloadTable()
                                    }

                                    arronUtil.Toast.fire(option)
                                })
                            }
                        })
                        break;
                }
            });
        },
    }

    controller.listener()
});
