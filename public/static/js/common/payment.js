layui.use(['form', 'table', 'jquery', 'laydate', 'arronUtil', 'dropdown'], function () {
    let $ = layui.jquery,
        form = layui.form,
        dropdown = layui.dropdown,
        table = layui.table,
        arronUtil = layui.arronUtil,
        laydate = layui.laydate;

    let controller = {
        reloadTable: function (params = {}) {
            let defaultParams = eval('(' + $('#filterUser li.layui-menu-item-checked').attr('lay-options') + ')')
            let p = Object.assign({}, defaultParams, form.val('searchForm'))
            Object.assign(p, params)
            table.reload('paymentTable', {
                where: p,
                page: {
                    curr: 1
                }
            })
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
                id: 'paymentTable',
                url: arronUtil.url("/payment/getOrderList"),
                method: 'post',
                parseData: function (res) {
                    return {
                        'code': 0,
                        'msg': '',
                        'data': res.rows,
                        'count': res.total
                    }
                },
                page: {
                    limits: [15, 20, 25, 30],
                    limit: 15
                },
                skin: 'line',
                even: true,
            })

            table.on('tool(currentTableFilter)', function (obj) {
                if (obj.event === 'gopay') {
                    arronUtil.pay(controller, obj.data.pay_type, obj.data.amount, obj.data.payno);
                }
            })

            // 监听操作
            form.on('submit(data-search-btn)', function (data) {
                controller.reloadTable()
                return false;
            });

            form.on('submit(data-money-btn)', function (data) {
                let result = data.field;
                if (!result.money) {
                    arronUtil.Toast.fire({
                        title: '请输入要充值的金额！'
                    })
                    return false;
                }

                if (result.money <= 0) {
                    arronUtil.Toast.fire({
                        title: '请输入正确的金额！'
                    })
                    return false;
                }

                arronUtil.pay(controller, parseInt(result.pay_type), result.money);
                return false;
            });


            form.on('select(payTypeFilter)', function (data) {
                controller.reloadTable();
            })
            form.on('select(payStateFilter)', function (data) {
                controller.reloadTable();
            })

            $('button[type=reset]').on('click', function (e) {
                document.searchForm.reset()
                controller.reloadTable()
            })
        }
    }

    controller.listener()
});
