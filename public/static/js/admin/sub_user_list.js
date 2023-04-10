layui.use(['form', 'table', 'arronUtil'], function () {
    let $ = layui.jquery,
        form = layui.form,
        arronUtil = layui.arronUtil,
        table = layui.table;

    let controller = {
        reloadTable: function (params = {}) {
            table.reload('userTable', {
                page: {
                    curr: 1
                },
                where: params
            })
        },

        listener: function () {

            table.init('currentTableFilter', {
                url: arronUtil.url("/user/getSubUserList") + '?company_id=' + company_id,
                id: 'userTable',
                //异步请求，格式化数据
                parseData: function (res) {
                    return {
                        'code': 0,
                        'msg': '',
                        'data': res.rows,
                        'count': res.total
                    }
                },
                page: {
                    limits: [10, 20, 30, 40],
                    limit: 10,
                },
                skin: 'line',
                even: true,
            })

            // 监听搜索操作
            form.on('submit(data-search-btn)', function (data) {
                if (data.field.username === "" && data.field.phone === "") {
                    arronUtil.Toast.fire({
                        title: '搜索内容不能为空'
                    })
                    return false
                }
                controller.reloadTable(data.field);
                return false;
            });

            $('button[type="reset"]').on('click', function () {
                controller.reloadTable()
            })

            /**
             * toolbar监听事件
             */
            table.on('toolbar(currentTableFilter)', function (obj) {
                if (obj.event === 'add') {  // 监听添加操作
                    $.post(arronUtil.url("/User/checkLimitUser"), {}, function (res) {
                        if (res.code === 1) {
                            let index = layer.open({
                                title: '开通账号',
                                type: 2,
                                shade: 0.2,
                                maxmin: true,
                                shadeClose: true,
                                anim: 2,
                                area: ['100%', '100%'],
                                content: arronUtil.url("/user/add"),
                            });
                            $(window).on("resize", function () {
                                layer.full(index);
                            });
                        } else {
                            arronUtil.Toast.fire({
                                title: res.msg
                            })
                        }
                    })
                } else if (obj.event === 'delete') {  // 监听删除操作
                    let checkStatus = table.checkStatus('currentTableId'),
                        data = checkStatus.data;
                    if(data === '' || data === 'undefind'){
                        arronUtil.Toast.fire({
                            title: '请勾选需要删除的数据'
                        });
                        return false;
                    }else{
                        let ids=[],url='';
                        $(data).each(function (i) {
                            ids.push(data[i].id);
                        });
                        console.log(ids);
                        $.post(url,ids,function () {
                            //todo
                        });
                    }
                }
            });

            table.on('tool(currentTableFilter)', function (obj) {
                if (obj.event === 'edit') {
                    let index = layer.open({
                        title: '编辑账号',
                        type: 2,
                        shade: 0.2,
                        maxmin:true,
                        shadeClose: true,
                        area: ['100%', '100%'],
                        content: arronUtil.url("/user/edit") + '?id=' + obj.data.id,
                    });
                    $(window).on("resize", function () {
                        layer.full(index);
                    });
                    return false;
                } else if (obj.event === 'delete') {
                    let id = obj.data.id;
                    layer.confirm('真的删除行么？删除后不可恢复', function (index) {
                        $.post(arronUtil.url("/User/del"), { id: id }, function (res) {
                            let option = { title: res.msg };
                            if (res.code === 1) {
                                option.icon = 'success'
                                obj.del()
                            }

                            arronUtil.Toast.fire(option).then(function () {
                                layer.closeAll();
                            })
                        })

                    });
                }
            });
        },
    }

    controller.listener()
});
