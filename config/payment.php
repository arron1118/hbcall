<?php

return [
    'alipay' => [
//        'web' => [
//            'app_id' => '2021002147669194',
//            'notify_url' => 'http://caller.hbosw.com/api/payment/alipayNotify',
//            'return_url' => 'http://caller.hbosw.com/company/payment/alipayResult',
//            'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAg6lknYHrNKy7b1plv4KXA909IfUW3XcVbMxFkXUD5sfwyrtXiLft/weFPUvD2DWB5kEu29IEHbJgbtMJdtXUASOCdV9aY+L7IAbo/UB487bVMuA8OOkscfHG1Ul6nXu8iJWfCnV0pLzwm8cGN0j/46e0otToVCHD2OFdqRu/z9YYg7beQR67UJW1CLNUCrvN52+1EplqRnR18zLmIApy1Dk8BzD6fGldH/BeZXbK1UTDMrduW1wHJJ1H5BVCV3+zAdQ2LXpSZBCK0jd4qW1VkB/H47tRHozFBjAwhnlFiP4+h0n4gCH12r74n8WLF/VJ+UdiJxayKuYDAgERO0vCCQIDAQAB',
//            'private_key' => 'MIIEogIBAAKCAQEAkElJdKnwQdIwtTbJGoKozod2Sg6U9MOrJ4Vfj9myBKU7uyQ1qn8TYWmh+RNZA3FaZHTZmiw2Xu4nWmgSQqGkh0V6kAs2MGS99fIYQF+CoQGhsyBMiBKvqmnfegdVNddiQUAbierfZc7t4I7JbJiyxcblhxMPY47tXfQNPUq/Di6m1Y8c1jnYJJBoAMHpL1MMS9T8XmiS+STYD/DQGSzNYWB+xMJm+kSpu6cwPlHHsjbSO1XaOF0GSl8eKYzUsxL4WDKJmQ/EVu0nufX5gThIXDxhhuzPiE7gRkhS4YJASxN6M2QKA26LdzHmfMbrX8xmPkwexOf0lrt1dpDgjHtmzQIDAQABAoIBAFeXNfGNzJ2YpRsNZC4kzad7ErNIgOLJ+hgm3mlsZaZuTIGCLNYRCMnlH4AeX7Y4VQCQ8xyl5GfiuZ8neJZcnI3F/u587+uW7L7mthQ2Jw3o+KnOXMdqWJviY9knpHHoC+zCpzUlkXKzmTLuW5cCZ9yqruI+DuSIes7DfloMC0nm28M1VAmo7dZj2FKQH39jpUt3A8/mz7BgBdz+I7W2Va4Y29VCkQnUfStzbuhrvcLF0YjrRPG8YE2ewch7v2f/6rTSonZFw4lZRUcubQ8PuARq10o6zBMEqZhHKBQZzlQ01F2rcOAF+zkj808PzZhFMqydZilTFQGqzttikf+TCv0CgYEA1B2kIcGW5izJ+F5pZPgzSSByJa882kYUIfL+nbXDqZwKZ3sN2xh3j3D09LRGMAAnFpnlWaRiE0orKG8+sFBgkg09p5p2bCuZQIhPlhHPzQ+iCwCGo/Zrl/noa96Ey3zdkCGwEIjHVh0yqEH+unVwiXoGWp0DmrZ478FC+OCTYX8CgYEAriMrQr4fq4Uc5JzAUZ3sDltd5si84cbT5yuVsfR3HPXUjohrrD0wLrWIPp2sS5CNhvCCSCp+/JaeEe3S1JeELOeG89aWWN0Luo+NQN2Oiq1QzIqRcn/fZ6Q/Nl/L/af89u29ykqTKb/MKZZfBR1hR9iI2SWwltHQvhmFLplZRbMCgYBBfFk01riInWFJXZR6SKpEtFCpU72cwa/rf0KeXARpM7R+mB4B+z7GOSBW/+T/YryunJqTH03sGKTUWevnsRjvXkkfmm9fG+K3ap3vfdZCv8XOUb4/lo9HHy9jRhKHZChfHBdoM2IfMup1ydIjrKguuU6G4RzAwf76Phc4ENVPbwKBgDTL2OvtdPCt9SqjE/Qq600XCotURWA2xjyKjGJd+lc/eWiVl/+qtZcT1vEVIQ3wD9jfxsBWkhXHHLnW31sxbROoRtRbNU5QBqRTrcIC6prFHYBGav7KIlPsCnZT6SdI7Xt4bViN77xyuFXLj5efZsU/s44SzU2M47sfRa/xMo3dAoGAVh4k4ghA9Yj5dMLUqLftpWcuUs0lb3AaFAAScRd1Md0498ser28qJ1IXoXIbS7aXf1FMbQxUUDyVvXiGNPbVFMF7V3Y/IVG7MpABuY33tVmvvYmpPuHdbDNwY7F9UxS7NAYUJuQrQ63dZVSxhPMxfuWSXsZtO29WENXgFeOESOg=',
//            'log' => [ // optional
//                'file' => app()->getRuntimePath() . 'payment/logs/alipay.log',
//                'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
//                'type' => 'single', // optional, 可选 daily.
//                'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
//            ],
//            'http' => [ // optional
//                'timeout' => 5.0,
//                'connect_timeout' => 5.0,
//            ],
//            // 'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
//        ],
        'web' => [
//            'app_id' => '2021003156685219',
            'app_id' => '2021004189691334',
            'notify_url' => 'http://caller.hbosw.com/api/payment/alipayNotify',
            'return_url' => 'http://caller.hbosw.com/company/payment/alipayResult',
            'ali_public_key' => app()->getConfigPath() . '/certs/alipayCertPublicKey_RSA2.pem',
            'private_key' => app()->getConfigPath() . '/certs/alipayCertPrivateKey.pem',
            'log' => [ // optional
                'file' => app()->getRuntimePath() . 'payment/logs/alipay_' . date('Y') . '_' . date('m') . '.log',
                'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
                'type' => 'single', // optional, 可选 daily.
                'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
            ],
            'http' => [ // optional
                'timeout' => 5.0,
                'connect_timeout' => 5.0,
            ],
            // 'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
        ],
        'app' => [
            'app_id' => '2021003124678557',
            'notify_url' => 'http://caller.hbosw.com/api/payment/alipayNotify',
            'return_url' => 'http://caller.hbosw.com/company/payment/alipayResult',
            'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAg6lknYHrNKy7b1plv4KXA909IfUW3XcVbMxFkXUD5sfwyrtXiLft/weFPUvD2DWB5kEu29IEHbJgbtMJdtXUASOCdV9aY+L7IAbo/UB487bVMuA8OOkscfHG1Ul6nXu8iJWfCnV0pLzwm8cGN0j/46e0otToVCHD2OFdqRu/z9YYg7beQR67UJW1CLNUCrvN52+1EplqRnR18zLmIApy1Dk8BzD6fGldH/BeZXbK1UTDMrduW1wHJJ1H5BVCV3+zAdQ2LXpSZBCK0jd4qW1VkB/H47tRHozFBjAwhnlFiP4+h0n4gCH12r74n8WLF/VJ+UdiJxayKuYDAgERO0vCCQIDAQAB',
            'private_key' => 'MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCmxzV+2/x9noGkESFZxlElTsMbcmrPWjBeC3DTHNOoNNr5yQureIElh1OVd6NVS+65h7WEQssCi8wynWMTlm68J0MykTFAsUrV4C9Z191LeQ6/8cvY2Gk7s+zkEYZWvqNGyOxqeqIEMXmIyCEB7cMtJETp479vpqubO+F1KPeF1TDRB72MtLGCmVmDXTrLp9hBTN6TfBBI2DRTZHBeB3Cw4xofsKFvn7v+Ee/4v6NjoIm3ksCqT3cSAMOTDCueE324d3iaE3p2Q8tDxlzljRuu0n+quiTLs8AkZsf5tiLzwcfUU+DNZs4bXPIeK2sNzs8Gm1SbWQg08uPXHXO1SwlJAgMBAAECggEBAJBIPQ6P0GL40t0WeMzK1f65oe9H0AGs27Uwnp31DWMyvtJjzLW+XbQS3Aut4d7z/wYA0tcmVazRNon/QOx8MzaRnP/NPlfiSYS4Gx7VsjwN8eW6kIj7yCZ/ZQx14MuAx46AWo9PooSQLL1ZrbyWbkjKXNgfUMmN3l5Asq8CDwl22quW2taBEgnnz8TmX6eeTh7biIUXisaSggpqQCvj6nzUCORTwpPDwElAlBswkuwZcnJtTdyZ6TWo+VYflVBfI8G5+F4Z4SVu7H/vsvbibpAxTYOi1s5FkcPl5VwFd4NJa+gNCRVu1y4eYli6w9jX3d4UsbQbcQgKvvb1DAiL0XUCgYEA7HOfS5GW3Xjdw+K4A9qUoNkNZIGEFZRrF60N6MTxDmAiarUBg03YzfKkNVKIz8NrHTYFJjiHuHyqXwfA7puU9N34cKBBwqd0cnwNm5b0Uqo9QCiq914SDufKoFIgg3LHWzOhoLpxPZr5PSfhTdSHmFyNIKHkoNHkJrQ4wcuvHo8CgYEAtJD7q+Hy9CaDdQY7nnLpIrsy5UywX3UFptRmrGofx03iFeyjcj5pQtz8TzXeHrTfi9VTSIVCjhiikJAKLlAYcdS487z/eWbFCxiki55IyXCEAp/9ORBBRPkU7C9V7J7cPTheHg3gHB38jMIIxGNrjIRgu75oi9YSDY7zp2qdRqcCgYBJaey/jciFow1X0IDJ0YfsGPgriHr2KErH4xc6ektN51NIRkLd/cGe0ANj+ug3ebk8LJWUtGCPS0Wqk8G3U97/2BtW/KruQQfKs/GVqVzafbjevsG2ZCK/NgCXnmgx5+U1z+YS/VBDjGZuMn+lpqMjDzlSNHHD7OcljTdCFHeeyQKBgAY2guJcKO7rsFRDfaOrEoiGZm7rX5o5PZOK9WlzUVqbPG9CsDELIrYRQoE7OkRWNubp1S7Gnw6inF1bB26mhODNz/tbAnNb7OW/2FGRhbGgtHoepSjkfUpxQ54I1u0IXk2g9eQU2CQ/h+QT/Rc80IOKPoXXPGOrXv2mcI3PJlA7AoGACn9eVI4pbaio7gmm6deLhlz3FBpD0ireHMY29NAh1DNEnJZqxD75c0viV1BIRFSsMhcVr+c8nOKmvlRe+u9k4kBDFlNdHvMW+IIuLC2d1cCts7QiScB+1RY7v/OHsOwWMojqWpxLXYdkZsWvlS3+t+m666NJxW+hpSRlWbj3ZEA=',
            'log' => [ // optional
                'file' => app()->getRuntimePath() . 'payment/logs/alipay_' . date('Y') . '_' . date('m') . '.log',
                'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
                'type' => 'single', // optional, 可选 daily.
                'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
            ],
            'http' => [ // optional
                'timeout' => 5.0,
                'connect_timeout' => 5.0,
            ],
            // 'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
        ]
    ],
    'wxpay' => [
//        'appid' => 'wxc5a6a4d49b6da197', // APP APPID
        'appid' => 'ww01beae8f33a202b4',    // 巨门企业微信ID
//        'app_id' => 'ww8ee3085852a83f1d', // 公众号 APPID
        'app_id' => 'ww01beae8f33a202b4',
        'miniapp_id' => '', // 小程序 APPID
//        'mch_id' => '1503645201',   // 商务号
        'mch_id' => '1695646480',
//        'key' => 'UbHJAz3LqCQ71Efq0PadywjTG2Cq13nb',    // 商务号KEY
        'key' => 'ECPbogadOBXvGzwp9klS9P3L7z3HE5IG',
        'notify_url' => 'http://caller.hbosw.com/api/payment/notify',   // 回调地址
        'cert_client' => app()->getConfigPath() . '/certs/apiclient_cert.pem', // optional，退款等情况时用到
        'cert_key' => app()->getConfigPath() . '/certs/apiclient_key.pem', // optional，退款等情况时用到
        'log' => [ // optional
            'file' => app()->getRuntimePath() . 'payment/logs/wechat_' . date('Y') . '_' . date('m') . '.log',
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

