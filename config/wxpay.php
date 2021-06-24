<?php

return [
    'appid' => '', // APP APPID
    'app_id' => 'ww8ee3085852a83f1d', // 公众号 APPID
    'miniapp_id' => '', // 小程序 APPID
    'mch_id' => '1503645201',   // 商务号
    'key' => 'UbHJAz3LqCQ71Efq0PadywjTG2Cq13nb',    // 商务号KEY
    'notify_url' => 'http://caller.hbosw.com/api/payment/notify',
    'cert_client' => './cert/apiclient_cert.pem', // optional，退款等情况时用到
    'cert_key' => './cert/apiclient_key.pem', // optional，退款等情况时用到
    'log' => [ // optional
        'file' => app()->getRuntimePath() . 'payment/logs/wechat.log',
        'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
        'type' => 'single', // optional, 可选 daily.
        'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
    ],
    'http' => [ // optional
        'timeout' => 5.0,
        'connect_timeout' => 5.0,
        // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
    ],
    'mode' => 'normal', // optional, dev/hk;当为 `hk` 时，为香港 gateway。
];
