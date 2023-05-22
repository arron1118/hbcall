<?php


namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class Attachment extends Model
{
    use SoftDelete;
}
