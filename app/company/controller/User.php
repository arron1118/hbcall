<?php

namespace app\company\controller;

use app\common\model\Company as CompanyModel;
use app\common\model\NumberStore;
use app\common\traits\UserTrait;
use app\common\model\User as UserModel;
use think\db\exception\DbException;
use think\facade\Event;

class User extends \app\common\controller\CompanyController
{
    use UserTrait;

    public function initialize()
    {
        parent::initialize();

        $this->view->assign([
            'callTypeList' => (new CompanyModel())->callTypeList(),
            'numberList' => NumberStore::select(),
        ]);
    }

    public function index()
    {
        $userModel = new UserModel();
        $this->view->assign([
            'isTestList' => $userModel->getTestList(),
            'statusList' => $userModel->getStatusList(),
        ]);
        return $this->view->fetch();
    }

    public function getUserList()
    {
        if ($this->request->isAjax()) {
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 10);
            $operate = $this->request->param('operate', '');
            $keyword = $this->request->param('keyword', '');
            $is_test = $this->request->param('is_test/d', -1);
            $map = [
                ['company_id', '=', $this->userInfo->id]
            ];

            if ($is_test !== -1) {
                $map[] = ['is_test', '=', $is_test];
            }

            if ($operate) {
                $map[] = [$operate, 'like', '%' . $keyword . '%'];
            }

            $this->returnData['count'] = UserModel::where($map)->count();
            $this->returnData['data'] = UserModel::with(['userXnumber' => ['numberStore']])
                ->hidden(['password', 'salt'])
                ->withCount('customer')
                ->where($map)
                ->order('id desc, logintime desc')
                ->limit(($page - 1) * $limit, $limit)
                ->append(['is_test_text', 'status_text'])
                ->select();

            $this->returnData['msg'] = '操作成功';
        }

