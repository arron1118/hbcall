<?php

namespace wxpay\example;

/**
 *
 * example目录下为简单的支付样例，仅能用于搭建快速体验微信支付使用
 * 样例的作用仅限于指导如何使用sdk，在安全上面仅做了简单处理， 复制使用样例代码时请慎重
 * 请勿直接直接使用样例对外提供服务
 *
 */

use wxpay\lib\WxPayApi;
use wxpay\lib\WxPayBizPayUrl;
use wxpay\example\WxPayConfig;
use wxpay\example\Log;
use Exception;

/**
 *
 * 刷卡支付实现类
 * @author widyhu
 *
 */
class NativePay
{
    /**
     *
     * 生成扫描支付URL,模式一
     * @param BizPayUrlInput $productId
     * @return string
     * @throws Exception
     */
    public function GetPrePayUrl($productId)
    {
        $biz = new WxPayBizPayUrl();
        $biz->SetProduct_id($productId);
        try {
            $config = new WxPayConfig();
            $values = WxpayApi::bizpayurl($config, $biz);
        } catch (Exception $e) {
            Log::ERROR(json_encode($e));
        }
        return "weixin://wxpay/bizpayurl?" . $this->ToUrlParams($values);
    }

    /**
     *
     * 参数数组转换为url参数
     * @param array $urlObj
     * @return string
     */
    private function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v) {
            $buff .= $k . "=" . $v . "&";
        }

        return trim($buff, "&");
    }

    /**
     *
     * 生成直接支付url，支付url有效期为2小时,模式二
     * @param UnifiedOrderInput $input
     * @return mixed
     */
    public function GetPayUrl($input)
    {
        if ($input->GetTrade_type() == "NATIVE") {
            try {
                $config = new WxPayConfig();
                return WxPayApi::unifiedOrder($config, $input);
            } catch (Exception $e) {
                Log::ERROR(json_encode($e));
            }
        }
        return false;
    }
}
