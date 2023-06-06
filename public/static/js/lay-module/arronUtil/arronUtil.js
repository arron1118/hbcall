layui.define(['jquery', 'layer', 'upload'], function (exports) {
    let $ = layui.jquery,
        upload = layui.upload,
        layer = layui.layer;

    let arronUtil = {
        /**
         * 生成 URL 链接
         *
         * @param url
         * @returns {string}
         */
        url: function (url) {
            return BASE_FILE + url
        },
        /**
         * 获取格式化时间
         *
         * @param param
         * @returns {string}
         */
        getDateTime: function (param = {}) {
            let defaultParam = {
                year: 0,
                month: 0,
                day: 0,
                hour: 0,
                minute: 0,
                second: 0
            };
            param = Object.assign(defaultParam, param)
            let d = new Date(),
                year = d.getFullYear() + param.year,
                month = d.getMonth() + 1 + param.month,
                day = d.getDate() + param.day,
                hour = d.getHours() + param.hour,
                minute = d.getMinutes() + param.minute,
                second = d.getSeconds() + param.second;

            if (month < 10) {
                month = '0' + month;
            }

            if (day < 10) {
                day = '0' + day;
            }

            if (hour < 10) {
                hour = '0' + hour;
            }

            if (minute < 10) {
                minute = '0' + minute;
            }

            if (second < 10) {
                second = '0' + second;
            }

            return year + '-' + month + '-' + day + ' ' + hour + ':' + minute + ':' + second;
        },

        /**
         * 设置 和 获取 cookie
         *
         * @param key
         * @param val
         * @param obj
         * @returns {string|boolean|*[]}
         */
        cookie: function (key = null, val = null, obj = {}) {
            if (key === null) {
                let val = [];
                let cookie = document.cookie.split('; ');
                $.each(cookie, function (index, item) {
                    let i = item.split('=');
                    val[i[0]] = i[1];
                })

                return val;
            } else {
                if (val === null) {
                    let val = '';
                    let cookie = document.cookie.split('; ');
                    $.each(cookie, function (index, item) {
                        let i = item.split('=');
                        if (i[0] === key) {
                            val = i[1];
                            return false;
                        }
                    })

                    return val;
                } else {
                    document.cookie = key + '=' + val + '; path=/';
                    return true;
                }
            }
        },

        /**
         * 检测手机号码
         *
         * @param phone
         * @returns {boolean}
         */
        isPhone: function (phone) {
            let pattern = /^1[3456789]\d{9}$/;
            return pattern.test(phone);
        },

        /**
         * sweetAlert 弹窗提示
         */
        Toast: window.Swal.mixin({
            toast: true,
            position: 'center',
            showConfirmButton: false,
            timer: 3000,
            icon: 'warning',
        }),

        /**
         * 展示客户导入提示信息
         * @param type
         */
        showImportInfo: function (type = 1) {
            let label = ''
            if (type === 1) {
                label = "['客户名称', '电话号码', '所在地', '邮箱', '备注', '联系人']"
            } else if (type === 2) {
                label = "['人才名称', '电话号码', '所在地', '邮箱', '备注', '专业', '证书类型', '联系人']"
            }

            arronUtil.Toast.fire({
                icon: false,
                toast: false,
                timer: false,
                title: '导入说明',
                width: '54rem',
                html: `<div class="p-3">
                   <p>只能上传Excel表格，格式为:</p> <p class="fw-bold">${label}</p>
                   <img src="/static/images/customer-example-import-${type}.png" class="pt-3" />
                </div>`
            })
        },

        /**
         * 支付接口
         *
         * @param page
         * @param payType
         * @param amount
         * @param payNo
         */
        pay: function (page, payType, amount, payNo = '') {
            if (payType === 1) {
                arronUtil.Toast.fire({
                    timer: false,
                    icon: 'info',
                    title: '正在提交订单...',
                    didOpen: () => window.Swal.showLoading()
                })
                $.post(arronUtil.url("/payment/pay"), { payType: payType, amount: amount, payno: payNo }, function (res) {
                    if (res.code === 1) {
                        let timerInterval, trade_state, trade_state_desc
                        arronUtil.Toast.fire({
                            toast: false,
                            timer: false,
                            icon: false,
                            allowOutsideClick: false,
                            showCloseButton: true,
                            html: `
                            <div class="text-center py-3">
                                <h6>请使用微信扫码支付</h6>
                                <img src="${res.data.qr}" />
                                <h6 class="fs-3"><span class="fs-6">金额：￥</span>${res.data.amount}</h6>
                            </div>
                            `,
                            didOpen: () => {
                                timerInterval = setInterval(function(){
                                    $.post(arronUtil.url("/payment/checkOrder"), { payno: res.data.payno }, function (r) {
                                        if (r.code === 1) {
                                            trade_state = r.data.trade_state
                                            trade_state_desc = r.data.trade_state_desc
                                            if (r.data.trade_state === 'SUCCESS') {
                                                arronUtil.Toast.fire({
                                                    title: r.data.trade_state_desc,
                                                    icon: 'success',
                                                })
                                            }
                                        }
                                    });
                                }, 3000);
                            },
                            didDestroy: () => {
                                clearInterval(timerInterval)
                            }
                        }).then((val) => {
                            page.reloadTable()
                            if (trade_state === 'NOTPAY') {
                                arronUtil.Toast.fire({
                                    title: trade_state_desc
                                })
                            }
                        })
                    } else {
                        arronUtil.Toast.fire({
                            title: res.msg,
                        })
                    }
                })
            } else if (payType === 2) {
                layer.open({
                    title: '支付',
                    type: 2,
                    area: ['100%', '100%'],
                    content: arronUtil.url("/payment/pay") + '?payType=' + payType + '&amount=' + amount + '&payno=' + payNo,
                    btn: false,
                    cancel: function (index, layero) {
                        layer.close(index);
                        page.reloadTable();
                    }
                });
            }
        },

        /**
         * 导入客户
         *
         * @param target
         * @param type
         * @param typeText
         * @param ok
         * @param fail
         */
        importCustomer: function (target, type, typeText, ok, fail) {
            // 导入客户
            let flag = false;
            type = type || 1
            typeText = typeText || '客户'
            ok = ok || function (res) {}
            fail = fail || function () {}

            let uploadIns = upload.render({
                elem: target,
                url: arronUtil.url('/customer/importExcel'),
                accept: 'file', //普通文件
                exts: 'xls|excel|xlsx', //导入表格
                auto: true,  //选择文件后不自动上传
                data: {
                    type: type,
                },
                before: function (obj) {
                    if (!flag) {
                        arronUtil.Toast.fire({
                            icon: 'question',
                            title: `是否允许上传重复的${typeText}`,
                            html: `重复的${typeText}以${typeText}电话为准`,
                            toast: false,
                            showConfirmButton: true,
                            confirmButtonText: '允许',
                            showDenyButton: true,
                            denyButtonText: '不允许',
                            timer: false,
                            showCloseButton: true,
                        }).then((r) => {
                            if (r.isConfirmed) {
                                uploadIns.config.data.is_repeat_customer = 1
                                flag = true
                                uploadIns.upload()
                            } else if (r.isDenied) {
                                uploadIns.config.data.is_repeat_customer = 0
                                flag = true
                                uploadIns.upload()
                            } else {
                                $('.layui-upload-file').val('')
                            }
                        })
                    } else {
                        arronUtil.Toast.fire({
                            timer: false,
                            title: '正在导入...',
                            didOpen: () => window.Swal.showLoading()
                        })
                    }

                    return flag
                },
                // 选择文件回调
                choose: function (obj) {
                },
                done: function (res) {
                    let op = {title: res.msg}
                    if (res.code === 1) {
                        op.icon = 'success';
                        op.timer = 1500

                        ok(res)
                    }

                    flag = false
                    arronUtil.Toast.fire(op)
                },
                error: function () {
                    flag = false
                    setTimeout(function () {
                        arronUtil.Toast.fire("上传失败！");
                    }, 1000);

                    fail()
                }
            });
        },

        /**
         * 呼叫中心
         */
        caller: {
            /**
             * 拨号
             *
             * @param params
             * @param ok
             * @param fail
             */
            makeCall: function (params, ok, fail) {
                params = params || {}
                ok = ok || function (res) {}
                fail = fail || function (res) {}
                arronUtil.Toast.fire({
                    timer: false,
                    icon: 'info',
                    title: '正在拨号...',
                    didOpen: () => window.Swal.showLoading()
                })
                $.post(arronUtil.url('/hbcall/makeCall'), params, function (res) {
                    if (res.code === 1) {
                        ok(res)

                        arronUtil.caller.success(res)
                    } else {
                        fail(res)

                        arronUtil.caller.fail(res)
                    }
                });
            },
            /**
             * 拨号成功提示信息
             *
             * @param param
             */
            success: function (param) {
                let op = {
                    toast: false,
                    titleText: '拨号成功',
                    icon: 'success',
                    allowOutsideClick: false,
                }, timerInterval

                switch (param.data.call_type) {
                    case 1:
                        op.timer = 11000
                        op.timerProgressBar = true
                        op.didOpen = () => {
                            const t = window.Swal.getHtmlContainer().querySelector('.clock')
                            timerInterval = setInterval(() => {
                                t.textContent = Math.ceil(Swal.getTimerLeft() / 1000)
                            }, 1000)
                        }
                        op.willClose = () => {
                            clearInterval(timerInterval)
                        }
                        op.html = `
                            <div>
                                <p>请在<span class="fs-3 clock px-2 text-warning">10</span>秒内用手机拨打号码：<h3>${param.data.xNumber}</h3> 进行通话。</p>
                            </div>
                        `
                        break;

                    case 2:
                    case 5:
                    case 3:
                    case 4:
                        op.html = `
                            <div>
                                <p>${param.msg}</p>
                            </div>
                        `
                        break;
                }
                arronUtil.Toast.fire(op)
            },
            /**
             * 拨号失败提示信息
             *
             * @param param
             */
            fail: function (param) {
                arronUtil.Toast.fire({
                    toast: false,
                    timer: false,
                    icon: 'error',
                    titleText: '温馨提示',
                    html: `
                        <div class="">
                            <p>${param.msg}</p>
                        </div>
                    `,
                })
            }
        }
    }

    exports('arronUtil', arronUtil)
})
