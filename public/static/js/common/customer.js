layui.use(['layer', 'miniTab', 'element', 'excel', 'upload', 'table', 'form', 'laydate', 'dropdown'], function () {
    let $ = layui.jquery,
        layer = layui.layer,
        miniTab = layui.miniTab,
        element = layui.element,
        excel = layui.excel,
        table = layui.table,
        upload = layui.upload,
        dropdown = layui.dropdown,
        laydate = layui.laydate,
        form = layui.form;

    //菜单点击事件
    dropdown.on('click(filterUser)', function(options){
        reloadTable(Object.assign({}, form.val('searchForm'), options), true)
    });
    dropdown.on('click(filterCate)', function(options){
        reloadTable(Object.assign({}, form.val('searchForm'), options), true)
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
            params = Object.assign({}, form.val('searchForm'), { startDate: d[0], endDate: d[1] })
            Object.assign(params, user)
            Object.assign(params, cate)
            reloadTable(params)
        }
    });

    let offcanvas = $('#offcanvas')
    offcanvas.on('show.bs.offcanvas', e => {
        let id = $(e.relatedTarget).data('id')
        let f = document.formEditor, title = ''
        if (id) {
            title = '编辑'
            $.get('/customer/edit', { id: id }, res => {
                if (res.code === 1) {
                    for (let i = 0; i < f.elements.length; i++) {
                        let name = f.elements[i].name,
                            nodeName = f.elements[i].nodeName,
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
            url = id ? '/customer/edit' : '/customer/add'

        $.post(url, formData, function (res) {
            let option = { title: res.msg }
            if (res.code === 1) {
                option.icon = 'success'
                option.timer = 1500
                let params = [],
                    cate_item = $('.cate-list.active')
                params[cate_item.data('name')] = cate_item.data('key')
                Toast.fire(option).then(function () {
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
    let option = { icon: 0 }

    table.init('currentTableFilter', {
        id: 'customerTable',
        url: '/customer/getCustomerList',
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
                    content: '/customer/customerRecordList?customerId=' + editId,
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
                    $.get('/customer/getCustomerPhone', { id: editId }, function (res) {
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
                makeCall({ phone: obj.data.phone, customerId: obj.data.id }, obj);
                break;
            case 'delete':
                layer.confirm('确定删除么？', { title: '温馨提示' }, function (index) {
                    layer.close(index)
                    $.post('/customer/del', { id: editId }, function (res) {
                        let op = { title: res.msg }
                        if (res.code === 1) {
                            op.icon = 'success';
                            op.timer = 1500
                            Toast.fire(op).then(() => {
                                reloadTable()
                            })
                        } else {
                            Toast.fire(op);
                        }
                    })
                })
                break;
        }
    });

    table.on('toolbar(currentTableFilter)', function (obj) {
        let checkStatus = table.checkStatus('customerTable').data;
        let ids = '';

        if (['distribution', 'changeCate', 'delete'].includes(obj.event)) {
            let temp = [];
            if (checkStatus.length <= 0) {
                Toast.fire('请选择' + typeText, { timer: 1500 });
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
                let userId = 0, userHtml = '';
                $.each(user, (index, item) => {
                    userHtml += `<li class="list-group-item select-user" data-id="${item.id}">${item.username}</li>`
                })
                layer.open({
                    type: 1,
                    title: '选择用户',
                    skin: 'layui-layer-rim',
                    area: ['420px', '500px'],
                    content: `
                                <div class="row justify-content-center">
                                    <div class="col-12">
                                        <ul class="list-group">
                                            ${userHtml}
                                        </ul>
                                    </div>
                                </div>
                                `,
                    success: function(layero, index){
                        layero.find('.select-user').on('click', function () {
                            userId = $(this).data('id')

                            $.post('/customer/distribution', { ids: ids, user_id: userId }, function (res) {
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
            case 'changeCate':
                let cateId = 0, cateHtml = '';
                $.each(cateList, (index, item) => {
                    cateHtml += `<li class="list-group-item select-cate" data-id="${index}">${item}</li>`
                })
                console.log(location)
                layer.open({
                    type: 1,
                    title: '选择分类',
                    skin: 'layui-layer-rim',
                    area: ['420px', '500px'],
                    content: `
                                <div class="row justify-content-center">
                                    <div class="col-12">
                                        <ul class="list-group">
                                            ${cateHtml}
                                        </ul>
                                    </div>
                                </div>
                                `,
                    success: function(layero, index){
                        layero.find('.select-cate').on('click', function () {
                            cateId = $(this).data('id')

                            $.post('/customer/changeCate', { ids: ids, cate: cateId }, function (res) {
                                let op = { title: res.msg }
                                if (res.code === 1) {
                                    layer.close(index)
                                    op.icon = 'success'
                                    op.timer = 1500
                                    Toast.fire(op).then(() => {
                                        table.reload('customerTable')
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
                layer.open({
                    type: 1,
                    title: false,
                    skin: 'layui-layer-rim',
                    area: ['615px', '385px'],
                    content: `<div class="p-3">
                           注：只能上传Excel表格，格式为:<br /> ['${typeText}名称', '电话号码', '所在地', '邮箱', '备注']
                           <img src="/static/images/customer-example-import.png" class="pt-3" />
                        </div>`
                });
                break;
            case 'importExcel':
                $('#importExcel').click();
                break;
            case 'delete':
                layer.confirm('确定删除么？', { title: '温馨提示' }, function (index) {
                    $.post('/customer/del', { id: ids }, function (res) {
                        layer.close(index)
                        let op = { title: res.msg }
                        if (res.code === 1) {
                            op.icon = 'success'
                            op.timer = 1500
                            Toast.fire(op).then(() => {
                                reloadTable()
                            })
                        } else {
                            Toast.fire(op)
                        }
                    })
                });
                break;
        }
    });

    let makeCall = function (params = { phone: '', customerId: 0 }, obj = null) {
        $.post('/hbcall/makeCall', { mobile: params.phone, customerId: params.customerId }, function (res) {
            if ((res.code == '1000' || res.code == '0000' || res.code == '1003') && res.code !== 0) {
                obj.update({
                    last_calltime: utils.getDateTime(),
                    called_count: obj.data.called_count + 1
                })

                utils.caller.success(res)
            } else {
                utils.caller.fail(res)
            }
        });
    }

    // 导入客户
    let loading = null, flag = false;
    let uploadIns = upload.render({
        elem: '#importExcel',
        url: '/customer/importExcel',
        accept: 'file', //普通文件
        exts: 'xls|excel|xlsx', //导入表格
        auto: true,  //选择文件后不自动上传
        data: {
            type: type,
        },
        before: function (obj) {
            // loading = layer.load(); //上传loading
            if (!flag) {
                layer.confirm(`是否允许上传重复的${typeText}，注：重复的${typeText}以${typeText}电话为准`,
                    { icon: 3, title: '温馨提示', btn: ['允许', '不允许'] }, function (index) {
                        uploadIns.config.data.is_repeat_customer = 1
                        layer.close(index)
                        flag = true
                        uploadIns.upload()
                    }, function (index) {
                        uploadIns.config.data.is_repeat_customer = 0
                        flag = true
                        uploadIns.upload()
                    })
            }

            return flag
        },
        // 选择文件回调
        choose: function (obj) {
        },
        done: function (res) {
            layer.close(loading)
            let op = { title: res.msg }
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

    let reloadTable = function (params = {}, deep = false) {
        table.reload('customerTable', {
            where: params,
            page: {
                curr: 1
            }
        }, deep)
    }
    form.on('select(distributionFilter)', function (data) {
        let params = [],
            user = eval('(' + $('#filterUser li.layui-menu-item-checked').attr('lay-options') + ')'),
            cate = eval('(' + $('#filterCate li.layui-menu-item-checked').attr('lay-options') + ')');
        params[data.elem.name] = data.value
        params = Object.assign({}, form.val('searchForm'), params)
        Object.assign(params, user)
        Object.assign(params, cate)
        reloadTable(params);
    })

    // 监听搜索操作
    form.on('submit(data-search-btn)', function (data) {
        let user = eval('(' + $('#filterUser li.layui-menu-item-checked').attr('lay-options') + ')'),
            cate = eval('(' + $('#filterCate li.layui-menu-item-checked').attr('lay-options') + ')');
        Object.assign(data.field, user)
        Object.assign(data.field, cate)
        //执行搜索重载
        reloadTable(data.field)

        return false
    });

    $('button[type="reset"]').on('click', function () {
        let params = [],
            user = eval('(' + $('#filterUser li.layui-menu-item-checked').attr('lay-options') + ')'),
            cate = eval('(' + $('#filterCate li.layui-menu-item-checked').attr('lay-options') + ')');
        Object.assign(params, user)
        Object.assign(params, cate)
        reloadTable(params)
    })
});
