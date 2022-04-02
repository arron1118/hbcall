<?php

namespace app\common\library;

class Aes
{
    /**
     * @var array|string[]
     */
    private array $config = [
        'key' => 'UbHJAz3LqCQ71Efq',
        'iv' => '729fd4513397e11e',
        'method' => 'AES-128-CBC',
        'options' => OPENSSL_RAW_DATA,
    ];

    public function __construct($config = []){
//        $config['iv'] = md5(time() . uniqid(), true);
//        if (!isset($config['iv']) || !$config['iv']) {
//            $config['iv'] = substr(md5($this->config['key']), 0, 16);
//        }

        $this->config = array_merge($this->config, $config);
    }

    /**
     * 加密
     * @param $data
     * @return string
     */
    public function aesEncode($data){
        return  base64_encode(openssl_encrypt($data, $this->config['method'], $this->config['key'], OPENSSL_RAW_DATA , $this->config['iv']));
    }

    /**
     * 解密
     * @param $data
     * @return false|string
     */
    public function aesDecode($data){
        return openssl_decrypt(base64_decode($data),  $this->config['method'], $this->config['key'], OPENSSL_RAW_DATA , $this->config['iv']);
    }

    public function getConfig()
    {
        return $this->config;
    }
}
