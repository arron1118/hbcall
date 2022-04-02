<?php

namespace app\common\library;

class Aes
{
    protected $config = [
        'key' => 'UbHJAz3LqCQ71Efq0PadywjTG2Cq13nb',
        'iv' => '',
        'method' => 'AES-128-CBC'
    ];

    public function __construct($config = []){
//        $config['iv'] = md5(time() . uniqid(), true);
//        $config['iv'] = substr(md5($this->config['key']), 0, 16);
        $this->config = array_merge($this->config, $config);
    }

    // 加密
    public function aesEn($data){
        return  base64_encode(openssl_encrypt($data, $this->config['method'], $this->config['key'], OPENSSL_RAW_DATA , $this->config['iv']));
    }

    //解密
    public function aesDe($data){
        return openssl_decrypt(base64_decode($data),  $this->config['method'], $this->config['key'], OPENSSL_RAW_DATA , $this->config['iv']);
    }
}
