<?php

namespace App\Controllers\web;

use GuzzleHttp\Exception\BadResponseException as GuzzleException;

class PicController extends BaseController
{

    public function getMemberGroup($request, $response, $args)
	{
		$query = $request->getQueryParams();
        $userGroup = new \App\Models\UserGroupModel($this->db);
        try {
            $result = $this->client->request('GET', 'group/'.$args['id'].'/member', [
                'query' => [
                    'perpage' => 9,
                    'page'    => $request->getQueryParam('page')
                ]
            ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);
        $count = count($data['data']);
        // $findUser =
		// var_dump($data); die();
        if ($data['error'] == false) {
            return $this->view->render($response, 'pic/group-member.twig', [
                'members'	=> $data['data'],
                'group'	    => $args['id'],
                'pagination'=> $data['pagination'],
            ]);
        } else {
            $this->flash->addMessage('warning', $data['message']);
            return $response->withRedirect($this->router->pathFor('group.user'));

        }
        // var_dump($data->reporting->results);die();
    }

    public function getUnreportedItem($request, $response, $args)
    {
        // $userGroup = new \App\Models\UserGroupModel($this->db);
        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        // $getMember = $userGroup->findAll($args['id'])->setPaginate($page,40);
        // var_dump($getMember); die();
        try {
            $result = $this->client->request('GET', 'item/group/'. $args['id'], [
                'query' => [
                    'page'    => $request->getQueryparam('page'),
                    'perpage' => 10,
                    ]
                ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);

        // var_dump($data); die();
        return $this->view->render($response, 'pic/tugas.twig', [
            'items'	=> $data['data'],
            'group'	=> $args['id'],
            // 'member' => $getMember['data'],
            'pagination'	=> $data['pagination'],
        ]);
    }

    public function getReportedItem($request, $response, $args)
    {
        try {
            $result = $this->client->request('GET', 'item/group/'. $args['id'].'/all-reported', [
                'query' => [
                    'page'    => $request->getQueryparam('page'),
                    'perpage' => 5,
                    ]
                ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data); die();
        return $this->view->render($response, 'pic/laporan.twig', [
            'items'	=> $data['data'],
            'group'	=> $args['id'],
            'pagination'	=> $data['pagination'],
        ]);
    }

    public function deleteTugas($request, $response, $args)
	{
        $item = new \App\Models\Item($this->db);
        $findItem = $item->find('id', $args['id']);
		try {
			$client = $this->client->request('DELETE', 'item/'.$args['id']);

			$content = json_decode($client->getBody()->getContents(), true);
            $this->flash->addMessage('success', 'Tugas telah berhasil dihapus');
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents(), true );
			$this->flash->addMessage('warning', 'Anda tidak diizinkan menghapus tugas ini ');
		}
		// return $this->view->render($response, 'pic/tugas.twig');
        // var_dump($content); die();
        return $response->withRedirect($this->router->pathFor('pic.item.group',['id' => $findItem['group_id']]));
	}

    public function createItem($request, $response)
    {

            $query = $request->getQueryParams();
            $group = $request->getParam('group');
            if ($request->getParam('user_id') == "null")
            {
                $userId = null;
            } else {
                $userId = $request->getParam('user_id');
            }
            // var_dump($request->getParam('user_id')); die();
            try {
                $result = $this->client->request('POST', 'item', [
                    'form_params' => [
                        'name'          => $request->getParam('name'),
                        'description'   => $request->getParam('description'),
                        'recurrent'     => $request->getParam('recurrent'),
                        'start_date'    => $request->getParam('start_date'),
                        'user_id'    	=> $userId,
                        'group_id'      => $request->getParam('group'),
                        'creator'    	=> $_SESSION['login']['id'],
                        'privacy'       => $request->getParam('public'),
                    ]
                ]);
            } catch (GuzzleException $e) {
                $result = $e->getResponse();
            }

            $content = $result->getBody()->getContents();
            $contents = json_decode($content, true);
            // var_dump($contents); die();
            if ($contents['code'] == 201) {
                $this->flash->addMessage('success', $contents['message']);
                return $response->withRedirect($this->router->pathFor('pic.item.group',['id' => $group ]));
            } else {
                // foreach ($contents['message'] as $value ) {
                // }
                $_SESSION['errors'] = $contents['message'];
                $_SESSION['old']    = $request->getParams();
                // var_dump($_SESSION['errors']); die();
                return $response->withRedirect($this->router->pathFor('pic.item.group',['id' => $group ]));
            }


    }

    public function showItem($request, $response, $args)
    {
        $user = new \App\Models\Users\UserModel($this->db);
        $userGroup = new \App\Models\UserGroupModel($this->db);
        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        // $id = $_SESSION['login']['id'];
        try {
            $result = $this->client->request('GET', 'item/show/'.$args['id'].'?'
            . $request->getUri()->getQuery());
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);

        try {
            $comment = $this->client->request('GET', 'item/comment/'.$args['id'].'?'
            . $request->getUri()->getQuery());
        } catch (GuzzleException $e) {
            $comment = $e->getResponse();
        }

        $getMember = $userGroup->findAll($data['data']['group_id'])->setPaginate($page,40);
        $allComment = json_decode($comment->getBody()->getContents(), true);

        $userId = $data['data']['user_id'];
        $findUser = $user->find('id', $userId);
        // var_dump($data); die();

        if ($data['data']) {
            return $this->view->render($response, 'pic/show-item-tugas.twig', [
                'items'        => $data['data'],
                'comment'      => $allComment['data'],
                'member'       => $getMember['data'],
                'user_data'    => $findUser,
                'user'         => $userId,
            ]);
        } else {
            $this->flash->addMessage('error', $data['message']);
            return $response->withRedirect($this->router->pathFor('home'));

        }

    }

    public function getSearchUser($request, $response, $args)
    {
        $user = new \App\Models\Users\UserModel($this->db);
        $findUser = $user->find('id', $args['id']);
        $userId['id']   = $findUser['id'];
        $_SESSION['guard'] = $userId['id'];
        return $this->view->render($response, 'pic/search-user.twig', $userId);

    }

    public function searchUser($request, $response, $args)
    {
        $user = new \App\Models\Users\UserModel($this->db);

        // var_dump($_SESSION['guard']); die();
        $search = $request->getParam('search');
        $_SESSION['search'] = $search;
        // $userId = $_SESSION['login']['id'];
        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perpage = $request->getQueryParam('perpage');
        $result = $user->search($search, $_SESSION['guard'])->setPaginate($page, 8);


        $data['guard'] = $_SESSION['guard'];
        $data['users'] = $result['data'];
        $data['count']    = count($data['users']);
        $data['pagination'] = $result['pagination'];
        $data['search'] = $_SESSION['search'];
        // var_dump($data['users']); die();
        if (!empty($search)) {

            return $this->view->render($response, 'pic/search-user.twig', $data);
        }

    }

    public function editItem($request, $response, $args)
    {

            $query = $request->getQueryParams();
            $group = $request->getParam('group');
            $itemId = $args['id'];
            if ($request->getParam('user_id') == "null")
            {
                $userId = null;
            } else {
                $userId = $request->getParam('user_id');
            }
            // var_dump($request->getParam('user_id')); die();
            try {
                $result = $this->client->request('PUT', 'item/'.$args['id'], [
                    'form_params' => [
                        'name'          => $request->getParam('name'),
                        'description'   => $request->getParam('description'),
                        'recurrent'     => $request->getParam('recurrent'),
                        'start_date'    => $request->getParam('start_date'),
                        'user_id'    	=> $userId,
                        'group_id'      => $request->getParam('group'),
                        'creator'    	=> $_SESSION['login']['id'],
                        'privacy'        => $request->getParam('privacy'),
                    ]
                ]);
            } catch (GuzzleException $e) {
                $result = $e->getResponse();
            }

            $content = $result->getBody()->getContents();
            $contents = json_decode($content, true);
            var_dump($contents); die();
            if ($contents['code'] == 201) {
                $this->flash->addMessage('succes', $contents['message']);
                return $response->withRedirect($this->router->pathFor('web.pic.show.item',['id' => $id ]));
            } else {
                // foreach ($contents['message'] as $value ) {
                // }
                $_SESSION['errors'] = $contents['message'];
                $_SESSION['old']    = $request->getParams();
                return $response->withRedirect($this->router->pathFor('web.pic.show.item',['id' => $id ]));
                // var_dump($_SESSION['errors']); die();
            }


    }

}
