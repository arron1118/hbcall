layui.use(['layer', 'miniTab', 'element', 'excel', 'upload', 'table', 'form', 'laydate', 'dropdown', 'arronUtil'], function () {
    let $ = layui.jquery,
        layer = layui.layer,
        miniTab = layui.miniTab,
        table = layui.table,
        upload = layui.upload,
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
        MAKE_CALL_URL: arronUtil.url('/hbcall/makeCall'),
        IMPORT_EXCEL_URL: arronUtil.url('/customer/importExcel'),
    }

    let controller = {
        makeCall: function (params = {phone: '', customerId: 0}, obj = null) {
            $.post(REQUEST_CONFIG.MAKE_CALL_URL, {mobile: params.phone, customerId: params.customerId}, function (res) {
                if ((res.code == '1000' || res.code == '0000' || res.code == '1003') && res.code !== 0) {
                    obj.update({
                        last_calltime: arronUtil.getDateTime(),
                        called_count: obj.data.called_count + 1
                    })

                    arronUtil.caller.success(res)
                } else {
                    arronUtil.caller.fail(res)
                }
            });
        },

        reloadTable: function (params = {}, deep = false) {
            let defaultParams = { type: type }
            let finalParams = Object.assign({}, defaultParams, params)
            table.reload('customerTable', {
                where: finalParams,
                page: {
                    curr: 1
                }
            }, deep)
        },

        upload: function () {
            // 导入客户
            let loading = null, flag = false;
            let uploadIns = upload.render({
                elem: '#importExcel',
                url: REQUEST_CONFIG.IMPORT_EXCEL_URL,
                accept: 'file', //普通文件
                exts: 'xls|excel|xlsx', //导入表格
                auto: true,  //选择文件后不自动上传
                data: {
                    type: type,
                },
                before: function (obj) {
                    if (!flag) {
                        arronUtil.Toast.fire({
                            icon: 'question',
                            title: `是否允许上传重复的${typeText}`,
                            html: `重复的${typeText}以${typeText}电话为准`,
                            toast: false,
                            showConfirmButton: true,
                            confirmButtonText: '允许',
                            showDenyButton: true,
                            denyButtonText: '不允许',
                            timer: false,
                            showCloseButton: true,
                        }).then((r) => {
                            if (r.isConfirmed) {
                                uploadIns.config.data.is_repeat_customer = 1
                                flag = true
                                uploadIns.upload()
                            } else {
                                uploadIns.config.data.is_repeat_customer = 0
                                flag = true
                                uploadIns.upload()
                            }
                        })
                    } else {
                        loading = layer.load(); //上传loading
                    }

                    return flag
                },
                // 选择文件回调
                choose: function (obj) {
                },
                done: function (res) {
                    layer.close(loading)
                    let op = {title: res.msg}
                    if (res.code === 1) {
                        op.icon = 'success';
                        op.timer = 1500
                        table.reload('customerTable', {
                            page: {
                                curr: 1
                            }
                        })
                    }

                    flag = false
                    Toast.fire(op)
                },
                error: function () {
                    flag = false
                    setTimeout(function () {
                        Toast.fire("上传失败！");
                        //关闭所有弹出层
                        layer.close(loading); //疯狂模式，关闭所有层
                    }, 1000);
                }
            });
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
                            Toast.fire(op).then(() => {
                                controller.reloadTable()
                            })
                        } else {
                            Toast.fire(op);
                        }
                    })
                }
            })
        },

        listener: function () {
            controller.upload()

            //菜单点击事件
            dropdown.on('click(filterUser)', function (options) {
                controller.reloadTable(Object.assign({}, form.val('searchForm'), options), true)
            });
            dropdown.on('click(filterCate)', function (options) {
                controller.reloadTable(Object.assign({}, form.val('searchForm'), options), true)
            });

            laydate.render({
                elem: '#datetime',
                range: ['#startDate', '#endDate'],
                type: 'datetime',
                done: function (value) {
                    let d = value.split(' - '),
                        params = [],
                        user = eval('(' + $('#filterUser li.layui-menu-item-checked').attr('lay-options') + ')'),
                        cate = eval('(' + $('#filterCate li.layui-menu-item-checked').attr('lay-options') + ')');
                    params = Object.assign({}, form.val('searchForm'), {startDate: d[0], endDate: d[1]})
                    Object.assign(params, user)
                    Object.assign(params, cate)
                    controller.reloadTable(params)
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
                        Toast.fire(option).then(function () {
                            let params = [],
                                cate_item = $('.cate-list.active')
                            params[cate_item.data('name')] = cate_item.data('key')
                            table.reload('customerTable', {
                                where: Object.assign({}, form.val('searchForm'), params)
                            })
                            $('[data-bs-dismiss="offcanvas"]').click()
                        })
                    } else {
                        Toast.fire(option)
                    }

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
                method: 'post',
                toolbar: '#currentTableBar',
                defaultToolbar: ['filter', 'exports', 'print', {
                    title: '提示',
                    layEvent: 'LAYTABLE_TIPS',
                    icon: 'layui-icon-tips'
                }],
                parseData: function (res) {
                    return {
                        'code': 0,
                        'msg': '',
                        'data': res.data,
                        'count': res.total
                    }
                },
                page: {
                    limits: [15, 30, 45, 60, 90, 150, 300, 600, 900, 1500],
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
                                    Toast.fire({
                                        title: res.msg,
                                    })
                                }
                            })
                        }
                        break;

                    case 'makeCall':
                        controller.makeCall({phone: obj.data.phone, customerId: obj.data.id}, obj);
                        break;

                    case 'delete':
                        controller.delete(editId)
                        break;
                }
            });

            table.on('toolbar(currentTableFilter)', function (obj) {
                let checkStatus = table.checkStatus('customerTable').data;
                let ids = '';

                if (['distribution', 'changeCate', 'delete'].includes(obj.event)) {
                    let temp = [];
                    if (checkStatus.length <= 0) {
                        Toast.fire({ title: '请选择' + typeText, timer: 1500 });
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
                                html += `<li class="list-group-item ${selector}" data-id="${item.id}">${item.username}</li>`
                            });
                        } else {
                            url = REQUEST_CONFIG.CHANGE_CATE_URL
                            selector = 'select-cate'
                            title = '选择分类'
                            delete cateList[-1]

                            $.each(cateList, (index, item) => {
                                html += `<li class="list-group-item ${selector}" data-id="${index}">${item}</li>`
                            })
                        }

                        layer.open({
                            type: 1,
                            title: title,
                            skin: 'layui-layer-rim',
                            area: ['420px', '500px'],
                            content: `
                                <div class="row justify-content-center">
                                    <div class="col-12">
                                        <ul class="list-group">
                                            ${html}
                                        </ul>
                                    </div>
                                </div>
                                `,
                            success: function (layero, index) {
                                layero.find('.' + selector).on('click', function () {
                                    id = $(this).data('id')
                                    obj.event === 'distribution' ? params.user_id = id : params.cate = id

                                    $.post(url, params, function (res) {
                                        let op = { title: res.msg }
                                        if (res.code === 1) {
                                            layer.close(index)
                                            op.icon = 'success'
                                            op.timer = 1500
                                            Toast.fire(op).then(() => {
                                                table.reload('customerTable', {
                                                    page: {
                                                        curr: 1
                                                    }
                                                })
                                            })
                                        } else {
                                            Toast.fire(op)
                                        }
                                    })
                                })
                            }
                        });

                        break;

                    case 'info':
                        arronUtil.showImportInfo()
                        break;

                    case 'importExcel':
                        $('#importExcel').click();
                        break;

                    case 'delete':
                        controller.delete(ids)
                        break;
                }
            });

            form.on('select(distributionFilter)', function (data) {
                console.log(data)
                let params = [],
                    user = eval('(' + $('#filterUser li.layui-menu-item-checked').attr('lay-options') + ')'),
                    cate = eval('(' + $('#filterCate li.layui-menu-item-checked').attr('lay-options') + ')');
                params[data.elem.name] = data.value
                params = Object.assign({}, form.val('searchForm'), params)
                Object.assign(params, user)
                Object.assign(params, cate)
                controller.reloadTable(params);
            })

            // 监听搜索操作
            form.on('submit(data-search-btn)', function (data) {
                let user = eval('(' + $('#filterUser li.layui-menu-item-checked').attr('lay-options') + ')'),
                    cate = eval('(' + $('#filterCate li.layui-menu-item-checked').attr('lay-options') + ')');
                Object.assign(data.field, user)
                Object.assign(data.field, cate)
                //执行搜索重载
                controller.reloadTable(data.field)

                return false
            });

            $('button[type="reset"]').on('click', function () {
                let params = [],
                    user = eval('(' + $('#filterUser li.layui-menu-item-checked').attr('lay-options') + ')'),
                    cate = eval('(' + $('#filterCate li.layui-menu-item-checked').attr('lay-options') + ')');
                Object.assign(params, user)
                Object.assign(params, cate)
                controller.reloadTable(params)
            })
        }
    }


    controller.listener()
});