        return json($this->returnData);
    }

    public function checkLimitUser()
    {
        if ($this->userInfo->limit_user > 0 && $this->userInfo->user_count >= $this->userInfo->limit_user) {
            $this->returnData['msg'] = '已经达到限制开通用户数量';
        } else {
            $this->returnData['code'] = 1;
        }

        return json($this->returnData);
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $params['username'] = trim($params['username']);
            $params['salt'] = getRandChar(6);
            $params['password'] = getEncryptPassword(trim($params['password']), $params['salt']);

            if ($this->userInfo->limit_user > 0 && $this->userInfo->user_count === $this->userInfo->limit_user) {
                $this->returnData['msg'] = '已经达到限制开通用户数量';
                return json($this->returnData);
            }

            if (UserModel::getByUsername($params['username'])) {
                $this->returnData['msg'] = '用户已经存在';
                return json($this->returnData);
            }

            if (UserModel::getByPhone($params['phone'])) {
                $this->returnData['msg'] = '手机号已经存在';
                return json($this->returnData);
            }

            if (isset($params['is_test'])) {
                $params['is_test'] = (int) $params['is_test'];
                if ($params['is_test'] === 1) {
                    if ($params['test_endtime'] !== '') {
                        $params['test_endtime'] = strtotime($params['test_endtime']);
                        if ($params['test_endtime'] < time() - 1800) {
                            $this->returnData['msg'] = '结束时间不能小于现在时间';
                            return json($this->returnData);
                        }
                    } else {
                        $this->returnData['msg'] = '结束时间不能为空';
                        return json($this->returnData);
                    }
                }
            }

            $params['company_id'] = $this->userInfo['id'];

            $userModel = new UserModel();

            if ($userModel->save($params)) {
                $this->returnData['msg'] = '开通成功';
                $this->returnData['code'] = 1;

                // 保存小号关联数据
                $userModel->userXnumber()->save(['number_store_id' => $params['number_store_id']]);
            } else {
                $this->returnData['msg'] = '开通失败';
            }
        }

        return json($this->returnData);
    }

    public function edit()
    {
        $userId = $this->request->param('id');
        $userInfo = UserModel::with(['userXnumber'])
            ->where([
                'company_id' => $this->userInfo->id,
            ])
//            ->hidden(['password'])
            ->find($userId);

        if (!$userInfo) {
            $this->returnData['msg'] = lang('No data was found');
            return json($this->returnData);
        }

        if ($this->request->isPost()) {
            $params = $this->request->param();
            $params['username'] = trim($params['username']);

            $existsName = UserModel::where('id <> ' . $params['id'] . ' and username = "' . $params['username'] . '"')->count();
            if ($existsName > 0) {
                $this->returnData['msg'] = '用户已经存在';
                return json($this->returnData);
            }

            if (UserModel::where('id <> ' . $params['id'] . ' and phone = "' . $params['phone'] . '"')->count() > 0) {
                $this->returnData['msg'] = '手机号已经存在';
                return json($this->returnData);
            }

            if ($params['password'] !== $userInfo->password) {
                $userInfo->password = getEncryptPassword(trim($params['password']), $userInfo->salt);
            }

            if (isset($params['is_test'])) {
                $userInfo->is_test = (int) $params['is_test'];
                $this->returnData['data'] = $userInfo;
                if ((int) $params['is_test'] === 1) {
                    if ($params['test_endtime'] !== '') {
                        $userInfo->test_endtime = strtotime($params['test_endtime']);
                        if ($userInfo->test_endtime < time() - 1800) {
                            $this->returnData['msg'] = '结束时间不能小于现在时间';
                            $this->returnData['data'] = $userInfo->test_endtime;
                            return json($this->returnData);
                        }
                    } else {
                        $this->returnData['msg'] = '结束时间不能为空';
                        return json($this->returnData);
                    }
                }
            } else {
                $userInfo->is_test = 0;
                $userInfo->test_endtime = 0;
            }

            $userInfo->username = $params['username'];
            $userInfo->realname = $params['realname'];
            $userInfo->phone = $params['phone'];
            $userInfo->callback_number = $params['callback_number'];
            $userInfo->limit_call_number = $params['limit_call_number'];
            $userInfo->customer_view_num = $params['customer_view_num'];
            $userInfo->talent_num = $params['talent_num'];
            $userInfo->talent_keep_time = $params['talent_keep_time'];
            $userInfo->customer_num = $params['customer_num'];
            $userInfo->customer_keep_time = $params['customer_keep_time'];
            $this->returnData['msg'] = '保存失败';

            if ($userInfo->save()) {
                // 保存小号关联数据
                if (isset($params['number_store_id'])) {
                    if ($userInfo->userXnumber) {
                        $userInfo->userXnumber->number_store_id = $params['number_store_id'];
                        $userInfo->userXnumber->save();
                    } else {
                        $userInfo->userXnumber()->save(['number_store_id' => $params['number_store_id']]);
                    }
                }

                // 更新用户的客户数量
                if ($userInfo->talent_num || $userInfo->talent_keep_time || $userInfo->customer_num || $userInfo->customer_keep_time) {
                    Event::trigger('Customer', $userInfo);
                }

                $this->returnData['msg'] = '保存成功';
                $this->returnData['code'] = 1;
            }
        } else {
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '获取成功';
            $this->returnData['data'] = [
                'userInfo' => $userInfo,
                'callTypeList' => (new CompanyModel())->getCalltypeList(),
            ];
        }

        return json($this->returnData);
    }

    public function del()
    {
        if ($this->request->isPost()) {
            $userId = $this->request->param('id', 0);
            if (!$userId) {
                $this->returnData['msg'] = '未提供正确的ID';
                return json($this->returnData);
            }

            try {
                $userInfo = UserModel::where([
                    'company_id' => $this->userInfo->id,
                ])->find($userId);

                $userInfo->together(['userXnumber'])->delete();

                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '删除成功';
            } catch (DbException $exception) {
                $this->returnData['msg'] = $exception->getMessage();
            }
        }

        return json($this->returnData);
    }

    /**
     * 返回的小号列表
     * @return NumberStore[]|array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function getAxbNumbers()
    {
        return NumberStore::where(['company_id' => $this->userInfo['id'], 'status' => 1])->select()->toArray();
    }

    /**
     * 返回小号
     * @param $id
     * @return mixed
     */
    protected function getAxbNumber($id)
    {
        return NumberStore::where('id', $id)->value('number');
    }

    public function updateUser()
    {
        if ($this->request->isPost()) {
            $this->returnData['msg'] = '更新失败';
            if ($this->request->has('id')) {
                $param = $this->request->param();
                $user = UserModel::find($param['id']);
                if ($user->save($param)) {
                    $this->returnData['msg'] = '更新成功';
                    $this->returnData['code'] = 1;
                }
            }

            return json($this->returnData);
        }

        return json($this->returnData);
    }

    public function profile()
    {
        if ($this->request->isPost()) {
            $realname = trim($this->request->param('realname'));
            $this->userInfo->realname = $realname;
            $this->userInfo->save();

            return json(['msg' => lang('Operation successful'), 'code' => 1]);
        }

        return $this->view->fetch();
    }
}
