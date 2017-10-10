<?php

namespace App\Controllers\api;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\GroupModel;
use App\Models\UserGroupModel;

class GroupController extends BaseController
{
	//Get All Group
	function index(Request $request, Response $response)
	{
		$group = new \App\Models\GroupModel($this->db);
		$get = $group->getAll();
		$countGroups = count($get);

		if ($get) {
			$page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
			$perPage = $request->getQueryParam('perpage');
			$getGroup = $group->getAllGroup()->setPaginate($page, $perPage);

			if ($getGroup) {
				$data = $this->responseDetail(200, false,  'Data tersedia', [
						'data'			=>	$getGroup['data'],
						'pagination'	=>	$getGroup['pagination'],
					]);
			} else {
				$data = $this->responseDetail(404, true, 'Data tidak ditemukan');
			}
		} else {
			$data = $this->responseDetail(204, false, 'Tidak ada konten');
		}
		return $data;
	}

	//Find group by id
	function findGroup(Request $request, Response $response, $args)
	{
		$group = new \App\Models\GroupModel($this->db);
		$findGroup = $group->find('id', $args['id']);

		if ($findGroup) {
			$data = $this->responseDetail(200, false, 'Data tersedia', [
					'data'	=>	$findGroup
				]);
		} else {
			$data = $this->responseDetail(404, true, 'Data tidak ditemukan');
		}
		return $data;
	}

	//Create group
	public function add(Request $request, Response $response)
	{
		$rules = [
			'required' => [
				['name']
			]
		];

		$this->validator->labels([
			'name' 			=>	'Nama grup',
			'description'	=>	'Deskripsi',
			'image'			=>	'Foto',
		]);

		$this->validator->rules($rules);
		if ($this->validator->validate()) {
			$groups = new \App\Models\GroupModel($this->db);
			$userGroups = new \App\Models\UserGroupModel($this->db);

			$data = $request->getParams();
			$token = $request->getHeader('Authorization')[0];
			$data['creator'] = $groups->getUserByToken($token)['id'];
			$addGroup = $groups->add($data);
			$findGroup = $groups->find('id', $addGroup);

			$dataPic = [
				'user_id'	=> $data['creator'],
				'group_id'	=> $addGroup,
				'status'	=> 1
			];
			$setPicGroup = $userGroups->add($dataPic);

			return $this->responseDetail(201, false, 'Grup '.$findGroup['name'].' berhasil dibuat', [
					'data'	=>	$findGroup
				]);
		} else {
			return $this->responseDetail(400, true, $this->validator->errors());
		}
	}

	//Edit group
	public function update(Request $request, Response $response)
	{
		$group = new \App\Models\GroupModel($this->db);
		$userGroup = new \App\Models\UserGroupModel($this->db);

		$token = $request->getHeader('Authorization')[0];
		$userId = $group->getUserByToken($token);
		$groupId = $request->getParsedBody()['id'];
		$findGroup = $group->find('id', $groupId);
		$member = $userGroup->findTwo('group_id', $groupId, 'user_id', $userId['id']);

		if ($findGroup) {
			if ($userId['status'] == 1 || $member[0]['status'] == 1) {
				$x = $group->updateData($request->getParsedBody(), $groupId);
				$afterUpdate = $group->find('id', $groupId);

				return $this->responseDetail(201, false, 'Info grup berhasil diperbarui', [
					'data'	=>	$afterUpdate
				]);
			} else {
				return $this->responseDetail(401, true, 'Anda tidak boleh mengubah info group');
			}
		} else {
			return $this->responseDetail(404, true, 'Grup tidak ditemukan');
		}
	}

	//Set user as member of group
	public function setUserGroup(Request $request, Response $response)
	{
		$rules = [
			'required' => [
				['group_id'],
				['user_id'],
				['status']
			]
		];

		$this->validator->rules($rules);

		$this->validator->labels([
			'group_id' 	=>	'ID Group',
			'user_id'	=>	'ID User',
		]);

		if ($this->validator->validate()) {
			$userGroup = new UserGroupModel($this->db);
			$findUserGroup = $userGroup->findTwo('user_id', $request->getParams()['user_id'],
								'group_id',  $request->getParams()['group_id']);
			if (!empty($findUserGroup[0])) {
				return $this->responseDetail(400, true, 'Sudah bergabung dengan grup');
			} else {
				$userGroup->add($request->getParams());
				return $this->responseDetail(201, false, 'Berhasil ditambahkan menjadi anggota group');
			}
		} else {
			return $this->responseDetail(400, true, $this->validator->errors());
		}
	}

