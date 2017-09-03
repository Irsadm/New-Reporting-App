<?php
namespace App\Controllers\api;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\RequestModel;
use App\Models\GuardModel;
use App\Models\GroupModel;
use App\Models\UserGroupModel;
use App\Models\Users\UserToken;
use App\Models\Users\UserModel;
/**
 *
 */
class RequestController extends BaseController
{
    public function createUserToGroup($request, $response, $args)
    {
        $requestModel = new RequestModel($this->db);
        // $userToken = new UserToken($this->db);
        $userGroups = new UserGroupModel($this->db);
        $groups = new GroupModel($this->db);

        $token = $request->getHeader('Authorization')[0];
		$userId = $request->getQueryParam('user_id');
        $groupId = $request->getQueryParam('group_id');

        $group = $groups->find('id', $args['group']);
        $userGroups = $userGroups->findTwo('user_id', $userId, 'group_id', $groupId);
        $data = [
            'user_id'   =>  $userId,
            'group_id'  =>  $groupId,
        ];
        if ($group) {
            if ($userGroups[0]) {
                return $this->responseDetail(400, true, 'Anda sudah bergabung dengan grup ini');
            } else {
                $requestModel->requestUserToGroup($data);
                return $this->responseDetail(201, false, 'Berhasil mengirim permintaan group');
            }
        } else {
            return $this->responseDetail(404, true, 'Grup tidak ditemukan');
        }
    }

    public function createGuardToUser($request, $response, $args)
    {
        $requestModel = new RequestModel($this->db);
        $userToken = new UserToken($this->db);
        $token = $request->getHeader('Authorization')[0];
        $guardId = $userToken->getUserId($token);
        $userId = $args['user'];
        $data = [
            'user_id'   =>  $userId,
            'guard_id'  =>  $guardId,
        ];
        if ($data) {
            $addRequest = $requestModel->requestGuardToUser($data);
            $data = $this->responseDetail(201, false, 'Berhasil mengirim permintaan user');
        } else {
            $data = $this->responseDetail(401, true, 'Ada kesalahan saat mengirim permintaan');
        }
        return $data;
    }
    public function createUserToGuard($request, $response, $args)
    {
        $requestModel = new RequestModel($this->db);
        $userToken = new UserToken($this->db);
        $token = $request->getHeader('Authorization')[0];
        $userId = $userToken->getUserId($token);
        $guardId = $args['guard'];
        $data = [
            'user_id'   =>  $userId,
            'guard_id'  =>  $guardId,
        ];
        if ($data) {
            $addRequest = $requestModel->requestUserToGuard($data);
            $data = $this->responseDetail(201, false, 'Berhasil mengirim permintaan guard');
        } else {
            $data = $this->responseDetail(401, true, 'Ada kesalahan saat mengirim permintaan');
        }
        return $data;
    }

    public function guardRequest($request, $response, $args)
    {
        $requestModel = new RequestModel($this->db);

        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perPage = $request->getQueryParam('perpage');

		$userId = $request->getQueryParam('user_id');
        $userReq = $requestModel->findGuardRequest(4)->setPaginate($page, $perPage);
        // var_dump($userReq);die;

        if ($userReq) {
            return $this->responseDetail(200, false, 'Data ditemukan', $userReq);
        } else {
            return $this->responseDetail(200, false, 'Data tidak ditemukan');
        }
    }

    public function userRequest($request, $response, $args)
    {
        $requestModel = new RequestModel($this->db);

        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perPage = $request->getQueryParam('perpage');

		$userId = $request->getQueryParam('user_id');
        $userReq = $requestModel->findUserRequest(2)->setPaginate($page, $perPage);
        // var_dump($userReq);die;

        if ($userReq) {
            return $this->responseDetail(200, false, 'Data ditemukan', $userReq);
        } else {
            return $this->responseDetail(200, false, 'Data tidak ditemukan');
        }
    }

    public function groupRequest($request, $response, $args)
    {
        $requestModel = new RequestModel($this->db);

        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perPage = $request->getQueryParam('perpage');

		$groupId = $request->getQueryParam('group_id');
        $userReq = $requestModel->findGroupRequest($groupId)->setPaginate($page, $perPage);
        // var_dump($userReq);die;

        if ($userReq) {
            return $this->responseDetail(200, false, 'Data ditemukan', $userReq);
        } else {
            return $this->responseDetail(200, false, 'Data tidak ditemukan');
        }
    }

    public function allGroupRequest($request, $response, $args)
    {
        $requestModel = new RequestModel($this->db);

        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perPage = $request->getQueryParam('perpage');

		$userId = $request->getQueryParam('user_id');
        $userReq = $requestModel->findAllGroupRequest(2)->setPaginate($page, $perPage);
        $reqData = array_map("unserialize", array_unique(array_map("serialize", $userReq['data'])));
        // var_dump($reqData);die;

        if ($userReq) {
            return $this->responseDetail(200, false, 'Data ditemukan', [
                'data'          => $reqData,
                'pagination'    => $userReq['pagination']
                ]);
        } else {
            return $this->responseDetail(200, false, 'Data tidak ditemukan');
        }
    }

