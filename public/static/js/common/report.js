layui.use(['form', 'table', 'laydate', 'jquery', 'arronUtil'], function () {
    let $ = layui.jquery,
        form = layui.form,
        laydate = layui.laydate,
        arronUtil = layui.arronUtil,
        table = layui.table;

    let controller = {
        reloadTable: function (params = {}) {
            let p = Object.assign({}, form.val('search'), params)
            //执行搜索重载
            table.reload('currentTableId', {
                where: p
            });
        },

        listener: function () {

            const chart = echarts.init($('#chart')[0])
            window.onresize = function () {
                chart.resize();
            }

            //日期范围
            laydate.render({
                //设置开始日期、日期日期的 input 选择器
                //数组格式为 2.6.6 开始新增，之前版本直接配置 true 或任意分割字符即可
                elem: '#datetime',
                range: ['#startDate', '#endDate'],
                type: 'datetime',
                done: function (value, date, endDate) {
                    let d = value.split(' - ');
                    controller.reloadTable({ startDate: d[0], endDate: d[1]})
                }
            });

            table.init('currentTableFilter', {
                url: arronUtil.url("/Report/getReport"),
                method: 'post',
                totalRow: true,
                height: 350,
                //异步请求，格式化数据
                parseData: function (res) {
                    let data = res.data.filter(item => item.total > 0)
                    chart.setOption({
                        title: {
                            text: '拨号统计',
                        },
                        dataset: [
                            {
                                source: data
                            }
                        ],
                        legend: {},
                        tooltip: {
                            trigger: 'axis',
                        },
                        toolbox: {
                            feature: {
                                restore: { title: '还原' },
                                saveAsImage: { title: '保存图片' },
                            }
                        },
                        xAxis: {
                            type: 'category',
                            boundaryGap: false,
                            axisLabel: {
                                rotate: 45
                            }
                        },
                        yAxis: {
                            type: 'value',
                            boundaryGap: [0, '100%'],
                            // max: 'dataMax'
                        },
                        grid: {
                            bottom: 10,
                            left: 0,
                            right: 10,
                            containLabel: true
                        },
                        series: [
                            {
                                name: '拨号次数',
                                type: 'line',
                                encode: {
                                    x: 'realname',
                                    y: 'total'
                                },
                                areaStyle: {
                                }
                            },
                            {
                                name: '接通次数',
                                type: 'line',
                                encode: {
                                    x: 'realname',
                                    y: 'total1'
                                },
                                areaStyle: {
                                }
                            },
                            {
                                name: '有效呼叫',
                                type: 'line',
                                encode: {
                                    x: 'realname',
                                    y: 'total2'
                                },
                                areaStyle: {
                                }
                            },
                            {
                                name: '时间(分)',
                                type: 'line',
                                encode: {
                                    x: 'realname',
                                    y: 'duration'
                                },
                                areaStyle: {
                                }
                            },
                            {
                                name: '消费金额(元)',
                                type: 'line',
                                encode: {
                                    x: 'realname',
                                    y: 'cost'
                                },
                                areaStyle: {
                                }
                            }
                        ]
                    })
                },
                even: true,
                skin: 'line'
            });

            form.on('submit(searchSubmit)', function (data) {
                controller.reloadTable()

                return false;
            });

            form.on('select(companyFilter)', function (data) {
                controller.reloadTable()
            })

            setInterval(controller.reloadTable, 300000)

        },
    }

    controller.listener()
});