	//Get all user in group
	public function getAllUserGroup(Request $request, Response $response, $args)
	{
		$userGroup = new \App\Models\UserGroupModel($this->db);
		$users = new \App\Models\Users\UserModel($this->container->db);
		$userToken = new \App\Models\Users\UserToken($this->container->db);

		$finduserGroup = $userGroup->findUsers('group_id', $args['id']);
		$token = $request->getHeader('Authorization')[0];
		$findUser = $userToken->find('token', $token);
		$group = $userGroup->findUser('user_id', $findUser['user_id'], 'group_id', $args['id']);
		$user = $users->find('id', $findUser['user_id']);
		$perpage = $request->getQueryParam('perpage');

		if ($group) {
			if ($finduserGroup || $user['status'] == 1) {
				$page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
				$perPage = $request->getQueryParam('perpage');
				$findAll = $userGroup->findAll($args['id'])->setPaginate($page, $perPage);

				$data = $this->responseDetail(200, false, 'Berhasil', [
					'data'			=>	$findAll['data'],
					'pagination'	=>	$findAll['pagination']
				]);
			} else {
				$data = $this->responseDetail(404, true, 'User tidak ditemukan di dalam group');
			}
		} else {
			$data = $this->responseDetail(403, true, 'Kamu tidak terdaftar di dalam group');
		}
		return $data;
	}

	//Get one user in group
	public function getUserGroup(Request $request, Response $response, $args)
	{
		$userGroup = new \App\Models\UserGroupModel($this->db);
		$users = new \App\Models\Users\UserModel($this->container->db);
		$userToken = new \App\Models\Users\UserToken($this->container->db);

		$finduserGroup = $userGroup->findUser('group_id', $args['group'], 'user_id', $args['id']);
		$token = $request->getHeader('Authorization')[0];
		$findUser = $userToken->find('token', $token);
		$group = $userGroup->findUser('user_id', $findUser['user_id'], 'group_id', $args['group']);
		$user = $users->find('id', $findUser['user_id']);
		$getUser = $userGroup->getUser($args['group'], $args['id']);

		if ($group) {
			if ($finduserGroup) {
				$data = $this->responseDetail(200, false, 'Berhasil', [
					'data'	=>	$getUser
				]);
			} else {
				$data = $this->responseDetail(404, true, 'User tidak ditemukan didalam group');
			}
		} else {
			$data = $this->responseDetail(404, true, 'Kamu tidak terdaftar didalam group');
		}
		return $data;
	}

	//Delete user from group
	public function deleteUser(Request $request, Response $response, $args)
	{
		$userGroup = new \App\Models\UserGroupModel($this->db);
		$finduserGroups = $userGroup->findTwo('user_id', $args['id'], 'group_id', $args['group']);

		if ($finduserGroups) {
			$userGroup->hardDelete($finduserGroups[0]['id']);

			$data = $this->responseDetail(200, false, 'User berhasil dikeluarkan dari group', [
					'data'	=>	$userGroup
				]);
		} else {
			$data = $this->responseDetail(404, true, 'Data tidak ditemukan');
		}
		return $data;
	}

	//Set user in group as member
	public function setAsMember(Request $request, Response $response, $args)
	{
		$userGroup = new \App\Models\UserGroupModel($this->db);

		$finduserGroup = $userGroup->find('id', $args['id']);

		if ($finduserGroup) {
			$userGroup->setUser($finduserGroup['id']);

			$data = $this->responseDetail(200, false, 'Pengguna berhasil dijadikan Anggota	',  [
					'data'	=>	$userGroup
				]);
		} else {
			$data = $this->responseDetail(404, true, 'User tidak ditemukan didalam group');
		}
		return $data;
	}

	//Set user in group as PIC
	public function setAsPic(Request $request, Response $response, $args)
	{
		$userGroup = new \App\Models\UserGroupModel($this->db);

		$finduserGroup = $userGroup->find('id', $args['id']);

		if ($finduserGroup) {
			$userGroup->setPic($finduserGroup['id']);

			$data = $this->responseDetail(200, false, 'User berhasil dijadikan PIC', [
					'data'	=>	$userGroup
				]);
		} else {
			$data = $this->responseDetail(404, true, 'User Tidak ditemukan di dalam group');
		}
		return $data;
	}

