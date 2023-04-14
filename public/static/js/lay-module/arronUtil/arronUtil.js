layui.define(['jquery', 'layer'], function (exports) {
    let $ = layui.jquery,
        layer = layui.layer;

    let arronUtil = {
        /**
         * 生成 URL 链接
         * @param url
         * @returns {string}
         */
        url: function (url) {
            return BASE_FILE + url
        },
        /**
         * 获取格式化时间
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
         * @param phone
         * @returns {boolean}
         */
        isPhone: function (phone) {
            let pattern = /^1[3456789]\d{9}$/;
            return pattern.test(phone);
        },

        /**
         * 弹窗提示
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
                label = "['客户名称', '电话号码', '所在地', '邮箱', '备注']"
            } else if (type === 2) {
                label = "['人才名称', '电话号码', '所在地', '邮箱', '备注', '专业', '证书类型']"
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
         * @param page
         * @param payType
         * @param amount
         * @param payNo
         */
        pay: function (page, payType, amount, payNo = '') {
            if (payType === 1) {
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
         * 呼叫提示信息
         */
        caller: {
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
                            const t = Swal.getHtmlContainer().querySelector('.clock')
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
