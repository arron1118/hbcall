layui.use(['layer', 'miniTab', 'element', 'excel', 'upload', 'table', 'laydate'], function () {
    let $ = layui.jquery,
        layer = layui.layer,
        miniTab = layui.miniTab,
        element = layui.element,
        excel = layui.excel,
        table = layui.table,
        laydate = layui.laydate,
        upload = layui.upload;

    miniTab.listen();

    // 回访记录模态
    const recordModal = new bootstrap.Modal('#recordModal')
    const recordForm = $('form[name=record-add-form]')
    $('#recordModal').on('show.bs.modal', function (e) {
        // 重置表单
        document['record-add-form'].reset()
        laydate.render({
            elem: '#record-datetime',
            type: 'datetime',
        })
    })
    recordForm.submit(function (e) {
        $.post('/CustomerRecord/add', $(this).serialize(), function (res) {
            let op = { title: res.msg }
            if (res.code === 1) {
                op.icon = 'success'
                recordModal.hide()
            }

            Toast.fire(op)
        })

        return false
    })
    // 提示信息模态
    const infoModal = new bootstrap.Modal('#infoModal')
    // 输入框获取焦点
    let inputFocus = function () {
        $("#input").focus();
    };
    inputFocus();

    // 移除用户右键菜单
    let removeContextmenu = function () {
        $('#right-contextmenu').remove()
    }

    // 监听键盘、鼠标、滚轮事件
    $(window).on('keydown mousewheel mousedown', function (e) {
        let target = $(e.target.parentElement)
        if (target.hasClass('customer-context-menu')) {
            target.click()
        } else {
            removeContextmenu()
        }
    })

    let showCallInfoTimes = utils.cookie('showCallInfoTimes');
    // 首次显示呼叫提示信息
    if (!showCallInfoTimes || parseInt(showCallInfoTimes) === 0) {
        infoModal.show(document.getElementById('infoToggleModal'))
        utils.cookie('showCallInfoTimes', 1)
    }

    let callinginput = $('.callinginput');
    let customerContainer = $('.customer-list');
    // 生成拨号按键
    let btnSize = 4;    // 按键大小 单位：1rem = 16px
    let buildPhoneNumber = function () {
        let phoneNumbersDiv = $('#phone-numbers');
        let isPhoneIcon = false;
        let cssClass = 'align-middle cursor rounded-circle d-inline-block';
        let css = {
            width: btnSize + 'rem',
            height: btnSize + 'rem',
            lineHeight: btnSize + 'rem',
            cursor: 'pointer',
            backgroundColor: "rgb(228, 228, 228)"
        };
        for (let i = 1; i < 14; i++) {
            let col = $('<div />', {
                class: 'col text-center'
            });
            let text = i;

            // * 键
            if (i === 10) {
                text = `<i class="fa fa-asterisk"></i>`
                col.append($('<span />', {
                    class: cssClass + ' fs-4'
                }).css(css).html(text));
            }

            if (i === 11) {
                text = 0;
            }

            // 删除键
            if (i === 12) {
                text = `<i class="fa fa-backspace text-sm-center small"></i>`;
                col.append($('<span />', {
                    class: cssClass + ' fs-3'
                }).css(css).html(text).on('click', function (e) {
                    callinginput.val('');
                    inputFocus();
                }));
            }

            // 拨号键
            if (i === 13) {
                isPhoneIcon = true;
                text = `<i class="fa fa-phone"></i>`;
                css.backgroundColor = 'rgb(8, 86, 246)';
                css.color = '#fff';
                col.append($('<span />', {
                    class: cssClass + ' fs-2'
                }).css(css).html(text).on('click', function (e) {
                    if (callinginput.val().length < 11 || !utils.isPhone(callinginput.val())) {
                        Toast.fire('请输入正确的号码');
                        return false;
                    }

                    makeCall({ phone: callinginput.val() }, createItem({called_number: callinginput.val()}));
                }));
            } else {
                isPhoneIcon = false;
            }

            if (Number.isFinite(text)) {
                col.append($('<span />', {
                    class: cssClass,
                    'data-value': text
                }).css(css).html(text).on('click', function (e) {
                    if (callinginput.val().length < 11) {
                        callinginput.val(callinginput.val() + $(this).data('value'));//输入框值,val()里面
                    }
                }));
            }

            phoneNumbersDiv.append(col);
        }
    };
    buildPhoneNumber();

    callinginput.on('keydown', function (e) {
        // e.preventDefault();
        // 大键盘||小键盘
        if ((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 96 && e.keyCode <= 106)) {
            if (callinginput.val().length >= 11) {
                return false
            }
        } else if (e.keyCode === 13) {
            if ($(this).val().length < 11 || !utils.isPhone($(this).val())) {
                Toast.fire('请输入正确的号码');
                return false;
            }

            makeCall({ phone: $(this).val() }, createItem({called_number: $(this).val()}));
        }
    })

    let makeCall = function (params = { phone: '', customerId: 0 }, addList = null, customerCalled = null) {
        $.post('/hbcall/makeCall', { mobile: params.phone, customerId: params.customerId }, function (res) {
            if ((res.code == '1000' || res.code == '0000' || res.code == '1003') && res.code !== 0) {
                if (customerCalled) {
                    $(customerCalled).addClass('text-black-50')

                    let callTime = utils.getDateTime()
                    if ($(customerCalled).find('.called-customer').length === 0) {
                        $(customerCalled).find('.click-call').before($('<div />', {
                            class: 'called-customer text-end col-3 ',
                            text: callTime
                        }))
                    } else {
                        $(customerCalled).find('.called-customer').text(callTime)
                    }
                    customerContainer.append(customerCalled)
                }
                // 添加历史列表
                if (addList) {
                    let hl = $('.call-history-list .list-group');
                    hl.prepend($(addList));
                    hl.children().last().remove();
                }

                utils.caller.success(res)
            } else {
                utils.caller.fail(res)
            }
        });
    }

    // 创建历史列表
    let createItem = function (item) {
        return $('<div />', {
            class: 'row p-2 mx-1 border-bottom call-history-item',
            style: 'cursor: pointer',
            click: function () {
                item.create_time = utils.getDateTime()
                makeCall({ phone: item.called_number, customerId: item.customer ? item.customer.id : 0 }, createItem(item))
            },
            mouseover: function () {
                $(this).children('.click-call').removeClass('d-none')
                $(this).children('.call-datetime').addClass('d-none')
                $(this).children('.phone').text(item.called_number)
            },
            mouseout: function () {
                $(this).children('.click-call').addClass('d-none')
                $(this).children('.call-datetime').removeClass('d-none')
                $(this).children('.phone').text(item.called_number.toString().replace(item.called_number.toString().substring(3, 7), '****'))
            }
        }).append($('<div />', {
            class: 'col-6 text-truncate',
            text: item.customer ? item.customer.title : '',
        })).append($('<span />', {
            class: 'phone col-3',
            text: item.called_number.toString().replace(item.called_number.toString().substring(3, 7), '****')
        })).append($('<div />', {
            class: 'click-call col-3 text-primary text-end',
            text: '点击拨号'
        })).append($('<div />', {
            class: 'text-muted col-3 text-end call-datetime',
            text: item.create_time
        }))
    }
    // 创建客户列表
    let createCustomerItem = function (item, isCalled = false) {
        let aClass = 'call-history-item row p-2 mx-1 border-bottom ';
        if (isCalled) {
            aClass += ' text-black-50'
        }
        let listItem = $('<div />', {
            class: aClass,
            style: 'cursor: pointer',
            click: function () {
                makeCall({ phone: item.phone, customerId: item.id }, createItem({ called_number: item.phone, customer: { id: item.id, title: item.title }, create_time: utils.getDateTime() }), this)
            },
            mouseover: function () {
                $(this).children('.click-call').removeClass('d-none')
                $(this).children('.called-customer').addClass('d-none')
            },
            mouseout: function () {
                $(this).children('.click-call').addClass('d-none')
                $(this).children('.called-customer').removeClass('d-none')
            },
            // 右键菜单
            contextmenu: function (e) {
                // 阻止默认事件
                e.preventDefault()

                let parent = this,
                    ts = $('<div />', {
                        class: 'shadow-sm text-primary position-absolute',
                        css: { left: e.clientX + 'px', top: e.clientY + 'px', width: '150px' },
                        id: 'right-contextmenu'
                    }).append($('<div />', {
                        class: 'list-group customer-context-menu'
                    }).append($('<div />', {
                        type: 'button',
                        class: 'list-group-item list-group-item-action',
                        text: '立即拨号',
                        click: function () {
                            makeCall({ phone: item.phone, customerId: item.id }, createItem({ called_number: item.phone }), parent)

                            removeContextmenu()
                        }
                    }).append($('<i />', {
                        class: 'fa fa-phone float-end small lh-base',
                        css: { lineHeight: 'inhert' }
                    }))).append($('<div />', {
                        type: 'button',
                        class: 'list-group-item list-group-item-action',
                        'data-bs-toggle': 'modal',
                        'data-bs-target': '#recordModal',
                        text: '添加回访记录',
                        click: function (e) {
                            recordForm.find('input[name=customer_id]').val(item.id)

                            removeContextmenu()
                        }
                    }).append($('<i />', {
                        class: 'fa fa-book-bookmark float-end small lh-base',
                        css: { lineHeight: 'inhert' }
                    }))).append($('<button />', {
                        type: 'button',
                        class: 'list-group-item list-group-item-action',
                        text: '从列表中删除',
                        click: function () {
                            $(parent).remove();

                            removeContextmenu()
                        }
                    }).append($('<i />', {
                        class: 'fa fa-trash-alt float-end text-sm lh-base'
                    }))).append($('<button />', {
                        type: 'button',
                        class: 'list-group-item list-group-item-action',
                        text: '查看电话',
                        click: function () {
                            removeContextmenu()
                            let p = $(parent).find('.phone')
                            if (!p.data('show')) {
                                $.get('/customer/getCustomerPhone', { id: item.id }, function (res) {
                                    if (res.code === 1 && res.data) {
                                        $(parent).find('.phone').text(res.data).data('show', true)
                                    }
                                })
                            }
                        }
                    }).append($('<i />', {
                        class: 'fa fa-search float-end text-sm lh-base'
                    }))));

                removeContextmenu()

                $('html body').append(ts)
            }
        }).append($('<div />', {
            class: 'customer-id',
            'data-id': item.id,
        })).append($('<div />', {
            class: 'col-6 text-truncate',
            text: item.title,
            alt: item.title
        })).append($('<div />', {
            class: 'phone col-3',
            text: item.phone,
            'data-show': 'false'
        }))

        if (isCalled) {
            listItem.append($('<div />', {
                class: 'called-customer col-3 text-end',
                text: item.last_calltime
            }))
        }

        listItem.append($('<div />', {
            class: 'click-call text-primary d-none col-3 text-end',
            text: '点击拨号'
        }))

        return listItem
    }

    // 导入客户
    $('.check-rules').on('click', function () {
        layer.open({
            type: 1,
            title: false,
            skin: 'layui-layer-rim',
            area: ['615px', '385px'],
            content: `<div class="p-3">
                           注：只能上传Excel表格，格式为:<br /> ['客户名称', '电话号码', '所在地', '邮箱', '备注']
                           <img src="/static/images/customer-example-import.png" class="pt-3" />
                        </div>`
        })
    })
    let loading = null, flag = false;
    let uploadIns = upload.render({
        elem: '#importExcel',
        url: '/Customer/importExcel',
        accept: 'file', //普通文件
        exts: 'xls|excel|xlsx', //导入表格
        auto: true,  //选择文件后不自动上传
        before: function (obj) {
            // loading = layer.load(); //上传loading

            if (!flag) {
                let layero = layer.confirm('是否允许上传重复的客户，注：重复的客户以客户电话为准',
                    { icon: 3, title: '温馨提示', btn: ['允许', '不允许'] }, function (index) {
                        uploadIns.config.data.is_repeat_customer = 1
                        layer.close(index)
                        flag = true
                        uploadIns.upload()
                    }, function (index) {
                        uploadIns.config.data.is_repeat_customer = 0
                        flag = true
                        uploadIns.upload()
                    })
            }

            return flag
        },
        // 选择文件回调
        choose: function (obj) {
        },
        done: function (res) {
            layer.close(loading);
            let op = { title: res.msg };
            if (res.code === 1) {
                op.icon = 'success';
                customerContainer.html('')
                $.each(res.data, (index, item) => {
                    customerContainer.prepend(createCustomerItem(item))
                })
            }

            Toast.fire(op)
            flag = false
        },
        error: function () {
            flag = false
            setTimeout(function () {
                Toast.fire("上传失败！");
                //关闭所有弹出层
                layer.closeAll(); //疯狂模式，关闭所有层
            }, 1000);
        }
    });

    let customerList = [];
    let importCount = 0;
    // 导入最后一次导入的客户数据
    $('#lastImport').on('click', function () {
        $.post('/Customer/getLastImportCustomerList', {}, (res) => {
            if (res.code === 1 && res.data.length > 0) {
                customerList = res.data
                let isCalled = false
                $.each(res.data, (index, item) => {
                    if (item.called_count > 0) {
                        isCalled = true
                    }
                    customerContainer.append(createCustomerItem(item, isCalled))
                })
            }
        })
    })
    $('.clear-customer').on('click', function () {
        customerList = []
        customerContainer.html('')
    })
});