	//Set user in group as guardian
	public function setAsGuardian(Request $request, Response $response, $args)
	{
		$userGroup = new \App\Models\UserGroupModel($this->db);

		$finduserGroup = $userGroup->findUser('user_id', $args['id'], 'group_id', $args['group']);

		if ($finduserGroup) {
			$userGroup->setGuardian($finduserGroup['id']);

			$data = $this->responseDetail(200, false, 'User berhasil dijadikan Guardian', [
					'data'	=>	$finduserGroup
				]);
		} else {
			$data = $this->responseDetail(404, true, 'User tidak ditemukan di dalam group');
		}
		return $data;
	}

	public function getGroup(Request $request, Response $response)
	{
		$group = new GroupModel($this->db);
		$userGroup = new \App\Models\UserGroupModel($this->db);
		$userToken = new \App\Models\Users\UserToken($this->db);

		$token = $request->getHeader('Authorization')[0];
		$userId = $userToken->getUserId($token);

		if ($group) {
			$getGroup = $userGroup->findAllGroup($userId);
			$page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
			$perPage = $request->getQueryParam('perpage');
			$get = $group->getAllGroup()->setPaginate($page, $perPage);

			$data = $this->responseDetail(200, false, 'Berhasil menampilkan data', [
					'data'			=>	$get['data'],
					'pagination'	=>	$get['pagination']
				]);
		}else {
			$data = $this->responseDetail(404, true, 'Data tidak ditemukan');
		}
		return $data;
	}

	//Find group by id
	public function delGroup(Request $request, Response $response, $args)
	{
		$group = new GroupModel($this->db);
		$userGroup = new UserGroupModel($this->db);
		$userToken = new \App\Models\Users\UserToken($this->db);

		$token = $request->getHeader('Authorization')[0];
		$userId = $userToken->getUserId($token);
		$findGroup = $group->find('id', $args['id']);
		$pic = $userGroup->findTwo('group_id', $args['id'], 'user_id', $userId);

		if ($findGroup) {
			if ($userId == 1 || $pic[0]['status'] == 1) {
				$delete = $group->hardDelete($args['id']);
				if (file_exists('assets/images/'.$findGroup['image'])) {
					unlink('assets/images/'.$findGroup['image']);
				}
				return $this->responseDetail(200, false, 'Grup berhasil di hapus');
			} else {
				return $this->responseDetail(400, true, 'Anda tidak memiliki akses untuk menghapus grup ini');
			}
		} else {
			return $this->responseDetail(404, true, 'Grup tidak ditemukan');
		}
	}

	//Set user as member of group
	public function joinGroup(Request $request, Response $response, $args)
	{
		$userGroup = new UserGroupModel($this->db);
		$token = $request->getHeader('Authorization')[0];
		$userToken = new \App\Models\Users\UserToken($this->db);
		$userId = $userToken->getUserId($token);

		$findUser = $userGroup->findTwo('user_id', $userId, 'group_id', $args['id']);

		$data = [
			'group_id' 	=> 	$args['id'],
			'user_id'	=>	$userId,
		];

		if ($findUser[0]) {
			$data = $this->responseDetail(400, true, 'Anda sudah tergabung ke grup');
		} else {
			$addMember = $userGroup->createData($data);

			$data = $this->responseDetail(200, false, 'Anda berhasil bergabung dengan grup',  [
					'data'	=>	$data
				]);
		}
		return $data;
	}

	//leave group
	public function leaveGroup(Request $request, Response $response, $args)
	{
		$userGroup = new UserGroupModel($this->db);
		$posts = new \App\Models\PostModel($this->db);
		$userToken = new \App\Models\Users\UserToken($this->db);

		$token = $request->getHeader('Authorization')[0];
		$userId = $userToken->getUserId($token);
		$group = $userGroup->findTwo('user_id', $userId, 'group_id', $args['id']);

		if ($group[0]) {
			$leaveGroup = $userGroup->hardDelete($group[0]['id']);
			$data = $this->responseDetail(200, false, 'Anda telah meninggalkan grup');
		} else {
			$data = $this->responseDetail(400, true, 'Anda tidak tergabung di grup ini');
		}
		return $data;
	}

