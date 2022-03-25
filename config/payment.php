<?php

return [
    'alipay' => [
        'app_id' => '2021002147669194',
        'notify_url' => 'http://caller.hbosw.com/api/payment/alipayNotify',
        'return_url' => 'http://caller.hbosw.com/company/payment/alipayResult',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAg6lknYHrNKy7b1plv4KXA909IfUW3XcVbMxFkXUD5sfwyrtXiLft/weFPUvD2DWB5kEu29IEHbJgbtMJdtXUASOCdV9aY+L7IAbo/UB487bVMuA8OOkscfHG1Ul6nXu8iJWfCnV0pLzwm8cGN0j/46e0otToVCHD2OFdqRu/z9YYg7beQR67UJW1CLNUCrvN52+1EplqRnR18zLmIApy1Dk8BzD6fGldH/BeZXbK1UTDMrduW1wHJJ1H5BVCV3+zAdQ2LXpSZBCK0jd4qW1VkB/H47tRHozFBjAwhnlFiP4+h0n4gCH12r74n8WLF/VJ+UdiJxayKuYDAgERO0vCCQIDAQAB',
        'private_key' => 'MIIEogIBAAKCAQEAkElJdKnwQdIwtTbJGoKozod2Sg6U9MOrJ4Vfj9myBKU7uyQ1qn8TYWmh+RNZA3FaZHTZmiw2Xu4nWmgSQqGkh0V6kAs2MGS99fIYQF+CoQGhsyBMiBKvqmnfegdVNddiQUAbierfZc7t4I7JbJiyxcblhxMPY47tXfQNPUq/Di6m1Y8c1jnYJJBoAMHpL1MMS9T8XmiS+STYD/DQGSzNYWB+xMJm+kSpu6cwPlHHsjbSO1XaOF0GSl8eKYzUsxL4WDKJmQ/EVu0nufX5gThIXDxhhuzPiE7gRkhS4YJASxN6M2QKA26LdzHmfMbrX8xmPkwexOf0lrt1dpDgjHtmzQIDAQABAoIBAFeXNfGNzJ2YpRsNZC4kzad7ErNIgOLJ+hgm3mlsZaZuTIGCLNYRCMnlH4AeX7Y4VQCQ8xyl5GfiuZ8neJZcnI3F/u587+uW7L7mthQ2Jw3o+KnOXMdqWJviY9knpHHoC+zCpzUlkXKzmTLuW5cCZ9yqruI+DuSIes7DfloMC0nm28M1VAmo7dZj2FKQH39jpUt3A8/mz7BgBdz+I7W2Va4Y29VCkQnUfStzbuhrvcLF0YjrRPG8YE2ewch7v2f/6rTSonZFw4lZRUcubQ8PuARq10o6zBMEqZhHKBQZzlQ01F2rcOAF+zkj808PzZhFMqydZilTFQGqzttikf+TCv0CgYEA1B2kIcGW5izJ+F5pZPgzSSByJa882kYUIfL+nbXDqZwKZ3sN2xh3j3D09LRGMAAnFpnlWaRiE0orKG8+sFBgkg09p5p2bCuZQIhPlhHPzQ+iCwCGo/Zrl/noa96Ey3zdkCGwEIjHVh0yqEH+unVwiXoGWp0DmrZ478FC+OCTYX8CgYEAriMrQr4fq4Uc5JzAUZ3sDltd5si84cbT5yuVsfR3HPXUjohrrD0wLrWIPp2sS5CNhvCCSCp+/JaeEe3S1JeELOeG89aWWN0Luo+NQN2Oiq1QzIqRcn/fZ6Q/Nl/L/af89u29ykqTKb/MKZZfBR1hR9iI2SWwltHQvhmFLplZRbMCgYBBfFk01riInWFJXZR6SKpEtFCpU72cwa/rf0KeXARpM7R+mB4B+z7GOSBW/+T/YryunJqTH03sGKTUWevnsRjvXkkfmm9fG+K3ap3vfdZCv8XOUb4/lo9HHy9jRhKHZChfHBdoM2IfMup1ydIjrKguuU6G4RzAwf76Phc4ENVPbwKBgDTL2OvtdPCt9SqjE/Qq600XCotURWA2xjyKjGJd+lc/eWiVl/+qtZcT1vEVIQ3wD9jfxsBWkhXHHLnW31sxbROoRtRbNU5QBqRTrcIC6prFHYBGav7KIlPsCnZT6SdI7Xt4bViN77xyuFXLj5efZsU/s44SzU2M47sfRa/xMo3dAoGAVh4k4ghA9Yj5dMLUqLftpWcuUs0lb3AaFAAScRd1Md0498ser28qJ1IXoXIbS7aXf1FMbQxUUDyVvXiGNPbVFMF7V3Y/IVG7MpABuY33tVmvvYmpPuHdbDNwY7F9UxS7NAYUJuQrQ63dZVSxhPMxfuWSXsZtO29WENXgFeOESOg=',
        'log' => [ // optional
            'file' => app()->getRuntimePath() . 'payment/logs/alipay.log',
            'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
            'type' => 'single', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
        // 'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
    ],
    'wxpay' => [
        'appid' => 'wxc5a6a4d49b6da197', // APP APPID
        'app_id' => 'ww8ee3085852a83f1d', // 公众号 APPID
        'miniapp_id' => '', // 小程序 APPID
        'mch_id' => '1503645201',   // 商务号
        'key' => 'UbHJAz3LqCQ71Efq0PadywjTG2Cq13nb',    // 商务号KEY
        'notify_url' => 'http://caller.hbosw.com/api/payment/notify',   // 回调地址
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
    ]
];
