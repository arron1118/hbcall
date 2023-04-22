layui.use(['miniTab', 'element', 'laydate', 'arronUtil'], function () {
    let $ = layui.jquery,
        miniTab = layui.miniTab,
        element = layui.element,
        arronUtil = layui.arronUtil,
        laydate = layui.laydate;

    miniTab.listen();

    const REQUEST_CONFIG = {
        RECORD_ADD_URL: arronUtil.url('/CustomerRecord/add'),
        CUSTOMER_PHONE_URL: arronUtil.url('/customer/getCustomerPhone'),
    }

    let callinginput = $('.callinginput');
    let customerContainer = $('.customer-list');
    // 回访记录模态
    const recordModal = new bootstrap.Modal('#recordModal')
    const recordForm = $('form[name=record-add-form]')
    let controller = {
        // 生成拨号按键
        buildPhoneNumber: function () {
            let btnSize = 4,   // 按键大小 单位：1rem = 16px
                phoneNumbersDiv = $('#phone-numbers'),
                cssClass = 'align-middle cursor rounded-circle d-inline-block fs-3',
                css = {
                    width: btnSize + 'rem',
                    height: btnSize + 'rem',
                    lineHeight: btnSize + 'rem',
                    cursor: 'pointer',
                    backgroundColor: "rgb(228, 228, 228)"
                };

            for (let i = 1; i < 14; i++) {
                let spanParams = {
                        class: cssClass + ' fw-bold',
                        click: function (e) {
                            let v = callinginput.val()
                            if (v.length < 11) {
                                callinginput.val(v + $(this).data('value'));    //输入框值,val()里面
                            }
                        },
                        html: i
                    }

                switch (i) {
                    case 10:
                        // * 键
                        spanParams.html = `<i class="fa fa-asterisk"></i>`
                        spanParams['data-value'] = '*'
                        break;

                    case 11:
                        spanParams['data-value'] = 0
                        spanParams.click = function (e) {
                            if (callinginput.val().length < 11) {
                                callinginput.val(callinginput.val() + $(this).data('value'));//输入框值,val()里面
                            }
                        }
                        spanParams.html = 0
                        break;

                    case 12:
                        // 删除键
                        spanParams.click = function (e) {
                            let v = callinginput.val()
                            callinginput.val(v.slice(0, v.length - 1));
                            controller.inputFocus();
                        }
                        spanParams.html = `<i class="fa fa-backspace text-sm-center small"></i>`
                        break;

                    case 13:
                        // 拨号键
                        Object.assign(css, {
                            backgroundColor: 'rgb(8, 86, 246)',
                            color: '#fff',
                        })
                        spanParams.click = function (e) {
                            if (callinginput.val().length < 11 || !arronUtil.isPhone(callinginput.val())) {
                                arronUtil.Toast.fire('请输入正确的号码');
                                return false;
                            }

                            controller.makeCall({ phone: callinginput.val() },
                            controller.createItem({called_number: callinginput.val()}));
                        }
                        spanParams.html = `<i class="fa fa-phone"></i>`
                        break;

                    default:
                        spanParams['data-value'] = i
                }

                phoneNumbersDiv.append($('<div />', {
                    class: 'col text-center'
                }).append($('<span />', spanParams).css(css)));
            }
        },

        // 移除用户右键菜单
        removeContextmenu: function () {
            $('#right-contextmenu').remove()
        },

        // 输入框获取焦点
        inputFocus: function () {
            $("#input").focus();
        },

        makeCall: function (params = { phone: '', customerId: 0 }, addList = null, customerCalled = null) {
            arronUtil.caller.makeCall({ mobile: params.phone, customerId: params.customerId }, (res) => {
                if (customerCalled) {
                    $(customerCalled).addClass('text-black-50')

                    let callTime = arronUtil.getDateTime()
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
            })
        },

        /**
         * 创建历史列表
         *
         * @param item
         * @returns {*|jQuery}
         */
        createItem: function (item) {
            return $('<div />', {
                class: 'row p-2 mx-1 border-bottom call-history-item',
                style: 'cursor: pointer',
                click: function () {
                    item.create_time = arronUtil.getDateTime()
                    controller.makeCall({ phone: item.called_number, customerId: item.customer ? item.customer.id : 0 }, controller.createItem(item))
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
        },

        // 创建客户列表
        createCustomerItem: function (item, isCalled = false) {
            let aClass = 'call-history-item row p-2 mx-1 border-bottom ';
            if (isCalled) {
                aClass += ' text-black-50'
            }
            let listItem = $('<div />', {
                class: aClass,
                style: 'cursor: pointer',
                click: function () {
                    controller.makeCall({ phone: item.phone, customerId: item.id },
                        controller.createItem({
                            called_number: item.phone,
                            customer: { id: item.id, title: item.title },
                            create_time: arronUtil.getDateTime()
                        }), this)
                },
                mouseover: function () {
                    $(this).addClass('bg-light-subtle')
                    $(this).children('.click-call').removeClass('d-none')
                    $(this).children('.called-customer').addClass('d-none')
                },
                mouseout: function () {
                    $(this).removeClass('bg-light-subtle')
                    $(this).children('.click-call').addClass('d-none')
                    $(this).children('.called-customer').removeClass('d-none')
                },
                // 右键菜单
                contextmenu: function (e) {
                    // 阻止默认事件
                    e.preventDefault()

                    let parent = this,
                        menus = [
                            {
                                icon: 'fa-phone',
                                text: '立即拨号',
                                option: {
                                    click: function () {
                                        controller.makeCall({ phone: item.phone, customerId: item.id },
                                            controller.createItem({ called_number: item.phone }), parent)

                                        controller.removeContextmenu()
                                    }
                                }
                            },
                            {
                                icon: 'fa-book-bookmark',
                                text: '添加回访记录',
                                option: {
                                    'data-bs-toggle': 'modal',
                                    'data-bs-target': '#recordModal',
                                    click: function (e) {
                                        recordForm.find('input[name=customer_id]').val(item.id)

                                        controller.removeContextmenu()
                                    }
                                }
                            },
                            {
                                icon: 'fa-trash-alt',
                                text: '从列表中删除',
                                option: {
                                    click: function () {
                                        $(parent).remove();

                                        controller.removeContextmenu()
                                    }
                                }
                            },
                            {
                                icon: 'fa-search',
                                text: '查看电话',
                                option: {
                                    click: function () {
                                        controller.removeContextmenu()
                                        let p = $(parent).find('.phone')
                                        if (!p.data('show')) {
                                            $.get(REQUEST_CONFIG.CUSTOMER_PHONE_URL, { id: item.id }, function (res) {
                                                if (res.code === 1 && res.data) {
                                                    $(parent).find('.phone').text(res.data).data('show', true)
                                                }
                                            })
                                        }
                                    }
                                }
                            }
                        ],
                        listGroup = $('<div />', { class: 'list-group customer-context-menu' })

                    menus.forEach((val, index) => {
                        let op = {
                            type: 'button',
                            text: val.text,
                            class: 'list-group-item list-group-item-action',
                        }
                        Object.assign(op, val.option)

                        listGroup.append($('<button />', op).append($('<i />', {
                            class: 'fa float-end small lh-base ' + val.icon
                        })))
                    })

                    controller.removeContextmenu()

                    $('html body').append($('<div />', {
                        class: 'shadow-sm text-primary position-absolute',
                        css: { left: e.clientX + 'px', top: e.clientY + 'px', width: '150px' },
                        id: 'right-contextmenu'
                    }).append(listGroup))
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
        },
        listener: function () {
            controller.buildPhoneNumber()
            controller.inputFocus()

            arronUtil.importCustomer('#importExcel', 1, '客户', res => {
                customerContainer.html('')
                $.each(res.data, (index, item) => {
                    customerContainer.prepend(controller.createCustomerItem(item))
                })
            })

            $('#recordModal').on('show.bs.modal', function (e) {
                // 重置表单
                document['record-add-form'].reset()
                laydate.render({
                    elem: '#record-datetime',
                    type: 'datetime',
                })
            })
            recordForm.submit(function (e) {
                $.post(REQUEST_CONFIG.RECORD_ADD_URL, $(this).serialize(), function (res) {
                    let op = { title: res.msg }
                    if (res.code === 1) {
                        op.icon = 'success'
                        recordModal.hide()
                    }

                    arronUtil.Toast.fire(op)
                })

                return false
            })
            // 提示信息模态
            const infoModal = new bootstrap.Modal('#infoModal')

            // 监听键盘、鼠标、滚轮事件
            $(window).on('keydown mousewheel mousedown', function (e) {
                let target = $(e.target.parentElement)
                if (target.hasClass('customer-context-menu')) {
                    target.click()
                } else {
                    controller.removeContextmenu()
                }
            })

            let showCallInfoTimes = arronUtil.cookie('showCallInfoTimes');
            // 首次显示呼叫提示信息
            if (!showCallInfoTimes || parseInt(showCallInfoTimes) === 0) {
                infoModal.show(document.getElementById('infoToggleModal'))
                arronUtil.cookie('showCallInfoTimes', 1)
            }

            callinginput.on('keydown', function (e) {
                // 大键盘||小键盘
                if ((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 96 && e.keyCode <= 106)) {
                    if (callinginput.val().length >= 11) {
                        return false
                    }
                } else if (e.keyCode === 13) {
                    if ($(this).val().length < 11 || !arronUtil.isPhone($(this).val())) {
                        arronUtil.Toast.fire('请输入正确的号码');
                        return false;
                    }

                    controller.makeCall({ phone: $(this).val() }, controller.createItem({called_number: $(this).val()}));
                }
            })

            // 导入客户
            $('.check-rules').on('click', function () {
                arronUtil.showImportInfo()
            })

            // 导入最后一次导入的客户数据
            $('#lastImport').on('click', function () {
                $.post(arronUtil.url('/Customer/getLastImportCustomerList'), (res) => {
                    if (res.code === 1 && res.data.length > 0) {
                        customerContainer.html('')
                        let isCalled = false
                        $.each(res.data, (index, item) => {
                            if (item.called_count > 0) {
                                isCalled = true
                            }
                            customerContainer.append(controller.createCustomerItem(item, isCalled))
                        })
                    }
                })
            })
            $('.clear-customer').on('click', function () {
                customerContainer.html('')
            })
        }
    }

    controller.listener()
});