	//search group
	public function searchGroup(Request $request, Response $response)
    {
        $group = new GroupModel($this->db);
		$userToken = new \App\Models\Users\UserToken($this->db);

		$token = $request->getHeader('Authorization')[0];
		$userId = $userToken->getUserId($token);
        $search = $request->getParams()['search'];

		$data['groups'] =  $group->search($search);
        $data['count'] = count($data['groups']);

        if ($data['count']) {
        	$data = $this->responseDetail(200, false, 'Berhasil menampilkan data search', [
        			'data'	=>	$data
        		]);
        } else {
        	$data = $this->responseDetail(404, true, 'Data tidak ditemukan');
        }

        return $data;
    }

    public function postImage($request, $response, $args)
    {
        $group = new GroupModel($this->db);

        $findGroup = $group->find('id', $args['id']);
        if (!$findGroup) {
            return $this->responseDetail(404, 'Grup tidak ditemukan');
        }

        if (!empty($request->getUploadedFiles()['image'])) {
            $storage = new \Upload\Storage\FileSystem('assets/images');
            $image = new \Upload\File('image', $storage);

            $image->setName(uniqid('grp-'.date('Ymd').'-'));
            $image->addValidations(array(
                new \Upload\Validation\Mimetype(array('image/png', 'image/gif',
                'image/jpg', 'image/jpeg')),
                new \Upload\Validation\Size('512K')
            ));

            $image->upload();
			$data['image'] = $image->getNameWithExtension();

			$x = $group->updateData($data, $args['id']);
			$newGroup = $group->find('id', $args['id']);

			if (file_exists('assets/images/'.$findGroup['image'])) {
				unlink('assets/images/'.$findGroup['image']);
			}
            return  $this->responseDetail(200, false, 'Foto grup berhasil diperbarui', [
                'data' => $newGroup
            ]);
        } else {
            return $this->responseDetail(400, true, 'File foto belum dipilih');
        }
    }

    public function inActive($request, $response)
    {
    	$group = new GroupModel($this->db);

    	$getGroup = $group->getInActive();
    	$countGroups = count($getGroup);
		$page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
		$perPage = $request->getQueryParam('perpage');
		$get = $group->getAllGroupNonActive()->setPaginate($page, $perPage);

    	if ($countGroups == 0) {
    		return $this->responseDetail(404, true, 'Data tidak ditemukan');
    	} else {
    		return $this->responseDetail(200, false, 'Berhasil menampilkan data', [
    			'data' 			=> 	$get['data'],
    			'pagination'	=>	$get['pagination']
    		]);
    	}
    }

    public function getPicGroup($request, $response)
	{
		$userGroup = new \App\Models\UserGroupModel($this->db);
		$userToken = new \App\Models\Users\UserToken($this->db);

		$token = $request->getHeader('Authorization')[0];
		$userId = $userToken->getUserId($token);
		$getGroup = $userGroup->picGroup($userId);
		$page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
		$perPage = $request->getQueryParam('perpage');
		$get = $getGroup->setPaginate($page, $perPage);

		if ($getGroup == 0) {
			return $this->responseDetail(404, true, 'Data tidak ditemukan');
		} else {
			return $this->responseDetail(200, false, 'Berhasil menampilkan data', [
				'data'			=>	$get['data'],
				'pagination'	=>	$get['pagination']
			]);
		}
	}

    public function setInActive(Request $request, Response $response, $args)
    {
		$group = new GroupModel($this->db);

		$findGroup = $group->find('id', $args['id']);

    	if (!$findGroup) {
    		return $this->responseDetail(400, true, 'Ada masalah saat menghapus data');
    	} else {
    		$group->softDelete($args['id']);
    		return $this->responseDetail(200, false, 'Berhasil menghapus data');
    	}
    }

    //Set restore group
	public function restore(Request $request, Response $response, $args)
	{
		$group = new GroupModel($this->db);

		$findGroup = $group->find('id', $args['id']);

		if (!$findGroup) {
			return $this->responseDetail(400, true, 'Ada masalah saat mengembalikan data');
		} else {
			$group->restore($args['id']);
			$get = $group->find('id', $args['id']);
			return $this->responseDetail(200, false, 'Berhasil mengembalikan data', [
				'data'	=>	$get
			]);
		}
	}

