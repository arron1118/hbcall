<?php


namespace app\api\controller;


use app\common\model\NumberStore;
use think\db\exception\PDOException;

class Util extends \app\common\controller\ApiController
{

    /**
     * 根据TXT文件导入号码
     * @param $txtFile
     * @return string|\think\response\Json
     * @throws \Exception
     */
    public function importNumbersFromTxt($txtFile)
    {
        if (!file_exists($txtFile)) {
            return 'File not exists';
        }

        $fh = fopen($txtFile, 'r+');
        $content = [];
        if (!$fh) {
            return 'File open fail';
        } else {
            $data = [];
            // 逐行读取
            while (!feof($fh)) {
                $number = mb_convert_encoding(trim(fgets($fh)), 'UTF-8');
                $content[] = $number;
                // 去掉空数据
                if (!empty($number)) {
                    $data[] = ['number' => $number];
                }
            }

            try {
                // 多维数组去重
                $data = array_unique($data, SORT_REGULAR);
                $res = (new NumberStore())->saveAll($data);
            } catch (PDOException $e) {
                // 重复的数据可能什么报错
                return '[error code: ' . $e->getCode() . '] ' . $e->getMessage();
            }
            fclose($fh);
        }

        // 数组去空
        $content = array_filter($content);
        // 数组去重
        $content = array_unique($content);

        return json($content);
    }

}
