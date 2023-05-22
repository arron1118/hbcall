layui.use(['form', 'table', 'arronUtil', 'jquery', 'laydate'], function () {
    let $ = layui.jquery,
        laydate = layui.laydate,
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
            // 时间限制为现在
            let minDate = arronUtil.getDateTime();
            laydate.render({
                elem: '#testEndTime',
                type: 'datetime',
                min: minDate,
                value: arronUtil.getDateTime({ day: 1 }),
            })
            $('#isTest').on('click', function (e) {
                let endtimeItem = $('.test-endtime'),
                    limitCallNumber = $('.limit-call-number')
                if (e.target.checked) {
                    endtimeItem.removeClass('d-none')
                    limitCallNumber.removeClass('d-none')
                    $(this).val(1)
                } else {
                    endtimeItem.addClass('d-none')
                    limitCallNumber.addClass('d-none')
                }
            })
            $('#offcanvas').on('show.bs.offcanvas', e => {
                let id = $(e.relatedTarget).data('id')
                let f = document.formEditor, title = ''

                $('#newsImgBox').html('')
                if (id) {
                    title = '编辑'
                    $.get(arronUtil.url("/User/edit"), { id: id }, res => {
                        if (res.code === 1) {
                            for (const element of f.elements) {
                                let name = element.name,
                                    nodeName = element.nodeName,
                                    el = $(e.currentTarget).find('[name="' + name + '"]');

                                if (nodeName === 'SELECT') {
                                    let v = ''
                                    if (name === 'number_store_id') {
                                        v = res.data['userInfo']['userXnumber']['number_store_id']
                                    }
                                    el.val(v)
                                } else {
                                    if (['is_test', 'status'].includes(name)) {
                                        // el.val(1)
                                        if (res.data['userInfo'][name] === 1) {
                                            el.prop('checked', true)
                                            if ('is_test' === name) {
                                                $('.test-endtime').removeClass('d-none')
                                                $('.limit-call-number').removeClass('d-none')
                                            }
                                        } else {
                                            el.prop('checked', false)
                                            if ('is_test' === name) {
                                                $('.test-endtime').addClass('d-none')
                                                $('.limit-call-number').addClass('d-none')
                                            }
                                        }
                                    } else {
                                        el.val(res.data['userInfo'][name])
                                    }
                                }

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
                    $('.test-endtime').addClass('d-none')
                    $('.limit-call-number').addClass('d-none')
                }

                $('.offcanvas-title').text(title)
            })

            $('form[name="formEditor"]').submit(function () {
                let formData = $(this).serializeArray(),
                    id = $('[name="id"]').val(),
                    url = id ? arronUtil.url("/User/edit") : arronUtil.url("/User/add"),
                    rate = $(this).find('input[name=rate]');

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

            table.init('currentTableFilter', {
                url: arronUtil.url("/user/getUserList"),
                id: 'userTable',
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
                height: 750,
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