	public function getPic($request, $response, $args)
	{
		$userGroup = new \App\Models\UserGroupModel($this->db);
		$userToken = new \App\Models\Users\UserToken($this->db);

		$token = $request->getHeader('Authorization')[0];
		$userId = $userToken->getUserId($token);
		$getGroup = $userGroup->findAllUser($userId);

		if ($getGroup) {
			return $this->responseDetail(200, false, 'Berhasil menampilkan data', [
				'data'	=>	$getGroup
			]);
		} else {
			return $this->responseDetail(400, true, 'Ada kesalahan saat menampilkan data');
		}
	}

	//Get all user in group
	public function getGroupMember($request, $response, $args)
	{
		$userGroup = new UserGroupModel($this->db);
		$groups = new GroupModel($this->db);

		$token = $request->getHeader('Authorization')[0];
		$user = $groups->getUserByToken($token);
		$page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
		$perPage = $request->getQueryParam('perpage');
		$userId = $request->getQueryParam('user_id');
		$groupId = $request->getQueryParam('group_id');
		$pic = $userGroup->findTwo('group_id', $groupId, 'user_id', $userId);
		$data = $userGroup->findMember($groupId);
		$result = $this->paginateArray($data, $page, $perPage);

		if ($user['id'] == 1 || $pic[0]['status'] == 1 || $pic[0]['status'] == 0) {
			return $this->responseDetail(200, false, 'Berhasil menampilkan data', [
				'data'			=> $result['data'],
				'pagination'	=> $result['pagination'],
			]);
		} else {
			return $this->responseDetail(401, true, 'Anda tidak memiliki akses ke grup ini!');
		}
	}

	//Get all PIC in group
	public function getGroupPic($request, $response, $args)
	{
		$userGroup = new UserGroupModel($this->db);
		$groups = new GroupModel($this->db);

		$token = $request->getHeader('Authorization')[0];
		$user = $groups->getUserByToken($token);
		$page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
		$perPage = $request->getQueryParam('perpage');
		$userId = $request->getQueryParam('user_id');
		$groupId = $request->getQueryParam('group_id');
		$pic = $userGroup->findTwo('group_id', $groupId, 'user_id', $userId);
		$data = $userGroup->findPic($groupId);
		$result = $this->paginateArray($data, $page, $perPage);
		if ($userId == 1 || $pic[0]['status'] == 1 || $pic[0]['status'] == 0) {
			return $this->responseDetail(200, false, 'Berhasil menampilkan data', [
				'data'			=> $result['data'],
				'pagination'	=> $result['pagination'],
			]);
		} else {
			return $this->responseDetail(401, true, 'Anda tidak memiliki akses ke grup ini!');
		}
	}

	//Get all PIC in group
	public function getAllGroupMember($request, $response, $args)
	{
		$userGroup = new UserGroupModel($this->db);
		$groups = new GroupModel($this->db);

		$token = $request->getHeader('Authorization')[0];
		$user = $groups->getUserByToken($token);
		$page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
		$perPage = $request->getQueryParam('perpage');
		$userId = $request->getQueryParam('user_id');
		$groupId = $request->getQueryParam('group_id');
		$pic = $userGroup->findTwo('group_id', $groupId, 'user_id', $userId);
		$data = $userGroup->findAllMember($groupId);
		$result = $this->paginateArray($data, $page, $perPage);

		if ($userId == 1 || $pic[0]['status'] == 1 || $pic[0]['status'] == 0) {
			return $this->responseDetail(200, false, 'Berhasil menampilkan data', [
				'data'			=> $result['data'],
				'pagination'	=> $result['pagination'],
			]);
		} else {
			return $this->responseDetail(401, true, 'Anda tidak memiliki akses ke grup ini!');
		}
	}

	//Get all user in group
	public function getNotMember($request, $response, $args)
	{
		$userGroup = new UserGroupModel($this->db);
		$userToken = new \App\Models\Users\UserToken($this->db);

		$token = $request->getHeader('Authorization')[0];
		$userId = $userToken->getUserId($token);
		$page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
		$perPage = $request->getQueryParam('perpage');
		$users = $userGroup->notMember($args['id'])->setPaginate($page, $perPage);
		$pic = $userGroup->findUser('group_id', $args['id'], 'user_id', $userId);

		if ($userId == 1 || $pic['status'] == 1) {
			return $this->responseDetail(200, false, 'Berhasil menampilkan data', [
				'data' 			=> 	$users,
				'pagination'	=> 	$pic['pagination']
			]);
		} else {
			return $this->responseDetail(400, true, 'Anda tidak memiliki akses ke user ini!');
		}
	}

