(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory(require('@popperjs/core')) :
        typeof define === 'function' && define.amd ? define(['@popperjs/core'], factory) :
            (global = typeof globalThis !== 'undefined' ? globalThis : global || self, global.utils = factory(global));
}(this, (function (global) {
    'use strict';

    const $ = global.jQuery;
    const layer = global.layui.layer;

    function getDateTime(param = {}) {
        let defaultParam = {
            year: 0,
            month: 0,
            day: 0,
            hour: 0,
            minute: 0,
            second: 0
        };
        param = Object.assign(defaultParam, param)
        let d = new Date();
        let year = d.getFullYear() + param.year,
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
    }

    /**
     * 设置 和 获取 cookie
     * @param key
     * @param val
     * @param obj
     * @returns {string|boolean|*[]}
     */
    function cookie(key = null, val = null, obj = {}) {
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
    }

    function isPhone(phone) {
        let pattern = /^1[3456789]\d{9}$/;
        return pattern.test(phone);
    }

    const caller = {
        success: function (param) {
            layer.config({
                extend: 'skin/blue.css',
            }).open({
                type: 1,
                title: param.info ?? '拨号成功！',
                closeBtn: 2,
                area: '400px;',
                shade: 0.8,
                id: 'LAY_layuipro',
                btnAlign: 'c',
                moveType: 1,
                skin: 'layer-ext-blue',
                content: `
                            <div style="padding: 50px; line-height: 30px; font-weight: 300;">
                                <p>请在<span class="fs-3 clock px-2">10</span>秒内用手机拨打号码：<h3>${param.data.xNumber}</h3> 进行通话。</p>
                            </div>
                        `,
                success: function (layero, index) {
                    // 10秒倒计时
                    let t = 10;
                    let time = document.getElementsByClassName("clock")[0];
                    let getTime = function () {
                        t--;
                        time.innerHTML = t;
                        if (t < 0) {
                            clearInterval(inter);
                            // 关闭当前窗
                            layer.close(index);
                        }
                    };

                    let inter = setInterval(getTime, 1000);
                }
            });
        },
        fail: function (param) {
            layer.config({
                extend: 'skin/red.css',
            }).open({
                title: param.info ?? '温馨提示',
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
                            <p>${param.msg}</p>
                        </div>
                    `
            })
        }
    }

    return {
        getDateTime,
        cookie,
        caller,
        isPhone,
    }
})));


