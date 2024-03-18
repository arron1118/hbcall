layui.use(['layer', 'miniTab', 'arronUtil'], function () {
    let $ = layui.jquery,
        arronUtil = layui.arronUtil,
        miniTab = layui.miniTab;

    // 在新的Tab窗口打开页面
    miniTab.listen();

    let chart = echarts.init($('#historyChart')[0]),
        callChart = echarts.init($('#callChart')[0]),
        h = 10

    let controller = {
        getCostData: function () {
            $.post(arronUtil.url("/Index/getCostData"), function (res) {
                if (APP_MODULE === 'admin') {
                    $('.total-payment').html(res.data.total_payment)
                }
                $('.total-cost').html(res.data.total_cost)
                $('.percentage').html(res.data.percentage)
                $('.yesterday-percentage').html(res.data.yesterdayPercentage)
                $('.current-day-cost').html(res.data.current_day_cost)
                $('.yesterday-cost').html(res.data.yesterday_cost)
                $('.two-days-ago-cost').html(res.data.two_days_ago_cost)
                $('.current-month-cost').html(res.data.current_month_cost)
                $('.current-year-cost').html(res.data.current_year_cost)
            })
        },
        getChartData: function () {
            $.post(arronUtil.url("/Index/getDashboardReport"), function (res) {
                $('.totalCallHistory').text(res.data.totalCallHistory)
                $('.totalCallAndPickUp').text(res.data.totalCallAndPickUp)
                $('.totalCallAndNoPickUp').text(res.data.totalCallAndNoPickUp)
                $('.totalCallDuration').text(res.data.totalCallDuration)
                chart.setOption({
                    title: {
                        text: '呼叫统计',
                    },
                    dataset: [
                        {
                            source: res.data.chartData
                        }
                    ],
                    tooltip: {
                        trigger: 'item',
                    },
                    series: [
                        {
                            name: '拨号次数',
                            type: 'pie',
                            radius: ['35%', '75%'],
                            datasetIndex: 0
                        }
                    ]
                })
            })
        },
        getCallChartData: function () {
            $.post(arronUtil.url("/Index/getHoursData"), { hours: h }, function (res) {
                if (res.code === 1) {
                    // 近几小时的记录
                    let day = h + '小时'
                    if (h % 24 === 0) {
                        day = h / 24 + '天'
                    }

                    callChart.setOption({
                        title: {
                            text: `近 ${day} 拨号统计`,
                            subtext: `共计 ${res.total} 次`,
                            subtextStyle: {
                                color: '#aaa',
                            }
                        },
                        dataset: [
                            {
                                source: res.data
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
                                // rotate: 45
                            }
                        },
                        yAxis: {
                            type: 'value',
                            boundaryGap: [0, '100%'],
                            // max: 'dataMax'
                        },
                        grid: {
                            bottom: 10,
                            left: 20,
                            right: 20,
                            containLabel: true
                        },
                        series: [
                            {
                                name: '拨号次数',
                                type: 'line',
                                encode: {
                                    x: 'datetime',
                                    y: 'sum'
                                },
                                areaStyle: {
                                }
                            },
                            {
                                name: '时间(分)',
                                type: 'line',
                                encode: {
                                    x: 'datetime',
                                    y: 'duration'
                                },
                                areaStyle: {
                                }
                            },
                            {
                                name: '消费(￥)',
                                type: 'line',
                                encode: {
                                    x: 'datetime',
                                    y: 'expense'
                                },
                                areaStyle: {
                                }
                            }
                        ]
                    })
                }
            })
        },
        getTopData: function () {
            $.post(arronUtil.url("/Index/getTopList"), {}, function (res) {
                if (res.code === 1) {
                    $('.toplist-table > tbody').html('')
                    $.each(res.data, function (index, item) {
                        $('.toplist-table > tbody').append($('<tr />')
                            .append($('<td />', { text: item.username }))
                            .append($('<td />', { text: item.call_sum }))
                            .append($('<td />', { text: item.call_success_sum }))
                            .append($('<td />', { text: item.call_duration_sum }))
                            .append($('<td />', { text: item.expense }))
                        )
                    })
                }
            })
        },
        listener: function () {
            controller.getCostData()
            controller.getChartData();
            controller.getCallChartData();
            controller.getTopData();

            // 定时任务
            setInterval(function () {
                controller.getCostData()
                controller.getChartData()
                controller.getCallChartData()
                controller.getTopData()
            }, 1000 * 300);

            window.onresize = function (e) {
                chart.resize();
                callChart.resize();
            }
        }
    }

    controller.listener()
});
