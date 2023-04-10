layui.use(['form', 'table', 'laydate', 'dropdown', 'arronUtil'], function () {
    let $ = layui.jquery,
        form = layui.form,
        laydate = layui.laydate,
        table = layui.table,
        arronUtil = layui.arronUtil,
        dropdown = layui.dropdown;

    let controller = {
        reloadTable: function (params = {}) {
            //执行搜索重载
            table.reload('currentTableId', {
                page: {
                    curr: 1
                },
                where: params
            });
        },

        listener: function () {

            //菜单点击事件
            dropdown.on('click(filterUser)', function(options){
                controller.reloadTable(Object.assign({}, form.val('searchForm'), options))
            });

            laydate.render({
                elem: '#datetime',
                range: ['#startDate', '#endDate'],
                type: 'datetime',
                done: function (value) {
                    let d = value.split(' - ')
                    let data = Object.assign({}, form.val('searchForm'), { startDate: d[0], endDate: d[1] })
                    controller.reloadTable(data)
                }
            });

            table.init('currentTableFilter', {
                url: arronUtil.url("/HbCall/getHistoryList"),
                method: 'post',
                toolbar: '#syncCallHistory',
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
                    limits: [15, 30, 45, 60, 90, 450, 600],
                    limit: 15,
                },
                even: true,
                skin: 'line',
            })

            $('button[type="reset"]').on('click', function () {
                let params = [],
                    user = eval('(' + $('#filterUser li.layui-menu-item-checked').attr('lay-options') + ')')
                Object.assign(params, user)
                controller.reloadTable(params);
            })

            form.on('select(filterUser)', function (data) {
                controller.reloadTable(Object.assign({}, form.val('searchForm'), { user_id: data.value }))
            })

            form.on('select(filterOperate)', function (data) {
                let val = form.val('searchForm')
                if (val.duration !== '' && val.operate !== '') {
                    controller.reloadTable(val)
                }
            })

            // 监听搜索操作
            form.on('submit(data-search-btn)', function (data) {
                let user = eval('(' + $('#filterUser li.layui-menu-item-checked').attr('lay-options') + ')')
                Object.assign(data.field, user)
                controller.reloadTable(data.field);

                return false;
            });

            table.on('toolbar(currentTableFilter)', function (obj) {
                switch (obj.event) {
                    case 'syncCallHistory':
                        let load = layer.load();
                        $.post(arronUtil.url('/HbCall/updateCallHistory'), function (res) {
                            layer.close(load)
                            let errorHtml = '';
                            if (res.data.error > 0) {
                                errorHtml += '<h5>失败</h5>'
                                $.each(res.data.errList, function (index, item) {
                                    errorHtml += '<p>' + index + '：' + item + '</p>'
                                })
                                errorHtml += '<br />'
                            }

                            arronUtil.Toast.fire({
                                toast: false,
                                timer: false,
                                html: '总共：' + res.data.total + ' 成功：' + res.data.success + ' 失败：' + res.data.error + '<br /><br />' + errorHtml,
                                icon: 'success',
                                showConfirmButton: true,
                                confirmButtonText: '确定'
                            });
                        })
                        break;
                }
            })
        },
    }

    controller.listener()
});
