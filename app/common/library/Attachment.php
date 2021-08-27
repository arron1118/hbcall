<?php


namespace app\common\library;


use think\db\exception\DbException;
use think\exception\FileException;
use think\facade\Config;
use think\facade\Filesystem;
use app\common\model\Attachment as AttachmentModel;
use think\facade\Session;

class Attachment
{

    public function upload($fileName = 'image', $savePath = 'attachment')
    {
        try {
            $file = request()->file($fileName);
            if (!$file) {
                return false;
            }

            $filesystem = Config::get('filesystem.default');
            $disks = Config::get('filesystem.disks');

            // 保存上传文件
            $saveName = Filesystem::putFile($savePath, $file);
            $saveName = $disks[$filesystem]['url'] . '/' . str_replace('\\', '/', $saveName);

            try {
                // 保存上传记录
                $attachment = new AttachmentModel();
                $attachment->admin_id = Session::get('admin')['id'];
                $attachment->url = $saveName;
                $attachment->mime_type = $file->getOriginalMime();
                $attachment->file_extension = $file->getOriginalExtension();
                $attachment->storage = 'local';
                $attachment->sha1 = $file->sha1();
                $attachment->file_name = str_replace('.' . $file->getOriginalExtension(), '', $file->getOriginalName());
                $attachment->file_size = $file->getSize();
                $attachment->create_time = time();
                $attachment->save();
            } catch (DbException $dbException) {
                exit($dbException);
            }


            return [
                'savePath' => $saveName
            ];
        } catch (FileException $e) {
            dump($e);
        }
    }

}
