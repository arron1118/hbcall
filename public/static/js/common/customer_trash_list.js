layui.use(['layer', 'miniTab', 'jquery', 'table', 'laydate', 'arronUtil'], function () {
    let $ = layui.jquery,
        layer = layui.layer,
        arronUtil = layui.arronUtil,
        miniTab = layui.miniTab,
        laydate = layui.laydate,
        table = layui.table;

    const REQUEST_CONFIG = {
        DISTRIBUTION_URL: arronUtil.url('/customer/distribution'),
    }

    let controller = {
        listener: function () {

            miniTab.listen();
            // 客户列表
            table.init('currentTableFilter', {
                id: 'customerTable',
                url: arronUtil.url("/Customer/trash_list"),
                method: 'post',
                where: {
                    type: type,
                },
                height: 750,
                toolbar: '#currentTableBar',
                // height: 680,
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
            });

            table.on('toolbar(currentTableFilter)', function (obj) {
                let checkStatus = table.checkStatus('customerTable').data;
                let ids = '',
                    temp = [];

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

                if (obj.event === 'distribution') {
                    let html = '',
                        params = { ids: ids },
                        title = '',
                        url, selector;

                    url = REQUEST_CONFIG.DISTRIBUTION_URL
                    selector = 'select-user'
                    title = '选择用户'

                    $.each(user, (index, item) => {
                        html += `<li class="list-group-item list-group-item-action ${selector}" data-id="${item.id}" style="cursor: pointer;">${item.username}</li>`
                    });

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
                                params.user_id = $(this).data('id')

                                $.post(url, params, function (res) {
                                    let op = { title: res.msg }
                                    if (res.code === 1) {
                                        op.icon = 'success'
                                        op.timer = 1500
                                        op.didDestroy = () => {
                                            table.reload('customerTable')
                                        }
                                    }

                                    arronUtil.Toast.fire(op)
                                })
                            })
                        }
                    })
                }
            });
        },
    }

    controller.listener()
});
