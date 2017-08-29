<?php
namespace App\Controllers\api;


use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\GuardModel;
use App\Models\Users\UserModel;

class GuardController extends BaseController
{
    // Function Create Guardian
    public function createGuardian(Request $request, Response $response, $args)
    {
        $guard = new \App\Models\GuardModel($this->db);
        $userToken = new \App\Models\Users\UserToken($this->db);
        // $userId = $userToken->getUserId($token);
        $userId = $args['user'];
        $guardId = $args['guard'];
        $findGuard = $guard->findTwo('guard_id', $guardId, 'user_id', $userId);

        $data = [
            'guard_id'  =>  $guardId,
            'user_id' => $userId,
        ];

        if (!$findGuard) {
            $addGuardian = $guard->add($data);

            $data = $this->responseDetail(200, false, 'Pengguna berhasil ditambahkan ', [
                'data' => $data
            ]);
        } else {
            $data = $this->responseDetail(404, true, 'Pengguna sudah ditambahkan');
        }
        return $data;
    }

    // Function Delete Guardian
    public function deleteGuardian(Request $request, Response $response, $args)
    {
        $guard = new GuardModel($this->db);
        $userToken = new \App\Models\Users\UserToken($this->db);

        $token = $request->getHeader('Authorization')[0];
        $findUser = $userToken->find('token', '72af357cae642386ccaaf5c4e86b669a');
        $findGuard = $guard->findGuards('user_id', $findUser['user_id'], 'guard_id', $args['id']);
           // var_dump($findGuard);die();
        // $query = $request->getQueryParams();
           if ($findGuard && $findUser['user_id']) {
               $oh = $guard->deleteGuard($args['id']);
               var_dump($oh);die();
               $data = $this->responseDetail(200, false, 'Guardian berhasil dihapus', [
                    'data' => $findGuard
                ]);
           } else {
               $data = $this->responseDetail(404, true, 'Data tidak ditemukan');
           }
           return $data;
    }

    // Function show user by guard_id
    public function getUserByGuard(Request $request, Response $response, $args)
    {
        $guards = new GuardModel($this->db);

        $token = $request->getHeader('Authorization')[0];
        $user = $guards->getUserByToken($token);
        $guardId = $request->getQueryParam('id');
        $findGuard = $guards->find('guard_id', $guardId);

        if ($findGuard || $user) {
            $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
            $perPage = $request->getQueryParam('perpage');
            $findAll = $guards->findAllUser($guardId)->setPaginate($page, $perPage);

            return $this->responseDetail(200, false, 'Berhasil menampilkan user', [
                'data'          =>  $findAll['data'],
                'pagination'    =>  $findAll['pagination']
            ]);
        } else {
            return $this->responseDetail(404, true, 'User tidak ditemukan');
        }
    }

    // Function show guard by user_id
    public function getGuardByUser(Request $request, Response $response, $args)
    {
        $guard = new GuardModel($this->db);
        $userToken = new \App\Models\Users\UserToken($this->container->db);

        $token = $request->getHeader('Authorization')[0];
        // $userId = $userToken->find('token', '90c4a9cebeaae6515c7dd4d265271bf6');
        $userId = $userToken->getUserId($token);
        $guards = $guard->findGuards('guard_id', $args['id'], 'user_id', $userId['user_id']);
// var_dump($guards);die();
        // $query = $request->getQueryParams();
         if ($userId['user_id'] || $guards ) {
             $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
             $perPage = $request->getQueryParam('perpage');
                $userGuard = $guard->getUserId($userId['user_id'])->setPaginate($page, $perPage);
                // var_dump($userGuard);die();
             $data = $this->responseDetail(200, false, 'Berhasil menampilkan data', [
                    'data'    =>  $userGuard['data'],
                    'pagination'      =>  $userGuard['pagination'],
                ]);
        } else {
            $data = $this->responseDetail(400, true, 'Gagal menampilkan data');
        }
        return $data;
      }

    // Function get user by guard login
    public function getUser(Request $request, Response $response, $args)
    {
        $guard = new GuardModel($this->db);
        $userToken = new \App\Models\Users\UserToken($this->container->db);

        $token = $request->getHeader('Authorization')[0];
        $userId = $userToken->getUserId($token);
        // $query = $request->getQueryParams();
         if ($userId) {
             $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
             $perPage = $request->getQueryParam('perpage');
                $userGuard = $guard->findAllUser($userId)->setPaginate($page, $perPage);
                // var_dump($userGuard);die();
             $data = $this->responseDetail(200, false, 'Berhasil menampilkan user', [
                    'data'    =>  $userGuard['data'],
                    'pagination'      =>  $userGuard['pagination'],
                ]);
        } else {
            $data = $this->responseDetail(400, true, 'Gagal menampilkan user');
        }
        return $data;
    }
}
