<?php

namespace App\Controllers\web;

use GuzzleHttp\Exception\BadResponseException as GuzzleException;


class AdminController extends BaseController
{
    public function index($request, $response)
    {
        try {
            $count = $this->client->request('GET', 'admin/count/all');
        } catch (GuzzleException $e) {
            $count = $e->getResponse();
        }
        $data = json_decode($count->getBody()->getContents(), true);

        return $this->view->render($response, 'admin/admin.twig',[
            'count' => $data['data']['count']
        ]);
    }
    public function getLogin($request, $response)
    {
        return $this->view->render($response, 'admin/login.twig');
    }

    public function login($request, $response)
    {
        try {
            $result = $this->client->request('POST', 'login',
                ['form_params' => [
                    'username' => $request->getParam('username'),
                    'password' => $request->getParam('password')
                ]
            ]);

        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $data = json_decode($result->getBody()->getContents(), true);

        if ($data['code'] == 200) {
            $_SESSION['key'] = $data['key'];
            $_SESSION['login'] = $data['data'];

            if ( $data['data']['status'] == 1) {
                $this->flash->addMessage('success', 'Selamat datang, '. $login['username']);
                return $response->withRedirect($this->router->pathFor('admin.dashboard'));
            } else {
                $this->flash->addMessage('warning', 'Anda tidak terdaftar sebagai admin');
                return $response->withRedirect($this->router->pathFor('login.admin'));
            }
        } else {
            $this->flash->addMessage('warning', 'Username atau password tidak cocok');
            return $response->withRedirect($this->router->pathFor('login.admin'));
        }
    }

    public function userList($request, $response)
    {
        try {
            $result = $this->client->request('GET', $this->router->pathFor('api.user.list'), [
                    'query' => [
                    'perpage' => 10,
                    'page' => $request->getQueryParam('page')
                    ]
                ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $data = json_decode($result->getBody()->getContents(), true);

        return $this->view->render($response, 'admin/data/user-list.twig', [
            'data'          =>  $data['data'],
            'pagination'     =>  $data['pagination'],
        ]);
    }

    public function groupList($request, $response)
    {
        try {
            $result = $this->client->request('GET', $this->router->pathFor('api.group.list'), [
                    'query' => [
                    'perpage' => 10,
                    'page' => $request->getQueryParam('page')
                    ]
                ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $data = json_decode($result->getBody()->getContents(), true);
// var_dump($data);die;
        return $this->view->render($response, 'admin/data/group/group-list.twig', [
            'data'          =>  $data['data'],
            'pagination'    =>  $data['pagination'],
        ]);
    }

    public function guardList($request, $response)
    {
        try {
            $result = $this->client->request('GET', $this->router->pathFor('api.guard'), [
                    'query' => [
                        'perpage'   => 10,
                        'page'      => $request->getQueryParam('page')
                    ]
                ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $data = json_decode($result->getBody()->getContents(), true);

        return $this->view->render($response, 'admin/data/guard-list.twig', [
            'data'          =>  $data['data'],
            'pagination'     =>  $data['pagination'],
        ]);
    }

    public function childList($request, $response)
    {
        try {
            $result = $this->client->request('GET', $this->router->pathFor('api.child'), [
                    'query' => [
                        'perpage'   => 10,
                        'page'      => $request->getQueryParam('page')
                    ]
                ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $data = json_decode($result->getBody()->getContents(), true);

        return $this->view->render($response, 'admin/data/child-list.twig', [
            'data'          =>  $data['data'],
            'pagination'     =>  $data['pagination'],
        ]);
    }

    public function getAllItem($request, $response)
    {
        try {
            $result = $this->client->request('GET', $this->router->pathFor('api.item.all'), [
                    'query' => [
                    'perpage' => 10,
                    'page' => $request->getQueryParam('page')
                    ]
                ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $data = json_decode($result->getBody()->getContents(), true);

        return $this->view->render($response, 'admin/data/item-list.twig', [
            'data'          =>  $data['data'],
            'pagination'     =>  $data['pagination'],
        ]);
    }

    public function getMemberGroup($request, $response, $args)
    {
        try {
			$findGroup = $this->client->request('GET', 'group/find/'. $args['id']);
            try {
                $client = $this->client->request('GET','group/member/all',[
                    'query' => [
                        'perpage'   => 10,
                        'page'      => $request->getQueryParam('page'),
                        'user_id'   => $_SESSION['login']['id'],
                        'group_id'  => $args['id']
                        ]]);
                        $data = json_decode($client->getBody()->getContents(), true);
                    } catch (GuzzleException $e) {
                        $data = json_decode($e->getResponse()->getBody()->getContents(), true);
                    }

		} catch (GuzzleException $e) {
			$findGroup = $e->getResponse();
		}
		$dataGroup = json_decode($findGroup->getBody()->getContents(), true);

        $_SESSION['group'] = $dataGroup['data'];
        // var_dump($_SESSION['group']);die;
        return $this->view->render($response, 'admin/data/group/group-member.twig', [
            'data'          =>  $data['data'],
            // 'group_id'      =>  $args['id'],
            'pagination'    =>  $data['pagination']
        ]);
    }

    public function getAddMember($request, $response)
    {
        try {
            $result = $this->client->request('GET', $this->router->pathFor('api.user.list'), [
                    'query' => [
                    'perpage' => 10,
                    'page' => $request->getQueryParam('page')
                    ]
                ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $data = json_decode($result->getBody()->getContents(), true);

         return $this->view->render($response, 'admin/data/group/add-member.twig', [
            'data'          =>  $data['data'],
            'pagination'     =>  $data['pagination'],
        ]);
    }

    //Post create group
    public function createGroup($request, $response)
    {
        // var_dump( $request->getParams());die;
        try {
            $result = $this->client->request('POST', 'group/create',
                ['form_params' => [
                    'name' 			=> $request->getParam('name'),
                    'description'	=> $request->getParam('description'),
                    'creator'       => $_SESSION['login']['id'],
                    // 'image'			=> $request->getParam('description')
                ]
            ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $content = json_decode( $result->getBody()->getContents(), true);
        if ($content['code'] ==201) {
            $this->flash->addMessage('success', 'Berhasil menambah group');
        } else {
            $this->flash->addMessage('error', $content['message']);
        }

        return $response->withRedirect($this->router->pathFor('admin.group.list'));
    }

    //Delete group
	public function deleteGroup($request, $response, $args)
	{
		try {
			$client = $this->client->request('GET',
			$this->router->pathFor('api.group.delete', ['id' => $args['id']]));
		} catch (GuzzleException $e) {
			$client = $e->getResponse();
		}

		$content = json_decode($client->getBody()->getContents(), true);

		if ($content['code'] == 200) {
            unset($_SESSION['group']);
			$this->flash->addMessage('success', $content['message']);
		} else {
			$this->flash->addMessage('error', $content['message']);
		}
        return $response->withRedirect($this->router->pathFor('admin.group.list'));
	}

    //Edit group
	public function updateGroup($request, $response)
	{
        // var_dump($_FILES);die;
        $id = $request->getParam('group_id');

        try {
            $result = $this->client->request('POST', 'group/update',
            ['form_params' =>  [
                'id'     		=>$id,
                'name'      	=> $request->getParam('name'),
                'description'  	=> $request->getParam('description')
            ]
        ]);
        if ($_FILES['image']['name']) {
            $path = $_FILES['image']['tmp_name'];
            $mime = $_FILES['image']['type'];
            $name  = $_FILES['image']['name'];

            try {
                $result2 = $this->client->request('POST', 'group/change/photo/'.$id, [
                    'multipart' => [
                        [
                            'name'     => 'image',
                            'filename' => $name,
                            'Mime-Type'=> $mime,
                            'contents' => fopen( $path, 'r' )
                        ]
                    ]
                ]);
            } catch (GuzzleException $e) {
                $result2 = $e->getResponse();
            }
        }
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);

        if ($data['code'] == 201) {
            $this->flash->addMessage('success',  $data['message']);
        } else {
            $_SESSION['old'] = $request->getParams();
            $this->flash->addMessage('error', $data['message'][0]);
        }
        return $response->withRedirect($this->router->pathFor('admin.group.list'));

	}

    public function deleteMember($request, $response, $args)
    {
    	$user = $args['user'];
    	$group = $_SESSION['group']['id'];

    	try {
    		$client = $this->client->request('GET', 'group/delete/member/'.$user.'/'.$group);
    		$content = json_decode($client, true);
    	} catch (GuzzleException $e) {
    		$client = $e->getResponse();
    	}

        $content = json_decode($client->getBody()->getContents(), true);

    	if ($content['code'] == 200) {
    		$this->flash->addMessage('success', $content['message']);
    	} else {
    		$this->flash->addMessage('warning', $content['message']);
    	}
        return $response->withRedirect($this->router->pathFor('admin.group.member', ['id'=> $group]));
    }

    public function detailUser($request, $response, $args)
    {
        try {
            $result = $this->client->request('GET', 'user/detail/'.$args['id']);
            try {
                $guard = $this->client->request('GET', 'guard/show/'.$args['id']);
            } catch (GuzzleException $e) {
                $guard = $e->getResponse();
            }
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);
        $dataGuard = json_decode($guard->getBody()->getContents(), true);
        $_SESSION['user'] = $data['data'];
// var_dump($data['data']);die;
        return $this->view->render($response, 'admin/data/user-info.twig', [
            'guard' => $dataGuard['data']
            ]);
    }


    public function deleteUser($request, $response, $args)
    {
        // var_dump($_SESSION['key']);die;
        try {
            $result = $this->client->request('GET', 'user/delete/'.$args['id']);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $data = json_decode($result->getBody()->getContents(), true);

        if ($data['code'] == 200) {
            $this->flash->addMessage('success', 'Akun berhasil dihapus');
        } else {
            $this->flash->addMessage('warning', $data['message']);
        }

        return $response->withRedirect($this->router->pathFor('admin.user.list'));
    }

    public function deleteItem($request, $response, $args)
	{
		try {
			$result = $this->client->request('GET', 'item/delete/'.$args['id']);
		} catch (GuzzleException $e) {
			$result = $e->getResponse();
		}
        $data = json_decode($result->getBody()->getContents(), true);

        if ($data['code'] == 200) {
            $this->flash->addMessage('success', 'Item berhasil dihapus');
        } else {
            $this->flash->addMessage('warning', 'Anda tidak diizinkan menghapus item ini ');
        }

        return $response->withRedirect($this->router->pathFor('admin.item.list'));
	}

}