    public function allRequest($request, $response, $args)
    {
        $requestModel = new RequestModel($this->db);

        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perPage = $request->getQueryParam('perpage');

        $userId = $request->getQueryParam('user_id');
        $guardReq = $requestModel->findGuardRequest($userId)->setPaginate($page, $perPage);
        $userReq = $requestModel->findUserRequest($userId)->setPaginate($page, $perPage);
        $allGroup = $requestModel->findAllGroupRequest($userId)->setPaginate($page, $perPage);
        $groupReq = array_map("unserialize", array_unique(array_map("serialize", $allGroup['data'])));
        // var_dump($reqData);die;
        $count = count($groupReq) +  count($userReq['data']) + count($guardReq['data']);
        if ($userReq) {
            return $this->responseDetail(200, false, 'Data ditemukan', [
                'data'  => [
                    'group' => $groupReq,
                    'user'  => $userReq['data'],
                    'guard' => $guardReq['data'],
                    'count' => $count
                ]
            ]);
        } else {
            return $this->responseDetail(200, false, 'Data tidak ditemukan');
        }
    }

    public function requestByUser($request, $response, $args)
    {
        $reques    = new \App\Models\RequestModel($this->db);
        $guard     = new \App\Models\GuardModel($this->db);
        $userToken = new \App\Models\Users\UserToken($this->container->db);
        $guardian  = $args['guard'];
        $token     = $request->getHeader('Authorization')[0];
        $user      = $userToken->getUserId($token);

        $findGuardian = $guard->findTwo('guard_id', $guardian, 'user_id', $user);
        $findRequest  = $reques->findTwo('guard_id', $guardian, 'user_id', $user);


        if (!$findGuardian) {
            if ($findRequest || $findRequest['category'] == 1 && $findRequest['status'] == 0) {
                $data = $this->responseDetail(200, false, 'Permintaan telah dikirim');
            }else {
                $data = [
                    'user_id'  => $user,
                    'group_id' => null,
                    'guard_id' => $guardian,
                    'category' => 1,
                    'status'   => 0,
                ];
                $create = $reques->create($data);
                $data = $this->responseDetail(201, false, 'Permintaan berhasil dikirim');
            }
        } else {
            $data = $this->responseDetail(400, true, 'Anda sudah menjadi anaknya');
        }

        return $data;

    }

    public function requestByGuard($request, $response, $args)
    {
        $reques    = new \App\Models\RequestModel($this->db);
        $guard     = new \App\Models\GuardModel($this->db);
        $userToken = new \App\Models\Users\UserToken($this->container->db);
        $user      = $args['user'];
        $token     = $request->getHeader('Authorization')[0];
        $guardian  = $userToken->getUserId($token);

        $findGuardian = $guard->findTwo('guard_id', $guardian, 'user_id', $user);
        $findRequest  = $reques->findTwo('guard_id', $guardian, 'user_id', $user);

        // var_dump($findGuardian); die();
        if (!$findGuardian) {
            if ($findRequest && $findRequest['status'] == 0 || $findRequest['category'] == 2) {
                $data = $this->responseDetail(200, false, 'Permintaan telah dikirim');
            }else {
                $data = [
                    'user_id'  => $user,
                    'group_id' => null,
                    'guard_id' => $guardian,
                    'category' => 2,
                    'status'   => 0,
                ];
                $create = $reques->create($data);
                $data = $this->responseDetail(201, false, 'Permintaan berhasil dikirim');
            }
        } else {
            $data = $this->responseDetail(400, true, 'Anda sudah menjadi walinya');
        }

        return $data;

    }

    public function confirm($request, $response, $args)
    {
        $reques    = new \App\Models\RequestModel($this->db);
        $guardian  = new \App\Models\GuardModel($this->db);
        $userGroup = new \App\Models\UserGroupModel($this->db);
        $id        = $args['id'];

        $findRequest = $reques->find('id', $id);
        $userId      = $findRequest['user_id'];
        $guardId     = $findRequest['guard_id'];
        $groupId     = $findRequest['group_id'];
        $status      = $findRequest['status'];
        $category    = $findRequest['category'];

        if ($findRequest && $status == 0) {

            $data = [
                'guard_id' => $guardId,
                'user_id'  => $userId
            ];
            $guardian->add($data);
            $reques->update($id);
            if ($category == 1) {
                return $this->responseDetail(200, false, 'sekarang menjadi anak anda');
            } else {
                return $this->responseDetail(200, false, 'sekarang menjadi wali anda');
            }

        }else {
            return $this->responseDetail(400, true, 'Permintaan tidak ditemukan');
        }

    }

    public function deleteRequest($request, $response, $args)
    {
        $model = new RequestModel($this->db);
        $id    = $args['id'];

        $findRequest = $model->find('id', $id);

        if ($findRequest) {
            $model->hardDelete($id);
            return $this->responseDetail(200, false, 'Permintaan berhasil dihapus');
        }else {
            return $this->responseDetail(400, true, 'Permintaan tidak ditemukan');
        }
    }

}
