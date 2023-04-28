layui.use(['form', 'table', 'laydate', 'layer', 'excel', 'upload', 'arronUtil'], function () {
    let $ = layui.jquery,
        form = layui.form,
        arronUtil = layui.arronUtil,
        laydate = layui.laydate,
        layer = layui.layer,
        excel = layui.excel,
        upload = layui.upload,
        table = layui.table;

    const REQUEST_CONFIG = {
        EDIT_URL: arronUtil.url('/News/edit'),
        ADD_URL: arronUtil.url('/News/add'),
        DELETE_URL: arronUtil.url('/News/delete'),
    }

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

        upload: function () {
            //上传图片
            let uploadInst = upload.render({
                elem: '#newsImg',
                url: arronUtil.url("/news/upload"),
                before: function (obj) {
                    //预读本地文件示例，不支持ie8
                    obj.preview(function (index, file, result) {
                        $('#newsImgBox').append($('<img />', {
                            class: 'img-thumbnail',
                            src: result
                        }))
                    });
                },
                done: function (res) {
                    let o = {
                        title: res.msg
                    }
                    if (res.code === 1) {
                        o.icon = 'success'
                        $('input[name="cover_img"]').val(res.data.savePath);
                    }

                    return arronUtil.Toast.fire(o)
                },
                error: function () {
                    //演示失败状态，并实现重传
                    let demoText = $('#demoText');
                    demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-xs demo-reload">重试</a>');
                    demoText.find('.demo-reload').on('click', function () {
                        uploadInst.upload();
                    });
                },
            })
        },

        delete: function (ids) {
            $.post(REQUEST_CONFIG.DELETE_URL, {ids: ids}, function (res) {
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

        getFilterParams: function () {
            return form.val('searchForm')
        },

        listener: function () {
            controller.upload()

            // 编辑框
            const SUMMERNOTE_CONFIG = {
                placeholder: '请输入新闻内容',
                tabsize: 2,
                height: 400,
                focus: false,
            }
            const SUMMERNOTE_TARGET = '#summernote'
            $(SUMMERNOTE_TARGET).summernote(SUMMERNOTE_CONFIG)

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
                                    el = $(e.currentTarget).find('[name="' + name + '"]');

                                if (name === 'is_top') {
                                    el.prop('checked', res.data[name] === 1)
                                } else if (name === 'content') {
                                    $(SUMMERNOTE_TARGET).summernote('code', res.data[name])
                                } else {
                                    el.val(res.data[name])
                                }
                            }

                            if (res.data.cover_img) {
                                $('#newsImgBox').html('')
                                $('#newsImgBox').append($('<img />', {
                                    class: 'img-thumbnail',
                                    src: res.data.cover_img
                                }))
                            }
                        }
                    })
                } else {
                    title = '添加'
                    $('[name="id"]').val('')
                    f.reset()
                    $('#newsImgBox').html('')

                    if (!$(SUMMERNOTE_TARGET).summernote('isEmpty')) {
                        $(SUMMERNOTE_TARGET).summernote('reset', SUMMERNOTE_CONFIG)
                    }
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
                        option.didDestroy = function () {
                            if (id) {
                                table.reload('newsTable', {
                                    where: controller.getFilterParams()
                                })
                            } else {
                                controller.reloadTable()
                            }

                            $('[data-bs-dismiss="offcanvas"]').click()
                        }
                    }

                    arronUtil.Toast.fire(option)
                })

                return false
            })

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
