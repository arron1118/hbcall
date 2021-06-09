<?php


namespace app\api\controller;


class HbCall extends \app\common\controller\ApiController
{
    public static function httpRequest($url, $format = 'get', $data = null)
    {
        //设置头信息
        $headerArray = array("Content-type:application/json;", "Accept:application/json");
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

        if ($format == 'post') {
            //post传值设置post传参
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data) {
                $data = json_encode($data);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArray);
        $data = json_decode(curl_exec($curl), true);
        dump($curl);

        curl_close($curl);

        //返回接口返回数据
        return $data;

    }


}
