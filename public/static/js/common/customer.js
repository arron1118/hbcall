layui.use(['layer', 'miniTab', 'element', 'table', 'form', 'laydate', 'dropdown', 'arronUtil'], function () {
    let $ = layui.jquery,
        layer = layui.layer,
        miniTab = layui.miniTab,
        table = layui.table,
        dropdown = layui.dropdown,
        laydate = layui.laydate,
        form = layui.form,
        arronUtil = layui.arronUtil;

    const REQUEST_CONFIG = {
        ADD_URL: arronUtil.url('/customer/add'),
        EDIT_URL: arronUtil.url('/customer/edit'),
        DELETE_URL: arronUtil.url('/customer/del'),
        LIST_URL: arronUtil.url('/customer/getCustomerList'),
        RECORD_LIST_URL: arronUtil.url('/customer/customerRecordList'),
        CUSTOMER_PHONE_URL: arronUtil.url('/customer/getCustomerPhone'),
        DISTRIBUTION_URL: arronUtil.url('/customer/distribution'),
        CHANGE_CATE_URL: arronUtil.url('/customer/changeCate'),
    }

    let controller = {
        reloadTable: function (params = {}, deep = false) {
            let finalParams = controller.getFilterParams()
            Object.assign(finalParams, params)
            table.reload('customerTable', {
                where: finalParams,
                page: {
                    curr: 1
                }
            }, deep)
        },

        delete: function (ids) {
            arronUtil.Toast.fire({
                title: '确定删除么？',
                text: '删除后将无法恢复',
                toast: false,
                icon: 'question',
                showConfirmButton: true,
                confirmButtonText: '确定',
                timer: false,
                showCloseButton: true,
            }).then((r) => {
                if (r.isConfirmed) {
                    $.post(REQUEST_CONFIG.DELETE_URL, { id: ids }, function (res) {
                        let op = { title: res.msg }
                        if (res.code === 1) {
                            op.icon = 'success';
                            op.timer = 1500
                            arronUtil.Toast.fire(op).then(() => {
                                controller.reloadTable()
                            })
                        } else {
                            arronUtil.Toast.fire(op);
                        }
                    })
                }
            })
        },

        getFilterParams: function () {
            let params = { type: type },
                user = eval('(' + $('#filterUser li.layui-menu-item-checked').attr('lay-options') + ')'),
                cate = eval('(' + $('#filterCate li.layui-menu-item-checked').attr('lay-options') + ')');

            return Object.assign({}, params, user, cate, form.val('searchForm'))
        },

        listener: function () {
            arronUtil.importCustomer('#importExcel', type, typeText, function () {
                controller.reloadTable()
            })

            //菜单点击事件
            dropdown.on('click(filterUser)', function (options) {
                controller.reloadTable()
            });
            dropdown.on('click(filterCate)', function (options) {
                controller.reloadTable()
            });

            laydate.render({
                elem: '#datetime',
                range: ['#startDate', '#endDate'],
                type: 'datetime',
                done: function (value) {
                    let d = value.split(' - ')
                    controller.reloadTable({ startDate: d[0], endDate: d[1] })
                }
            });

            let offcanvas = $('#offcanvas')
            offcanvas.on('show.bs.offcanvas', e => {
                let id = $(e.relatedTarget).data('id')
                let f = document.formEditor, title = ''
                if (id) {
                    title = '编辑'
                    $.get(REQUEST_CONFIG.EDIT_URL, {id: id}, res => {
                        if (res.code === 1) {
                            for (const element of f.elements) {
                                let name = element.name,
                                    nodeName = element.nodeName,
                                    el = $(e.currentTarget).find('[name="' + name + '"]');

                                if (nodeName === 'SELECT') {
                                    let v = Object.keys(res.cateList).find(key => parseInt(key) === res.data[name])
                                    el.val(v)
                                } else {
                                    el.val(res.data[name])
                                }

                                if (name === 'phone') {
                                    el.attr('disabled', true)
                                }
                            }
                        }
                    })
                } else {
                    title = '添加'
                    $(e.currentTarget).find('[name="phone"]').attr('disabled', false)
                    f.reset()
                }

                $('.offcanvas-title').text(title)
            })

            $('form[name="formEditor"]').submit(function () {
                let formData = $(this).serialize(),
                    id = $('[name="id"]').val(),
                    url = id ? REQUEST_CONFIG.EDIT_URL : REQUEST_CONFIG.ADD_URL

                $.post(url, formData, function (res) {
                    let option = {title: res.msg}
                    if (res.code === 1) {
                        option.icon = 'success'
                        option.timer = 1500
                        option.didDestroy = function () {
                            if (id) {
                                table.reload('customerTable', {
                                    where: controller.getFilterParams()
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

            miniTab.listen();

            table.init('currentTableFilter', {
                id: 'customerTable',
                url: REQUEST_CONFIG.LIST_URL,
                where: {
                    type: type,
                },
                height: 725,
                method: 'post',
                toolbar: '#currentTableBar',
                defaultToolbar: ['filter', 'exports', 'print', {
                    title: '提示',
                    layEvent: 'LAYTABLE_TIPS',
                    icon: 'layui-icon-tips'
                }],
                page: {
                    limits: [15, 30, 45, 60, 90, 150, 300, 600],
                    limit: 15,
                },
                even: true,
                skin: 'line',
                cellMinWidth: 50,
            })

            table.on('tool(currentTableFilter)', function (obj) {
                let editId = obj.data.id;
                // 传这个Id去后台查出备忘列表，渲染
                switch (obj.event) {
                    case 'showRecord':
                        let index = layer.open({
                            title: '回访记录列表',
                            type: 2,
                            shade: 0.2,
                            maxmin: true,
                            shadeClose: true,
                            area: ['100%', '100%'],
                            content: REQUEST_CONFIG.RECORD_LIST_URL + '?customerId=' + editId,
                        });
                        $(window).on("resize", function () {
                            layer.full(index);
                        });
                        break;

                    case 'showPhone':
                        let hide = $(this).find('.hide-phone'), icon = $(this).find('.phone-icon');

                        if (hide.data('show')) {
                            hide.text(hide.data('value')).data('show', false)
                            icon.removeClass('fa-eye-slash').addClass('fa-eye')
                        } else {
                            $.get(REQUEST_CONFIG.CUSTOMER_PHONE_URL, {id: editId}, function (res) {
                                if (res.code === 1 && res.data) {
                                    hide.text(res.data).data('show', true)
                                    icon.removeClass('fa-eye').addClass('fa-eye-slash')
                                } else {
                                    arronUtil.Toast.fire({
                                        title: res.msg,
                                    })
                                }
                            })
                        }
                        break;

                    case 'makeCall':
                        arronUtil.caller.makeCall({
                            phone: obj.data.phone,
                            customerId: obj.data.id
                        }, (res) => {
                            obj.update({
                                last_calltime: arronUtil.getDateTime(),
                                called_count: obj.data.called_count + 1
                            })
                        });
                        break;

                    case 'delete':
                        controller.delete(editId)
                        break;
                }
            });

            table.on('toolbar(currentTableFilter)', function (obj) {
                let checkStatus = table.checkStatus('customerTable').data;
                let ids = '';

                if (['distribution', 'changeCate', 'delete', 'migrate'].includes(obj.event)) {
                    let temp = [];
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
                }

                switch (obj.event) {
                    case 'distribution':
                    case 'changeCate':
                        let html = '',
                            params = { ids: ids },
                            title = '',
                            url, selector;

                        if (obj.event === 'distribution') {
                            url = REQUEST_CONFIG.DISTRIBUTION_URL
                            selector = 'select-user'
                            title = '选择用户'

                            $.each(user, (index, item) => {
                                html += `<li class="list-group-item list-group-item-action ${selector}" data-id="${item.id}" style="cursor: pointer;">${item.username}</li>`
                            });
                        } else {
                            url = REQUEST_CONFIG.CHANGE_CATE_URL
                            selector = 'select-cate'
                            title = '选择分类'
                            delete cateList[-1]

                            $.each(cateList, (index, item) => {
                                html += `<li class="list-group-item list-group-item-action ${selector}" data-id="${index}" style="cursor: pointer;">${item}</li>`
                            })
                        }

                        arronUtil.Toast.fire({
                            titleText: title,
                            icon: false,
                            timer: false,
                            toast: false,
                            html: `
                            <ul class="list-group list-group-flush">
                                ${html}
                            </ul>
                            `,
                            didOpen: () => {
                                $(window.Swal.getHtmlContainer()).find('.' + selector).on('click', function () {
                                    let id = $(this).data('id')
                                    obj.event === 'distribution' ? params.user_id = id : params.cate = id

                                    $.post(url, params, function (res) {
                                        let op = { title: res.msg }
                                        if (res.code === 1) {
                                            op.icon = 'success'
                                            op.timer = 1500
                                            op.didDestroy = () => {
                                                controller.reloadTable()
                                            }
                                        }

                                        arronUtil.Toast.fire(op)
                                    })
                                })
                            }
                        })
                        break;

                    case 'info':
                        arronUtil.showImportInfo(type)
                        break;

                    case 'importExcel':
                        $('#importExcel').click();
                        break;

                    case 'delete':
                        controller.delete(ids)
                        break;

                    case 'migrate':
                        let t = type === 1 ? '人才' : '客户'
                        arronUtil.Toast.fire({
                            timer: false,
                            icon: 'question',
                            toast: false,
                            text: '确定迁移所选的[' + typeText + ']到[' + t + '库]吗？',
                            showConfirmButton: true,
                            confirmButtonText: '执行迁移',
                            showDenyButton: true,
                            denyButtonText: '取消',
                        }).then(val => {
                            if (val.isConfirmed) {
                                arronUtil.Toast.fire({
                                    timer: false,
                                    icon: 'info',
                                    title: '正在迁移...',
                                    didOpen: () => window.Swal.showLoading()
                                })
                                $.post(arronUtil.url('/Customer/migrate'), { ids: ids, type: type }, (res) => {
                                    let option = {
                                        title: res.msg,
                                        toast: false,
                                        timer: false,
                                        showConfirmButton: true,
                                        confirmButtonText: '确定',
                                    }

                                    if (res.code === 1) {
                                        option.icon = 'success'
                                        option.text = '成功迁移 ' + res.data + ' 条数据'

                                        controller.reloadTable()
                                    }

                                    arronUtil.Toast.fire(option)
                                })
                            }
                        })
                        break;

                    case 'trash':
                        let index = layer.open({
                            title: '回收站 <span style="color: #999">(已分配并且被回收的数据)</span>',
                            type: 2,
                            shade: 0.2,
                            maxmin: true,
                            shadeClose: true,
                            area: ['100%', '100%'],
                            content: arronUtil.url('/Customer/trash_list') + '?type=' + type,
                        });
                        $(window).on("resize", function () {
                            layer.full(index);
                        });
                        break;
                }
            });

            form.on('select(distributionFilter)', function (data) {
                controller.reloadTable();
            })

            // 监听搜索操作
            form.on('submit(data-search-btn)', function (data) {
                controller.reloadTable()

                return false
            });

            $('button[type="reset"]').on('click', function () {
                document.searchForm.reset()
                controller.reloadTable()
            })
        }
    }


    controller.listener()
});
