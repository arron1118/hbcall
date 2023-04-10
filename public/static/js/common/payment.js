layui.use(['form', 'table', 'jquery', 'laydate', 'arronUtil'], function () {
    let $ = layui.jquery,
        form = layui.form,
        table = layui.table,
        arronUtil = layui.arronUtil,
        laydate = layui.laydate;

    let controller = {

        // 支付页面
        pay: function (payType, amount, payNo = '') {
            layer.open({
                title: '支付',
                type: 2,
                area: ['100%', '100%'],
                content: arronUtil.url("/payment/pay") + '?payType=' + payType + '&amount=' + amount + '&payno=' + payNo,
                btn: false,
                cancel: function (index, layero) {
                    layer.close(index);
                    table.reload('paymentTable');
                }
            });
        },

        reloadTable: function (params) {
            table.reload('paymentTable', {
                where: params,
                page: {
                    curr: 1
                }
            })
        },

        listener: function () {

            laydate.render({
                elem: '#datetime',
                range: ['#startDate', '#endDate'],
                type: 'datetime',
                done: function (value) {
                    let d = value.split(' - ')
                    let params = Object.assign({}, form.val('searchForm'), {startDate: d[0], endDate: d[1] ? d[1] : ''})
                    controller.reloadTable(params)
                }
            });

            table.init('currentTableFilter', {
                id: 'paymentTable',
                url: arronUtil.url("/payment/getOrderList"),
                method: 'post',
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
                    limits: [15, 20, 25, 30],
                    limit: 15
                },
                skin: 'line',
                even: true,
            })

            table.on('tool(currentTableFilter)', function (obj) {
                if (obj.event === 'gopay') {
                    let payType = obj.data.pay_type === '微信' ? 1 : 2;
                    controller.pay(payType, obj.data.amount, obj.data.payno);
                }
            })

            // 监听操作
            form.on('submit(data-search-btn)', function (data) {
                if (data.field.corporation === '' && data.field.datetime === '') {
                    arronUtil.Toast.fire({
                        title: '请输入搜索内容'
                    })
                    return false
                }
                controller.reloadTable(data.field)
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

                controller.pay(result.pay_type, result.money);
                return false;
            });


            form.on('select(payTypeFilter)', function (data) {
                let params = [];
                params[data.elem.name] = data.value
                params = Object.assign({}, form.val('searchForm'), params)
                controller.reloadTable(params);
            })
            form.on('select(payStateFilter)', function (data) {
                let params = [];
                params[data.elem.name] = data.value
                params = Object.assign({}, form.val('searchForm'), params)
                controller.reloadTable(params);
            })
            form.on('select(corporationFilter)', function (data) {
                let params = [];
                params[data.elem.name] = data.value
                params = Object.assign({}, form.val('searchForm'), params)
                controller.reloadTable(params);
            })

            $('button[type=reset]').on('click', function (e) {
                controller.reloadTable({})
            })
        }
    }

    controller.listener()
});
