<?php


namespace app\common\library;


use think\exception\FileException;
use think\facade\Filesystem;

class Attachment
{

    public function upload($fileName = 'image', $savePath = 'temp')
    {
        try {
            $file = request()->file($fileName);
            if (!$file) {
                return false;
            }

            return Filesystem::putFile($savePath, $file);
        } catch (FileException $e) {
            dump($e);
        }
    }

}
