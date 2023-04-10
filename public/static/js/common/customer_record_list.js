layui.use(['layer', 'miniTab', 'jquery', 'table', 'laydate', 'arronUtil'], function () {
    let $ = layui.jquery,
        layer = layui.layer,
        arronUtil = layui.arronUtil,
        miniTab = layui.miniTab,
        laydate = layui.laydate,
        table = layui.table;

    let controller = {
        listener: function () {

            miniTab.listen();
            // 客户列表
            table.init('currentTableFilter', {
                id: 'recordTable',
                url: arronUtil.url("/CustomerRecord/getCustomerRecordList"),
                method: 'post',
                where: {
                    customer_id: customer_id
                },
                toolbar: '#currentTableBar',
                // height: 680,
                defaultToolbar: ['filter', 'exports', 'print', {
                    title: '提示',
                    layEvent: 'LAYTABLE_TIPS',
                    icon: 'layui-icon-tips'
                }],
                //异步请求，格式化数据
                parseData: function (res) {
                    return {
                        'code': 0,
                        'msg': '',
                        'data': res.data,
                        'count': res.total
                    }
                },
                page: {
                    limits: [15, 30, 45, 60],
                    limit: 15,
                },
                skin: 'line',
                even: true,
            });

            table.on('tool(currentTableFilter)', function (obj) {
                let id = obj.data.id;
                switch (obj.event) {
                    case 'edit':
                        layer.open({
                            type: 2,
                            title: '编辑',
                            area: ['700px', '600px'],
                            content: arronUtil.url("/CustomerRecord/edit") + '?id=' + id
                        });
                        break;
                    case 'del':
                        layer.confirm('确定删除么？', { title: '温馨提示'}, function () {
                            $.post(arronUtil.url("/CustomerRecord/del"), { id: id }, function (res) {
                                let option = { title: res.msg }
                                if (res.code === 1) {
                                    option.icon = 'success';
                                    table.reload('recordTable', {
                                        page: {
                                            curr: 1
                                        }
                                    })
                                }

                                arronUtil.Toast.fire(option);
                            })
                        })
                        break;
                }
            });
            table.on('toolbar(currentTableFilter)', function (obj) {
                switch (obj.event) {
                    case 'add':
                        layer.open({
                            type: 2,
                            title: '添加',
                            area: ['700px', '600px'],
                            content: arronUtil.url('/CustomerRecord/add') + '?customer_id=' + customer_id
                        });
                        break;
                }
            })

            // 回访记录模态
            const recordModal = new bootstrap.Modal('#recordModal')
            const recordForm = $('form[name=record-add-form]')
            recordForm.find('input[name=customer_id]').val(customer_id)
            $('#recordModal').on('show.bs.modal', function (e) {
                let id = $(e.relatedTarget).data('id')
                let f = document['record-add-form'], title = ''
                if (id) {
                    title = '编辑'
                    $.get(arronUtil.url('/CustomerRecord/edit'), { id: id }, function (res) {
                        if (res.code === 1) {
                            for (const element of f.elements) {
                                let name = element.name,
                                    el = $(e.currentTarget).find('[name="' + name + '"]');
                                el.val(res.data[name])
                            }
                        }
                    })
                } else {
                    $('[name="id"]').val('')
                    title = '添加'
                    // 重置表单
                    f.reset()
                }

                $('.offcanvas-title').text(title)
                laydate.render({
                    elem: '#record-datetime',
                    type: 'datetime',
                })
            })
            recordForm.submit(function (e) {
                let formData = $(this).serializeArray(),
                    id = $('[name="id"]').val(),
                    url = id ? arronUtil.url("/CustomerRecord/edit") : arronUtil.url("/CustomerRecord/add")

                $.post(url, formData, function (res) {
                    let op = { title: res.msg }
                    if (res.code === 1) {
                        op.icon = 'success'
                        recordModal.hide()

                        table.reload('recordTable', {
                            page: {
                                curr: 1
                            }
                        })
                    }

                    arronUtil.Toast.fire(op)
                })

                return false
            })
        },
    }

    controller.listener()
});
