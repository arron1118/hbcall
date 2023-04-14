layui.use(['form', 'table', 'laydate', 'layer', 'excel', 'upload', 'arronUtil'], function () {
    let $ = layui.jquery,
        form = layui.form,
        arronUtil = layui.arronUtil,
        laydate = layui.laydate,
        layer = layui.layer,
        excel = layui.excel,
        upload = layui.upload,
        table = layui.table;

    let controller = {
        uploadExcel: function (files) {
            try {
                excel.importExcel(files, {
                    // 读取数据的同时梳理数据
                    fields: {
                        title: 'A',
                        cover_img: 'B',
                        author: 'C',
                        source: 'D',
                        lId: 'E',
                        keyword: 'F',
                        intro: 'G',
                        content: 'H',
                        power: 'I',
                    }
                }, function (data) {
                    data = Object.values(data);

                    let arr = [], temp = [];
                    for (let item of data) {
                        for (let value in item) {
                            temp.push(item[value]);
                        }
                    }

                    for (const element of temp) {
                        if (element.length > 0) {
                            for (let i = 1; i < element.length; i++) {
                                let tt = {
                                    cId: 0,
                                    title: element[i].title,
                                    cover_img: element[i].cover_img,
                                    author: element[i].author,
                                    source: element[i].source,
                                    lId: element[i].lId,
                                    keyword: element[i].keyword,
                                    intro: element[i].intro,
                                    content: element[i].content,
                                    power: element[i].power,
                                };
                                arr.push(tt);
                            }
                        }
                    }

                    $.ajax({
                        async: false,
                        url: arronUtil.url("/news/import"),
                        type: 'post',
                        dataType: "json",
                        contentType: "application/x-www-form-urlencoded",
                        data: {
                            data: arr
                        },
                        success: function (res) {
                            $('input[name="file"]').val('');

                            let option = { title: res.msg }
                            if (res.code === 1) {
                                option.icon = 'success'

                                //表格导入成功后，重载表格
                                controller.reloadTable()
                            }

                            arronUtil.Toast.fire(option)
                        },
                        error: function (msg) {
                            arronUtil.Toast.fire({
                                title: '请联系管理员!!!'
                            })
                        }
                    });
                });
            } catch (e) {
                arronUtil.Toast.fire({
                    title: '请联系管理员!!!'
                })
            }
        },

        delete: function (ids) {
            $.post(arronUtil.url('/news/delete'), {ids: ids}, function (res) {
                arronUtil.Toast.fire({
                    title: res.msg,
                    icon: 'success'
                })

                table.reload('newsTable');
            });
        },

        reloadTable: function (params = {}) {
            table.reload('newsTable', {
                page: {
                    curr: 1
                },
                where: params
            });
        },

        listener: function () {

            laydate.render({
                elem: '#newsDate'
            });

            table.init('newsTableFilter', {
                url: arronUtil.url("/news/index"),
                id: 'newsTable',
                toolbar: '#toolbarNews',
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
                        'count': res.count
                    }
                },
                page: {
                    limits: [15, 30, 45, 60],
                    limit: 15,
                },
                skin: 'line',
                even: true,
            })

            upload.render({
                elem: '#importExcel',
                url: '',
                accept: 'file', //普通文件
                exts: 'xls|excel|xlsx', //导入表格
                auto: false,  //选择文件后不自动上传
                before: function (obj) {
                    layer.load(); //上传loading
                },
                // 选择文件回调
                choose: function (obj) {
                    let files = obj.pushFile();
                    let fileArr = Object.values(files);// 注意这里的数据需要是数组，所以需要转换一下
                    // 用完就清理掉，避免多次选中相同文件时出现问题
                    for (let index in files) {
                        if (files.hasOwnProperty(index)) {
                            delete files[index];
                        }
                    }
                    controller.uploadExcel(fileArr); // 如果只需要最新选择的文件，可以这样写： uploadExcel([files.pop()])
                },
                error: function () {
                    setTimeout(function () {
                        arronUtil.Toast.fire({
                            title: '上传失败'
                        })
                        //关闭所有弹出层
                        layer.closeAll(); //疯狂模式，关闭所有层
                    }, 1000);
                }
            });

            // 监听搜索操作
            form.on('submit(data-search-btn)', function (data) {
                //执行搜索重载
                controller.reloadTable(data.field)

                return false;
            });

            /**
             * toolbar监听事件
             */
            // 表头
            table.on('toolbar(newsTableFilter)', function (obj) {
                switch (obj.event) {
                    case 'add':
                        let index = layer.open({
                            title: '发布新闻',
                            type: 2,
                            shade: 0.2,
                            maxmin: true,
                            shadeClose: true,
                            area: ['100%', '100%'],
                            content: arronUtil.url("/news/add"),
                        });
                        $(window).on("resize", function () {
                            layer.full(index);
                        });
                        break;

                    case 'importExcel':
                        $('#importExcel').click();
                        break;

                    case 'delete':  // 监听删除操作
                        let checkStatus = table.checkStatus('newsTable'),
                            data = checkStatus.data;
                        if (data.length < 1) {
                            arronUtil.Toast.fire({
                                title: '请勾选需要删除的数据'
                            })
                            return false;
                        }

                        let ids = [];
                        $(data).each(function (i) {
                            ids.push(data[i].id);
                        });

                        arronUtil.Toast.fire( { title: '删除 ' + data.length + ' 条数据？确定么' }).then(function (val) {
                            if (val.isConfirmed) {
                                controller.delete(ids)
                            }
                        });
                        break;
                }
            });

            //表内
            table.on('tool(newsTableFilter)', function (obj) {
                switch (obj.event) {
                    case 'edit':
                        let index = layer.open({
                            title: '编辑新闻',
                            type: 2,
                            shade: 0.2,
                            maxmin: true,
                            shadeClose: true,
                            area: ['100%', '100%'],
                            content: arronUtil.url("/news/edit") + '?id=' + obj.data.id,
                        });
                        $(window).on("resize", function () {
                            layer.full(index);
                        });
                        break;
                    case 'del':
                        arronUtil.Toast.fire({
                            title: '真的删除这条数据么',
                            showConfirmButton: true,
                            confirmButtonText: '确定',
                            toast: false,
                            icon: 'question',
                            timer: false,
                        }).then(function (val) {
                            if (val.isConfirmed) {
                                controller.delete(obj.data.id)
                            }
                        });
                        break;
                }
            });
        },
    }


    controller.listener()
});
