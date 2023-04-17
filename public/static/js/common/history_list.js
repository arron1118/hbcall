layui.use(['form', 'table', 'laydate', 'dropdown', 'arronUtil'], function () {
    let $ = layui.jquery,
        form = layui.form,
        laydate = layui.laydate,
        table = layui.table,
        arronUtil = layui.arronUtil,
        dropdown = layui.dropdown;

    let controller = {
        reloadTable: function (params = {}) {
            let defaultParams = eval('(' + $('#filterUser li.layui-menu-item-checked').attr('lay-options') + ')')
            let p = Object.assign({}, form.val('searchForm'), defaultParams)
            Object.assign(p, params)
            //执行搜索重载
            table.reload('currentTableId', {
                page: {
                    curr: 1
                },
                where: p
            });
        },

        listener: function () {

            //菜单点击事件
            dropdown.on('click(filterUser)', function(options){
                controller.reloadTable()
            });

            laydate.render({
                elem: '#datetime',
                range: ['#startDate', '#endDate'],
                type: 'datetime',
                done: function (value) {
                    let d = value.split(' - ')
                    controller.reloadTable({
                        startDate: d[0],
                        endDate: d[1]
                    })
                }
            });

            table.init('currentTableFilter', {
                url: arronUtil.url("/HbCall/getHistoryList"),
                method: 'post',
                toolbar: '#syncCallHistory',
                height: 725,
                defaultToolbar: ['filter', 'exports', 'print', {
                    title: '提示',
                    layEvent: 'LAYTABLE_TIPS',
                    icon: 'layui-icon-tips'
                }],
                page: {
                    limits: [15, 30, 45, 60, 90, 450, 600],
                    limit: 15,
                },
                even: true,
                skin: 'line',
            })

            $('button[type="reset"]').on('click', function () {
                document.searchForm.reset()
                controller.reloadTable();
            })

            form.on('select(filterOperate)', function (data) {
                controller.reloadTable()
            })

            // 监听搜索操作
            form.on('submit(data-search-btn)', function (data) {
                controller.reloadTable();

                return false;
            });

            table.on('toolbar(currentTableFilter)', function (obj) {
                if (obj.event === 'syncCallHistory') {
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
                }
            })
        },
    }

    controller.listener()
});
