<?php

namespace App\Controllers\api;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\Users\UserModel;
use App\Models\RequestModel;
use App\Models\GroupModel;
use App\Models\GuardModel;
use App\Models\Item;


class AdminController extends BaseController
{
	public function countAll($request, $response)
    {
		$item = new Item($this->db);
		$user = new UserModel($this->db);
		$group = new GroupModel($this->db);
		$guard = new GuardModel($this->db);
		$request = new RequestModel($this->db);

		$getuser = $user->getAll();
		$userRequest = $request->findTwo('category', 2, 'status', 0);
		$guardRequest = $request->findTwo('category', 1, 'status', 0);
		$groupRequest = $request->findTwo('category', 0, 'status', 0);

		$directory = 'assets/images/';
		$jpg = glob($directory . '*.jpg');
		$png = glob($directory . '*.png');
		$gif = glob($directory . '*.gif');

		$count = [
			'user' 		=> count($user->getAll()),
			'item' 		=> count($item->getAll()),
			'group' 	=> count($group->getAll()),
			'user_req' 	=> count($userRequest),
			'guard_req' => count($guardRequest),
			'group_req' => count($groupRequest),
			'guard' 	=> count($guard->findAllGuard()),
			'image'		=> (count($png) + count($jpg) + count($gif))
		];

		if ($count) {
			return $this->responseDetail(200, false, 'Data tersedia', [
				'data' => [
					'count'  => $count
				],
			]);
		} else {
			return $this->responseDetail(404, false,'Data tidak ditemukan');
		}
	}

	public function getAllImage($request, $response)
	{
		$directory = 'assets/images/';

		if (! is_dir($directory)) {
			exit('Invalid diretory path');
		}

		$files = array();
		$base = $request->getUri()->getBaseUrl();
		foreach (scandir($directory) as $file) {
			if ('.' === $file) continue;
			if ('..' === $file) continue;

			$files[] = "$base/assets/images/$file";
		}

		$page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
		$perPage = $request->getQueryParam('perpage');
		$result = $this->paginateArray($files, $page, $perPage);

		if ($files) {
			return $this->responseDetail(200, false, 'Data tersedia', $result);
		} else {
			return $this->responseDetail(404, false,'Data tidak ditemukan');
		}
	}

}