	//Post create group
	public function createByUser($request, $response)
	{
		$userToken = new \App\Models\Users\UserToken($this->db);

		$rules = ['required' => [['name'], ['description']] ];

		$this->validator->labels([
			'name' 			=>	'Name',
			'description'	=>	'Description',
			'image'			=>	'Image',
		]);
		$this->validator->rules($rules);

		$token = $request->getHeader('Authorization')[0];
		$userId = $userToken->getUserId($token);

		if ($this->validator->validate()) {
			$dataGroup = [
				'name' 			=>	$request->getParams()['name'],
				'description'	=>	$request->getParams()['description'],
				'image'			=>	$request->getParams()['image'],
				'creator'       =>  $userId
			];

			$group = new GroupModel($this->db);
			$userGroup = new \App\Models\UserGroupModel($this->db);

			$addGroup = $group->add($dataGroup);

			$data = [
				'group_id' 	=> 	$addGroup,
				'user_id'	=>	$userId,
				'status'	=>	1,
			];

			$addUserGroup = $userGroup->add($data);
			$newUserGroup = $userGroup->find('id', $addUserGroup);

			return $this->responseDetail(201, false, 'Berhasil Membuat group', [
				'data'	=>	$newUserGroup
			]);

		} else {
			return $this->responseDetail(401, true, 'Ada kesalahan saat membuat group');
		}
	}

	//Set user as member of group
	public function setMemberGroup($request, $response, $args)
	{
		$userGroups = new UserGroupModel($this->db);
		$userToken = new \App\Models\Users\UserToken($this->db);

		$token = $request->getHeader('Authorization')[0];
		$user = $userToken->getUserId($token);
		$userId = $request->getParams()['user_id'];
		$groupId = $request->getParams()['group_id'];
		$pic = $userGroups->findTwo('group_id', $groupId, 'user_id', $user);
		$userGroup = $userGroups->findTwo('group_id', $groupId, 'user_id', $userId);

		if (!$userGroup) {
			if ($user == 1 || $pic[0]['status'] == 1) {
				$data = [
					'group_id' 	=> 	$groupId,
					'user_id'	=>	$userId,
					'status'	=>	0
				];

				$addMember = $userGroups->createData($data);
				$findMember = $userGroups->findTwo('user_id', $userId, 'group_id', $groupId);

				return $this->responseDetail(201, false, 'Anda berhasil menambahkan user kedalam group!', [
					'data'	=>	$findMember
				]);
			} else {
				return $this->responseDetail(403, true, 'Anda tidak memiliki akses !');
			}

		}else {
			return $this->responseDetail(400, true, 'Member sudah tergabung!');
		}
	}

	public function getGeneralGroup($request, $response)
	{
		$userGroup = new \App\Models\UserGroupModel($this->db);
        $userToken = new \App\Models\Users\UserToken($this->db);

        $token = $request->getHeader('Authorization')[0];
		$userId = $userToken->getUserId($token);
		$getGroup = $userGroup->generalGroup($userId);
		$page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
		$perPage = $request->getQueryParam('perpage');

		if ($getGroup) {
			$result = $this->paginateArray($getGroup, $page, $perPage);

			return $this->responseDetail(200, false, 'Grup tersedia', [
				'data' 			=> 	$result['data'],
				'pagination' 	=> 	$result['pagination']
			]);
		} else {
			return $this->responseDetail(404, true, 'Group tidak tersedia');
		}
	}

	public function memberStatus($request, $response, $args)
	{
		$userGroup = new \App\Models\UserGroupModel($this->db);

		$token = $request->getHeader('Authorization')[0];
		$user = $userGroup->getUserByToken($token);
		$member = $userGroup->findTwo('user_id', $user['id'], 'group_id', $args['id']);

		if (!$member[0]) {
			return $this->responseDetail(403, true, 'Anda belum tergabung ke grup ini');
		} elseif ($member[0]['status'] == 1) {
			return $this->responseDetail(200, false, 'Berhasil masuk grup sebagai PIC', [
				'data'	=> 'PIC'
			]);
		} elseif ($member[0]['status'] == 0) {
			return $this->responseDetail(200, false, 'Berhasil masuk grup sebagai member', [
				'data'	=> 'member'
			]);
		}
	}

}
