<?php

namespace App\Controllers\web;

use GuzzleHttp\Exception\BadResponseException as GuzzleException;

class PicController extends BaseController
{

    public function getMemberGroup($request, $response)
	{
        $id = $_SESSION['group']['id'];
        try {
            $result = $this->client->request('GET', 'group/'.$id.'/member', [
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

        if ($data['error'] == false) {
            return $this->view->render($response, 'pic/group-member.twig', [
                'members'	=> $data['data'],
                'pagination'=> $data['pagination'],
            ]);
        } else {
            $this->flash->addMessage('warning', $data['message']);
            return $response->withRedirect($this->router->pathFor('group.user'));

        }
    }

    public function getUnreportedItem($request, $response)
    {
        $id = $_SESSION['group']['id'];
        try {
            $result = $this->client->request('GET', 'item/group/'. $id, [
                'query' => [
                    'page'    => $request->getQueryparam('page'),
                    'perpage' => 10,
                    ]
                ]);

                try {
                    $result2 = $this->client->request('GET', 'group/'.$id.'/member', [
                        'query' => [
                            'perpage' => 9,
                            'page'    => $request->getQueryParam('page')
                        ]
                    ]);
                } catch (GuzzleException $e) {
                    $result2 = $e->getResponse();
                }

                $data2 = json_decode($result2->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);

        // var_dump($data2);
        return $this->view->render($response, 'pic/tugas.twig', [
            'items'	=> $data['data'],
            // 'group'	=> $args['id'],
            'member'	=> $data2['data'],
            'pagination'	=> $data['pagination'],
        ]);
    }

    public function getReportedItem($request, $response)
    {
        $id = $_SESSION['group']['id'];
        try {
            $result = $this->client->request('GET', 'item/group/'. $id.'/all-reported', [
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
            // 'group'	=> $args['id'],
            'pagination'	=> $data['pagination'],
        ]);
    }

    public function deleteItem($request, $response, $args)
	{
		try {
			$client = $this->client->request('GET', 'item/delete/'.$args['id']);
			$content = json_decode($client->getBody()->getContents(), true);
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents(), true );
		}
        if ($content['error'] == false) {
            $this->flash->addMessage('success', 'Tugas telah berhasil dihapus');
        } else {
            $this->flash->addMessage('warning', 'Anda tidak diizinkan menghapus tugas ini ');
        }

        return $response->withRedirect($this->router->pathFor('pic.item.group'));
	}

    public function createItem($request, $response)
    {
        if (empty($request->getParam('user_id'))) {
            $userId = null;
        } else {
            $userId = $request->getParam('user_id');
        }

        if (!empty($_FILES['image']['name'])) {
            $path = $_FILES['image']['tmp_name'];
            $mime = $_FILES['image']['type'];
            $name = $_FILES['image']['name'];
            $imgData = [
                'name'     => 'image',
                'filename' => $name,
                'Mime-Type'=> $mime,
                'contents' => fopen( $path, 'r' )
            ];
        } else {
            $imgData = [
                'name'     => 'image',
                'contents' => null
            ];
        }
        try {
            $result = $this->client->request('POST', 'item/create', [
                'multipart' => [
                    $imgData,
                    [
                        'name'     => 'user_id',
                        'contents' => json_encode($userId)
                    ],
                    [
                        'name'     => 'name',
                        'contents' => $request->getParam('name')
                    ],
                    [
                        'name'     => 'description',
                        'contents' => $request->getParam('description')
                    ],
                    [
                        'name'     => 'recurrent',
                        'contents' => $request->getParam('recurrent')
                    ],
                    [
                        'name'     => 'start_date',
                        'contents' => $request->getParam('start_date')
                    ],
                    [
                        'name'     => 'group_id',
                        'contents' => $_SESSION['group']['id']
                    ],
                    [
                        'name'     => 'creator',
                        'contents' => $_SESSION['login']['id']
                    ],
                    [
                        'name'     => 'privacy',
                        'contents' => $request->getParam('privacy')
                    ],

                ]
            ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $content = json_decode($result->getBody()->getContents(), true);
        if ($content['code'] == 201) {
            $this->flash->addMessage('success', $content['message']);
        } else {
            $_SESSION['errors'] = $content['message'];
            $_SESSION['old']    = $request->getParams();
        }
        if (!empty($request->getParam('member'))) {
            return $response->withRedirect($this->router->pathFor('unreported.item.user.group'));
        } else {
            return $response->withRedirect($this->router->pathFor('pic.item.group',['id' => $group ]));
        }

    }

    public function showItem($request, $response, $args)
    {
        $_SESSION['item_id'] = $args['id'];
        return $response->withRedirect($this->router->pathFor('pic.get.item'));
    }

    public function getItem($request, $response)
    {
        $id  = $_SESSION['item_id'];
        $group  = $_SESSION['group'];

        try {
            $result = $this->client->request('GET', 'item/show/'.$id.'?'
            . $request->getUri()->getQuery());

            try {
                $result2 = $this->client->request('GET', 'group/'.$group['id'].'/member', [
                    'query' => [
                        'perpage' => 9,
                        'page'    => $request->getQueryParam('page')
                    ]
                ]);
            } catch (GuzzleException $e) {
                $result2 = $e->getResponse();
            }

            $member = json_decode($result2->getBody()->getContents(), true);

        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);

        try {
            $comment = $this->client->request('GET', 'item/comment/'.$id.'?'
            . $request->getUri()->getQuery());
        } catch (GuzzleException $e) {
            $comment = $e->getResponse();
        }

        $allComment = json_decode($comment->getBody()->getContents(), true);

        $userId = $data['data']['user_id'];

        if ($data['data']) {
            return $this->view->render($response, 'pic/show-item-tugas.twig', [
                'items'     => $data['data'],
                'comment'   => $allComment['data'],
                'member'    => $member['data'],
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
        
        $_SESSION['search'] = $request->getQueryParam('search');
        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $result = $user->search($_SESSION['search'], $_SESSION['login']['id'])->setPaginate($page, 8);

        $data['users']      = $result['data'];
        $data['count']      = count($data['users']);
        $data['pagination'] = $result['pagination'];
        $data['search']     = $_SESSION['search'];
        $data['group_id']   = $request->getQueryParam('group_id');

        if (!empty($_SESSION['search'])) {
            return $this->view->render($response, 'pic/search-user.twig', $data);
        }
    }

    public function deleteGroupRequest($request, $response, $args)
	{
        $item = new \App\Models\Item($this->db);
        $findItem = $item->find('id', $args['id']);
		try {
			$client = $this->client->request('GET', 'request/'.$args['id']);

			$content = json_decode($client->getBody()->getContents(), true);
            $this->flash->addMessage('success', 'Tugas telah berhasil dihapus');
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents(), true );
			$this->flash->addMessage('warning', 'Anda tidak diizinkan menghapus tugas ini ');
		}
        return $response->withRedirect($this->router->pathFor('pic.item.group',['id' => $findItem['group_id']]));
	}

    public function getSearch($request, $response, $args)
    {
        return  $this->view->render($response, 'pic/search-user.twig', [
            'group_id' => $args['group']
        ]);
    }

    public function deleteComment($request, $response, $args)
    {
        try {
            $result = $this->client->request('GET', 'comment/delete/'. $args['id']);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);

        if ($data['error'] == false) {
            $this->flash->addMessage('success', $data['message']);
            return $response->withRedirect($this->router->pathFor('web.pic.show.item',['id' => $data['data']['item_id']]));
        } else {
            $this->flash->addMessage('warning', $data['message']);
            return $response->withRedirect($this->router->pathFor('web.pic.show.item',['id' => $data['data']['item_id']]));
        }
    }

    public function getUserReport($request, $response, $args)
    {

        try {
            $user = $this->client->request('GET', 'user/detail/'. $args['id']);
        } catch (GuzzleException $e) {
            $user = $e->getResponse();
        }

        $dataUser = json_decode($user->getBody()->getContents(), true);
        $_SESSION['user'] = $dataUser['data'];

        return $response->withRedirect($this->router->pathFor('unreported.item.user.group'));
    }

}
