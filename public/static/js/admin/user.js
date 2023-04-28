layui.use(['jquery', 'form', 'table', 'laydate', 'upload', 'arronUtil'], function () {
    let $ = layui.jquery,
        form = layui.form,
        upload = layui.upload,
        laydate = layui.laydate,
        table = layui.table,
        arronUtil = layui.arronUtil;

    let controller = {
        appendAttachment: function (param = {
            source: '',
            name: '',
        }) {
            $('#newsImgBox').append($('<div />', {
                class: 'text-center position-relative mb-3',
            }).append($('<button />', {
                type: 'button',
                class: 'btn-close position-absolute',
                style: 'top: 0; right: 0;',
                title: '删除',
                click: function () {
                    let contract_attachment = $('input[name="contract_attachment"]'),
                        c = contract_attachment.val().split(',')
                    contract_attachment.val(c.filter(i => i !== param.source))
                    $(this).parent().remove()
                }
            })).append($('<img />', {
                src: param.source,
                alt: param.name || '',
                class: 'mx-auto d-block',
                style: 'max-height: 200px; width: auto;',
                click: function () {
                    let offset = {
                        x: 0,
                        y: 0,
                        left: 0,
                        top: 0,
                    }, zoom = 100
                    $('body').append($('<div />', {
                        class: 'bg-black p-5 position-absolute w-100 h-100',
                        style: 'z-index: 999999; top: 0; left: 0;',
                        click: function (e) {
                            e.preventDefault()
                            $(this).remove()
                            console.log('div click', e)
                        },
                    }).on({
                        dragstart: function (e) {

                        },
                        dragover: function (e) {
                            // console.log('drag over', e)
                        },
                        drop: function (e) {
                            e.preventDefault()
                            console.log('drop 2', offset)
                            $(this).children().css({
                                left: offset.left + 'px',
                                top: offset.top + 'px',
                            })
                        }
                    }).append($('<div />', {
                        class: 'position-absolute',
                        style: 'cursor: move;',
                        draggable: true,
                    }).on({
                        dragstart: function (e) {
                            offset.x = e.offsetX
                            offset.y = e.offsetY
                            console.log('drag start', $(this))
                        },
                        dragover: function (e) {
                            // console.log('drag over', e)
                            e.preventDefault()
                            // offset.x = e.offsetX - offset.x
                            // offset.y = e.offsetY - offset.y
                            // console.log('drag over', e.offsetX, e.offsetY)
                        },
                        drop: function (e) {
                            e.preventDefault()
                            offset.left += e.offsetX - offset.x
                            offset.top += e.offsetY - offset.y
                            $(this).css({
                                left: offset.left + 'px',
                                top: offset.top + 'px',
                            })
                        }
                    }).append($('<img />', {
                        class: '',
                        src: param.source,
                        alt: param.name || '',
                        click: function (e) {
                            // 阻止冒泡
                            window.event.cancelBubble = true
                            console.log('click image', e)
                        },
                        // mousemove: function (e) {
                        //     // console.log('mousemove', e)
                        //     // console.log($(this))
                        //     let s = $('html').width() / 2
                        //     if (e.offsetX < s) {
                        //         $(this).css({ cursor: "url('/static/images/left.ico') 0 32, auto" })
                        //     } else if (e.offsetX > s) {
                        //         $(this).css({ cursor: "url('/static/images/right.ico') 0 32, auto" })
                        //     }
                        // }
                    }).on({
                        mousewheel: function (e) {
                            // 放大缩小图片
                            if (e.originalEvent.wheelDeltaY > 0) {
                                zoom += 5
                                $(this).css({
                                    zoom: zoom + '%'
                                })
                            } else {
                                zoom -= 5
                                $(this).css({
                                    zoom: zoom + '%'
                                })
                            }
                        },
                    }))))
                }
            })))

            // document.onwheel = (e) => {
            //     console.log(e)
            // }
        },

        upload: function () {
            //上传图片
            let uploadInst = upload.render({
                elem: '#newsImg',
                url: arronUtil.url("/news/upload") ,
                multiple: true,
                accept: 'images', //只允许上传图片
                before: function (obj) {
                    //预读本地文件示例，不支持ie8
                    obj.preview(function (index, file, result) {
                        controller.appendAttachment({ source: result, name: file.name })
                    });
                },
                done: function (res) {
                    let op = { title: res.msg }
                    if (res.code === 1) {
                        op.icon = 'success'
                        let contract_attachment = $('input[name="contract_attachment"]')
                        contract_attachment.val(contract_attachment.val() + ',' + res.data?.savePath)
                    }
                    return arronUtil.Toast.fire(op)
                },
                error: function () {
                    //演示失败状态，并实现重传
                    let demoText = $('#demoText');
                    demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-xs demo-reload">重试</a>');
                    demoText.find('.demo-reload').on('click', function () {
                        uploadInst.upload();
                    });
                },
            });
        },

        reloadTable: function (params = {}) {
            let defaultParams = {},
                finalParams = Object.assign({}, defaultParams, form.val('searchForm'), params)
            //执行搜索重载
            table.reload('userTable', {
                page: {
                    curr: 1
                },
                where: finalParams
            }, false);
        },

        updateUserField: function (param) {
            $.post(arronUtil.url("/User/updateUser"), param, function (res) {
                let option = { title: res.msg };
                if (res.code === 1) {
                    option.icon = 'success'
                }

                arronUtil.Toast.fire(option)
            })
        },

        listener: function () {
            controller.upload()

            // 时间限制为现在
            let minDate = arronUtil.getDateTime();
            laydate.render({
                elem: '#testEndTime',
                type: 'datetime',
                min: minDate,
                value: arronUtil.getDateTime({ day: 1 }),
            })
            laydate.render({
                elem: '#contractDatetime',
                range: ['#startDate', '#endDate'],
                type: 'datetime',
                min: minDate
            })
            $('#isTest').on('click', function (e) {
                let endtimeItem = $('.test-endtime')
                if (e.target.checked) {
                    endtimeItem.removeClass('d-none')
                    $(this).val(1)
                } else {
                    endtimeItem.addClass('d-none')
                }
            })
            $('#offcanvas').on('show.bs.offcanvas', e => {
                let id = $(e.relatedTarget).data('id')
                let f = document.formEditor, title = ''

                $('#newsImgBox').html('')
                if (id) {
                    title = '编辑'
                    $.get(arronUtil.url("/User/edit"), { id: id }, res => {
                        if (res.code === 1) {
                            for (const element of f.elements) {
                                let name = element.name,
                                    nodeName = element.nodeName,
                                    el = $(e.currentTarget).find('[name="' + name + '"]');

                                if (nodeName === 'SELECT') {
                                    let v = ''
                                    if (name === 'call_type') {
                                        v = Object.keys(res.data['callTypeList']).find(key => parseInt(key) === res.data['userInfo'][name]);
                                    } else if (name === 'number_store_id') {
                                        v = res.data['userInfo']['companyXnumber']['number_store_id']
                                    }
                                    el.val(v)
                                } else {
                                    if (['is_test', 'status', 'talent_on'].includes(name)) {
                                        // el.val(1)
                                        if (res.data['userInfo'][name] === 1) {
                                            el.prop('checked', true)
                                            if ('is_test' === name) {
                                                $('.test-endtime').removeClass('d-none')
                                            }
                                        } else {
                                            el.prop('checked', false)
                                            if ('is_test' === name) {
                                                $('.test-endtime').addClass('d-none')
                                            }
                                        }
                                    } else {
                                        el.val(res.data['userInfo'][name])
                                    }
                                }

                                if (['username', 'corporation'].includes(name)) {
                                    el.prop('readonly', true)
                                }
                            }

                            let imgs = res.data['userInfo']['contract_attachment'].split(',')
                            $.each(imgs, (index, item) => {
                                item !== '' && controller.appendAttachment({
                                    source: item
                                })
                            })
                        } else {
                            arronUtil.Toast.fire({ title: res.msg })
                        }
                    })
                } else {
                    title = '添加'
                    f.reset()
                    $('[name="id"]').val('')
                    $('input[name=username], input[name=corporation]').prop('readonly', false)
                    $('.test-endtime').addClass('d-none')
                }

                $('.offcanvas-title').text(title)
            })

            $('form[name="formEditor"]').submit(function () {
                let formData = $(this).serializeArray(),
                    id = $('[name="id"]').val(),
                    url = id ? arronUtil.url("/User/edit") : arronUtil.url("/User/add"),
                    rate = $(this).find('input[name=rate]');
                if (rate.val() < 0) {
                    arronUtil.Toast.fire({
                        title: '费率不能小于0',
                        timer: 2000
                    }).then(() => {
                        rate.focus()
                    })

                    return false
                }

                $.post(url, formData, function (res) {
                    let option = { title: res.msg }
                    if (res.code === 1) {
                        option.icon = 'success'
                        option.timer = 2000
                        option.didDestroy = function () {
                            if (id) {
                                table.reload('userTable', {
                                    where: form.val('searchForm')
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

            table.init('currentTableFilter', {
                url: arronUtil.url("/user/getUserList"),
                id: 'userTable',
                toolbar: '#toolbarDemo',
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

            // 监听搜索操作
            form.on('submit(data-search-btn)', function (data) {
                //执行搜索重载
                controller.reloadTable()

                return false
            });
            form.on('select(tryUserFilter)', function (data) {
                controller.reloadTable()
            })
            form.on('select(userStatusFilter)', function (data) {
                controller.reloadTable();
            })
            $('button[type="reset"]').on('click', function () {
                document.searchForm.reset()
                controller.reloadTable()
            })

            form.on('switch(statusFilter)', function (obj) {
                let status = obj.elem.checked ? 1 : 0;
                let params = { id: obj.value, status: status };
                if (!status) {
                    arronUtil.Toast.fire({
                        title: '确认禁止吗？',
                        text: '禁止后，该账号下的所有拨号账号将无法正常使用',
                        toast: false,
                        showConfirmButton: true,
                        confirmButtonText: '确定',
                        timer: false,
                        showCloseButton: true,
                    }).then((r) => {
                        if (r.isConfirmed) {
                            controller.updateUserField(params)
                        } else {
                            obj.elem.checked = true
                            obj.othis.addClass('layui-form-onswitch').html('<em>正常</em><i></i>')
                        }
                    })
                } else {
                    controller.updateUserField(params)
                }
            })

            // 单元格编辑
            table.on('edit(currentTableFilter)', function (obj) {
                $.post(arronUtil.url("/User/edit"), obj.data, function (res) {
                    let option = { title: res.msg }
                    if (res.code === 1) {
                        option.icon = 'success'
                    }

                    arronUtil.Toast.fire(option)
                })
            });

            table.on('tool(currentTableFilter)', function (obj) {
                switch (obj.event) {
                    case 'checkUserList':
                        layer.open({
                            title: '用户列表',
                            type: 2,
                            shade: 0.2,
                            shadeClose: true,
                            anim: 2,
                            area: ['100%', '100%'],
                            content: arronUtil.url("/user/subUserList") + '?id=' + obj.data.id,
                        });
                        break;

                    case 'delete':
                        arronUtil.Toast.fire({
                            title: '确认删除吗？',
                            text: '删除账号后，其账号下所有关联账号将全部删除',
                            toast: false,
                            showConfirmButton: true,
                            confirmButtonText: '确定',
                            timer: false,
                            showCloseButton: true,
                        }).then((r) => {
                            if (r.isConfirmed) {
                                $.post(arronUtil.url('/User/del'), {id: obj.data.id}, function (res) {
                                    let option = { title: res.msg };
                                    if (res.code === 1) {
                                        option.icon = 'success'
                                        controller.reloadTable()
                                    }

                                    arronUtil.Toast.fire(option)
                                })
                            }
                        })
                        break;
                }
            });
        },
    }

    controller.listener()
});
