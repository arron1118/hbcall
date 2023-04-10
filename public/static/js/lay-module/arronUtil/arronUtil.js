layui.define(['jquery', 'layer'], function (exports) {
    let $ = layui.jquery,
        layer = layui.layer;

    let arronUtil = {
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

        isPhone: function (phone) {
            let pattern = /^1[3456789]\d{9}$/;
            return pattern.test(phone);
        },

        Toast: window.Swal.mixin({
            toast: true,
            position: 'center',
            showConfirmButton: false,
            timer: 3000,
            icon: 'warning',
        }),

        showImportInfo: function () {
            arronUtil.Toast.fire({
                icon: false,
                toast: false,
                timer: false,
                title: '导入说明',
                width: '45rem',
                html: `<div class="p-3">
                               <p>只能上传Excel表格，格式为:</p> <p class="fw-bold">['名称', '电话号码', '所在地', '邮箱', '备注', '专业']</p>
                               <img src="/static/images/customer-example-import.png" class="pt-3" />
                            </div>`
            })
        },

        /**
         * 呼叫提示信息
         */
        caller: {
            success: function (param) {
                let option = {
                    type: 1,
                    title: param.info ? param.info : '拨号成功！',
                    closeBtn: 2,
                    area: '400px;',
                    shade: 0.8,
                    id: 'LAY_layuipro',
                    btnAlign: 'c',
                    moveType: 1,
                    skin: 'layer-ext-blue',
                }
                switch (param.call_type) {
                    case 1:
                        option.content = `
                            <div style="padding: 50px; line-height: 30px; font-weight: 300;">
                                <p>请在<span class="fs-3 clock px-2">10</span>秒内用手机拨打号码：<h3>${param.data.xNumber}</h3> 进行通话。</p>
                            </div>
                        `
                        option.success = function (layero, index) {
                            // 10秒倒计时
                            let t = 10;

                            let inter = setInterval(function () {
                                t--;
                                $('.clock').text(t);
                                if (t < 0) {
                                    clearInterval(inter);
                                    // 关闭当前窗
                                    layer.close(index);
                                }
                            }, 1000);
                        }
                        break;

                    case 2:
                    case 5:
                        option.content = `
                            <div style="padding: 50px; line-height: 30px; font-weight: 300;">
                                <p>${param.msg}</p>
                            </div>
                        `
                        option.time = 3000
                        break;

                    case 3:
                    case 4:
                        option.content = `
                            <div style="padding: 50px; line-height: 30px; font-weight: 300;">
                                <p>${param.message}</p>
                            </div>
                        `
                        option.time = 3000
                        break;
                }

                layer.config({
                    extend: 'skin/blue.css',
                }).open(option);
            },
            fail: function (param) {
                let msg = param.msg

                layer.config({
                    extend: 'skin/red.css',
                }).open({
                    title: param.info ? param.info : '温馨提示',
                    type: 1,
                    area: '400px',
                    shade: 0.8,
                    id: 'LAY_layuipro',
                    btnAlign: 'c',
                    closeBtn: 2,
                    moveType: 1,
                    skin: 'layer-ext-red',
                    content: `
                        <div style="padding: 50px; line-height: 30px; font-weight: 300;">
                            <p>${msg}</p>
                        </div>
                    `
                })
            }
        }
    }

    exports('arronUtil', arronUtil)
})
