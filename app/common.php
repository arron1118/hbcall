<?php
// 应用公共文件

if (!function_exists('curl_request')) {
    /**
     * @param string $url 请求的地址
     * @param boolean $post 请求的方式
     * @param array $params 请求的参数
     * @param boolean $https 是否验证http证书  默认不验证http证书
     */
    function curl_request($url, $post = false, $params = [], $https = false)
    {
        #初始化请求的参数
        $url = curl_init($url);

        #设置请求选项
        if ($post) {
            #设置发送post请求
            curl_setopt($url, CURLOPT_POST, true);
            #设置post请求的参数
            curl_setopt($url, CURLOPT_POSTFIELDS, true);
        }

        #是否https协议的验证
        if ($https) {
            #禁止从服务器验证客户端本地的数据
            curl_setopt($url, CURLOPT_SSL_VERIFYPEER, false);
        }

        #发送请求
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($url);
        if ($res === false) {
            return curl_error($url);
        }
        #关闭请求
        curl_close($url);

        return $res;
    }
}
