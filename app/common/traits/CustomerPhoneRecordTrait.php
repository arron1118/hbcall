<?php
/**
 * copyright@Administrator
 * 2023/5/22 0022 16:40
 * email:arron1118@icloud.com
 */

namespace app\common\traits;

trait CustomerPhoneRecordTrait
{
    public function index()
    {
        return $this->view->fetch();
    }

    public function getRecordList()
    {
        return json($this->returnData);
    }
}
