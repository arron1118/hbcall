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
//            'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnCdyPggpKxo7OpjQ3qgXkcYxXEFLE5MbuNFED3480sUO6YB4KuBW1+RHUbEVZnIHIalTrBajj9RgTcjXl3ThLl0hYK5DfQZFKsfMeuzUnjArNZHqLCta9ZK4rzFKV26NZdQcB7fJIrttK/kVm0FLDm9ma76VwpQdDMw8AaH9wjWfcjAMoM78ypqKu6G4BG3lzp87tn19q/HJEM3uywezGZNJArMVcEQytK7oC9xoSoaQtctrjiafAS8+g2bDpKfsTrlYSUJXInDR9hjy9Y5FAO6pe8RjfGsQPtwevLc+N7a5ckZbekWFEMndZsM2/F3HUpuWLZOE8eS5o0WubBL0vwIDAQAB',
//            'private_key' => 'MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCZ/fPdAIgPcNsSKIwSTgHDo30LyoNiX1HnMbMjGWVLUFo9i41RS66umE68xS47NwVUlmjfxyru8cocTbN1yY5FLxMljBHPsXnXHJ+DFHQTvgUSzLXgWeBkgjrhtsl3B0/5NLoRrASJMpG+dssn30cgnmxULZYe3DShjdy5eLtivETH+4V1A67nryqmLgIq9rLYFp+CHN413Ul0Qm1QADferkEeWfcX50NetwzFk09HFp0Mz/20CtfGOrHZZJRF7iogMDs6KteiWoBTXZNeX9Flh1qj8/Lnax33IgsnC/Y4ecQ3E9FSBRAKAb72CbF1GKr8zf4wiR884jD0s+3fnE1tAgMBAAECggEAEg6yevbl/ZGJTt7MVhUppcxVDH3p3C47R3YPgI0o0KUSf+cOYjsSt8yKZx5kJnN0P5AHqj4LrctSnPELm0NQWJKFl0COkiFInU3w8okCd5IvvB2fMji31HFlADTT+Q41dY6WRx45xfDVIFWDyyAmkg+hullCIkpGLa+Z83HR32jvgouq+Q12Zb87YZCOkwlZmFy6TLj0e3/lGh42Z/AWgZXAWzTcZkZRkrj3qJ6Ip6hSOotCk3mt/qtFHtvOU0aQRMM165ulzdYFBDqj2udqyEsd9FpL1+pE5VuUo9xqdtMfarCUSDIZke8Ce4XD2bSXI0V+vTxxGamBH4ufcZ31oQKBgQDcUVL7pWo5kx6SjrUWsasXOsPDc60vpG8/M9bpLniLej02Nz7RbIJGuCxUGCYfAZvxmJjsShjLz4oO70rqu0YKjp4KOudFadut6Q4X5zwKa481XuzzFE23cvvyX5tYk3lTsQBEa900R3Kfba7/28/XCsYkN2FvR0W6UvxHZjMABQKBgQCy7qyeTpGl/d3wgfA8zROfjg5h0irA104bgbNJB/anLF6XA1BTXlqhzu5PfjeuUPj1PmaIen4sVLhOGUWgpQsS90ny8Y3PIa7LrZQAZOA7T3NKXPhV7aCOAMB2iWDhwkNaxwjCI3g/S0i4yCNIt9ELYW/iJMECeAoZZAhdYWncSQKBgHx+s96hTVI1cRwt6eRmByD8QuzqK931FCvptjJT4Sgnrfk55JRgtYkSv3HRp5DzztQJf7hd8n8Qypm/3Fyh8GU8y89KE6+krs8qF52oO8RDnUvyVQDS40ubfQUrw1OdGf0R0u3W6cuF6B5w02efJWYFn4uNg9BmvN8Zz5blCEKlAoGAVTEkCL95wyFq4Q7MtfYiKK53FIX/Cw+3xYrsNJJREmkipB3Uycy7tByVBE4g7qLo1cMfCE3h8vNnSO/pYcIS0rXghIYTsZHk6l67f7CHvlVmm/pF1ousDNA40nc4JGTcssTe26gNoX0To77OZdn28scygOZk6usCS/J/tQ4jW9kCgYEAyEZq9F850RKzuiIereJDPo1SFGl4BOjCp3n4EiM+8ilPLJZRCEO9qbQuP1eTK/cd90P27RRKyJ1SFFQ+qay3Dqloo4+QzgKWSsOZ0UncKQV53XP2m6ChT3iceCcoDgfVtj6f8LN+Un4rvOPB+V4+VWskBLL4NpnxZU7NxZ4CiAY=',
            'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtyp3+oJwE/qpj2NC9rBLm1fpvj5eHJAiykgbcBpuP/H0LYPTEE185Ue+eEt1aszQ0J170c57QsBpkKbdjfJsJGfGWUGfEgB8LsUVoqQAvfKMKAviiq+bRz+VBvoCNK8xGGsmOus+SWYnKW7zXECW3j/hRSPFXhjDZS6i47hyET2l5/NKfH9RWMzbfwfYkVbC3mg/AMHErCr6YM0799wmO8iS2z3LaMk8UTwQKV0O4+hljwTSxD6YJnJI8Db0DA93Qbs0OrTrP/btV3rVIcWdanToGeC50Ju+QbFHdmM0dw7HxFoFxwlF4jI7gssdvsrFNigbfU2T2qYN2MudigG1sQIDAQAB',
            'private_key' => 'MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCnhopkIeaMFdT4ZKyje4C0FiaVK4HwAUZaYGVgICWFuv0q9HVXogtcoJt0uaBB46cjB9j0AgzRU676eGiRITpdTsHblHQd+6K0SZ+evAcE4zKYVBI5U6dHMeczu8DUPJsMqQ0jrMVCsrmRf4S6TZne98B59iH03s2GbGM/zTx+Fr1MBp/XU1v2iERHC2BIN6CgZRFVwv95t+Q9ZsVvm1/8yqGuVCvjG0eSOmWjk3QUR/zoa0hs6/VyBWPiybFqVEpmhtT5QH8ZkJlx0VLSyl3RZyQf17UPLGh8ExByXQewTdISvYgIdUyxsD0foSrn7+4TSvu5Xc/4cw+2l7X6hio/AgMBAAECggEAGQkQMXqne5PfqedRrXTNfRw6U9yWpIlsPCFfxQfI071oDD1QM/JxhDw0PzNmcbJVzfRkRcLwq2+4HJJV/ipbEIquieQbnkd2vz6pbg1ndyGE9CLMPmjz/L3GcYLDhHQyL6gr4IL3T8pp1QabjUP/lMStrQxNcszBJi+YfXZsbeM2LRFB5It3Hkv6nJuw6t0zPyp5WZNAeH6kQ5ojOSe4HFyDirDopIeGKKhr1rYFxC/K4IHuysqrpBxBbYDQ2PWnw6YHC85q7wt7t/hAPjl8KLR7ivHEoZZAa0slmI3Ofw5bV1SoHfAcBCH7mDPsJaArnNCsam43ahMeEkVbZrBAYQKBgQDP9PoiHG+F9z0jvUdg95oAlejO73sxBtMAnH0tMfJxuXY+w/FD2E7PaAPMPyUEol6XmF9DePFmID56+UYGcVK35Fw+ruvpMIi26ftmA5iG0XjCJFY5OvpadR+WCxpOdlxamgq+oUhZtvD02Dj33TBpXFaoF2HTQ6/ymryhKBpFBwKBgQDOOl0Tt7fGeirz+53mXUN/M5UT0cxhJPjxlIMppDPZgcjB3wfOIKVmivZxRC4fTIuVS4esKvz0CWBNZQnNHYjYk+THfL1G4bN9cP3Oh1IwcMe+Xub22nIYBiBBlhBn3GXFvPjaKxp/IKXiBTyyI5JEO0KAMjMksLkSnfzpvp4bCQKBgAMrgdCZTF3naegsj3T78T4HCvh0kBUsPHUq7YGN3Fs9b37/b6MQHgttU3l+kOrkKrr22KTnqA5deXZYbGfWvGMPORS/h9sTIVJgeLOSZHXRpZyX/zR6IKzWUjfwTWNazIeZB4bmYHr1nfCthxjIJ1/Dx5JiYNxekMUK9MskGFprAoGAL9ynwW2//xZXZayd5tr6UUk9bg4g6uLTy+11y3JKfk56s1P50cMN4BCcRYlXUvhG5O1UnYaUkmairROoBKy4F9urGwk+PHchWxmgLhCF6KwkD3CjFeN4206AqfgT8qbaD9xdvPSH/70qApzIi2dqCN/f/TSpXfiN215DVlRhCVECgYBChpvmJIojLpt0Ce34pRaOUiAb8ulPzn6iA2OdZt/K9JiKjVASb1NEe6kngxFpIDEXol8m0mPL3LQnlk9OMIMtgq+ysQH/cfB6H1eEtJjmiCm5ap13U3bKJBFG9jRKfaE/pu8owc9QAtmrvZRNPHfwBzHzJVp+4oxV+Om/3r6SIA==',
            'log' => [ // optional
                'file' => app()->getRuntimePath() . 'payment/logs/alipay.log',
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
                'file' => app()->getRuntimePath() . 'payment/logs/alipay.log',
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
        'app_id' => 'ww8ee3085852a83f1d', // 公众号 APPID
        'miniapp_id' => '', // 小程序 APPID
//        'mch_id' => '1503645201',   // 商务号
        'mch_id' => '1695646480',
//        'key' => 'UbHJAz3LqCQ71Efq0PadywjTG2Cq13nb',    // 商务号KEY
        'key' => 'ECPbogadOBXvGzwp9klS9P3L7z3HE5IG',
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

