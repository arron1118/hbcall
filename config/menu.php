<?php

return [
    'admin' => [
        "homeInfo" => [
            "title" => "控制台",
            "href" => (string) url('/index/dashboard')
        ],
        "logoInfo" => [
            "title" => "外呼系统",
            "image" => "",
            "href" => "#"
        ],
        "menuInfo" => [
            [
                "title" => "常规管理",
                "icon" => "fa fa-address-book",
                "href" => "",
                "target" => "_self",
                "child" => [
                    [
                        "title" => "控制台",
                        "href" => (string) url('/index/dashboard'),
                        "icon" => "fa fa-gauge",
                        "target" => "_self"
                    ],
                    [
                        "title" => "用户管理",
                        "href" => (string) url('/user/index'),
                        "icon" => "fa-solid fa-users",
                        "target" => "_self"
                    ],
                    [
                        "title" => "通话记录",
                        "href" => (string) url('/hbcall/CallHistoryList'),
                        "icon" => "fa fa-clock",
                        "target" => "_self"
                    ],
                    [
                        "title" => "充值管理",
                        "href" => (string) url('/payment/index'),
                        "icon" => "fa fa-money-check-dollar",
                        "target" => "_self"
                    ],
                    [
                        "title" => "通话报表",
                        "href" => (string) url('/report/index'),
                        "icon" => "fa fa-chart-area",
                        "target" => "_self"
                    ],
                    [
                        "name" => "customer",
                        "title" => "客户管理",
                        "href" => "",
                        "icon" => "fa fa-users-gear",
                        "target" => "_self",
                        "child" => [
                            [
                                "name" => "customer_list",
                                "title" => "客户列表",
                                "href" => (string) url('/customer/index'),
                                "target" => "_self",
                            ],
                            [
                                "name" => "talent_list",
                                "title" => "人才列表",
                                "href" => (string) url('/customer/talent'),
                                "target" => "_self",
                            ],
                            [
                                "name" => "record_list",
                                "title" => "查看记录",
                                "href" => (string) url('/customerPhoneRecord/index'),
                                "target" => "_self",
                            ]
                        ]
                    ],
                    [
                        "title" => "资讯",
                        "href" => (string) url('/news/index'),
                        "icon" => "fa fa-newspaper",
                        "target" => "_self"
                    ],
                    [
                        "title" => "号码管理",
                        "href" => (string) url('/xnumberStore/index'),
                        "icon" => "fa fa-address-book",
                        "target" => "_self"
                    ],
                    [
                        "title" => "呼叫线路",
                        "href" => (string) url('/CallType/index'),
                        "icon" => "fa fa-address-book",
                        "target" => "_self"
                    ],
                    [
                        "title" => "系统配置",
                        "href" => (string) url('/SystemConfig/index'),
                        "icon" => "fa fa-gear",
                        "target" => "_self"
                    ],
                    [
                        "title" => "电子签名",
                        "href" => (string) url('/Signature/index'),
                        "icon" => "fa fa-pen",
                        "target" => "_self"
                    ],
                ]
            ],
            [
                "title" => "日志管理",
                "child" => [
                    [
                        "title" => "登录",
                        "target" => "_self",
                        "icon" => "fa fa-file-lines",
                        "child" => [
                            [
                                "title" => "后台登录",
                                "href" => (string) url('/SigninLogs/adminLogs'),
                                "target" => "_self",
                            ],
                            [
                                "title" => "企业登录",
                                "href" => (string) url('/SigninLogs/companyLogs'),
                                "target" => "_self",
                            ],
                            [
                                "title" => "用户登录",
                                "href" => (string) url('/SigninLogs/userLogs'),
                                "target" => "_self",
                            ]
                        ]
                    ],
                    [
                        "title" => "线路更换",
                        "href" => (string) url('/CallTypeLogs/index'),
                        "target" => "_self",
                    ]
                ]
            ]
        ]
    ],
    'company' => [
        "homeInfo" => [
            "title" => "控制台",
            "href" => (string) url('/index/dashboard')
        ],
        "logoInfo" => [
            "title" => "外呼系统",
            "image" => "",
            "href" => "#"
        ],
        "menuInfo" => [
            [
                "title" => "常规管理",
                "icon" => "fa fa-address-book",
                "href" => "",
                "target" => "_self",
                "child" => [
                    [
                        "title" => "控制台",
                        "href" => (string) url('/index/dashboard'),
                        "icon" => "fa fa-gauge",
                        "target" => "_self"
                    ],
                    [
                        "title" => "用户管理",
                        "href" => (string) url('/user/index'),
                        "icon" => "fa fa-users",
                        "target" => "_self"
                    ],
                    [
                        "title" => "通话记录",
                        "href" => (string) url('/hbcall/CallHistoryList'),
                        "icon" => "fa fa-clock",
                        "target" => "_self"
                    ],
                    [
                        "title" => "充值管理",
                        "href" => (string) url('/payment/index'),
                        "icon" => "fa fa-money-check-dollar",
                        "target" => "_self"
                    ],
                    [
                        "title" => "通话报表",
                        "href" => (string) url('/report/index'),
                        "icon" => "fa fa-chart-area",
                        "target" => "_self"
                    ],
                    [
                        "name" => "customer",
                        "title" => "客户管理",
                        "href" => "",
                        "icon" => "fa fa-users-gear",
                        "target" => "_self",
                        "child" => [
                            [
                                "name" => "customer_list",
                                "title" => "客户列表",
                                "href" => (string) url('/customer/index'),
                                "target" => "_self",
                            ],
                            [
                                "name" => "record_list",
                                "title" => "查看记录",
                                "href" => (string) url('/customerPhoneRecord/index'),
                                "target" => "_self",
                            ]
                        ]
                    ],
                ]
            ],
            [
                "title" => "日志管理",
                "child" => [
                    [
                        "title" => "登录",
                        "target" => "_self",
                        "icon" => "fa fa-file-lines",
                        "child" => [
                            [
                                "title" => "企业登录",
                                "href" => (string) url('/SigninLogs/companyLogs'),
                                "target" => "_self",
                            ],
                            [
                                "title" => "用户登录",
                                "href" => (string) url('/SigninLogs/userLogs'),
                                "target" => "_self",
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'home' => [
        "homeInfo" => [
            "title" => "立即拨号",
            "href" => (string) url('/hbcall/callCenter')
        ],
        "logoInfo" => [
            "title" => "外呼系统",
            "image" => "",
            "href" => "#"
        ],
        "menuInfo" => [
            [
                "title" => "常规管理",
                "icon" => "fa fa-address-book",
                "href" => "",
                "target" => "_self",
                "child" => [
                    [
                        "title" => "呼叫中心",
                        "href" => (string) url('/hbcall/callCenter'),
                        "icon" => "fa fa-mobile-retro",
                        "target" => "_self"
                    ],
                    [
                        "title" => "通话记录",
                        "href" => (string) url('/hbcall/callHistoryList'),
                        "icon" => "fa fa-clock",
                        "target" => "_self"
                    ],
                    [
                        "name" => "customer",
                        "title" => "客户管理",
                        "href" => "",
                        "icon" => "fa fa-users-gear",
                        "target" => "_self",
                        "child" => [
                            [
                                "name" => "customer_list",
                                "title" => "客户列表",
                                "href" => (string) url('/customer/index'),
                                "target" => "_self",
                            ],
                        ]
                    ],
                    [
                        "title" => "基本资料",
                        "href" => (string) url('/user/profile'),
                        "icon" => "fa fa-id-card",
                        "target" => "_self"
                    ],
                    [
                        "title" => "修改密码",
                        "href" => (string) url('/user/resetPassword'),
                        "icon" => "fa fa-lock",
                        "target" => "_self"
                    ]
                ]
            ],
            [
                "title" => "日志管理",
                "child" => [
                    [
                        "title" => "登录",
                        "target" => "_self",
                        "icon" => "fa fa-file-lines",
                        "href" => (string) url('/SigninLogs/userLogs')
                    ]
                ]
            ]
        ]
    ]
];
