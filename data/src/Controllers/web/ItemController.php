<?php
namespace App\Controllers\web;

use GuzzleHttp\Exception\BadResponseException as GuzzleException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
*
*/
class ItemController extends BaseController
{
    public function getItem($request, $response, $args)
    {
        $_SESSION['item_id'] = $args['id'];
        return $response->withRedirect($this->router->pathFor('get.item'));
    }

    public function showItem($request, $response, $args)
    {
        $id  = $_SESSION['item_id'];
        try {
            $result = $this->client->request('GET', 'item/show/'.$id.'?'
            . $request->getUri()->getQuery());
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

        // var_dump($data['error']);die();

        if ($data['error'] == false) {
            return $this->view->render($response, 'users/show-item.twig', [
                'items' => $data['data'],
                'comment' => $allComment['data'],
            ]);
        } else {
            $this->flash->addMessage('error', $data['message']);
            return $response->withRedirect($this->router->pathFor('home'));
        }
    }

    //Get group item unreported
    public function getGroupItem($request, $response, $args)
	{
        try {
            $result = $this->client->request('GET', 'item/group/'.$args['group'],[
                'query' => [
                    'perpage' => 10,
                    'page' => $request->getQueryParam('page')
                    ]]);

                    try {
                        $findGroup = $this->client->request('GET', 'group/find/'.$args['group']);
                    } catch (GuzzleException $e) {
                        $findGroup = $e->getResponse();
                    }
                    $dataGroup = json_decode($findGroup->getBody()->getContents(), true);

                } catch (GuzzleException $e) {
                    $result = $e->getResponse();
                }

        $data = json_decode($result->getBody()->getContents(), true);
        //var_dump($data);die();
        if (!isset($data['pagination'])) {
        $data['pagination'] = null;
        }
		return $this->view->render($response, 'users/group/unreported-item.twig', [
			'data'			=>	$data['data'],
			'count_item'	=>	count($data['data']),
			// 'pagination'  	=>	$data['pagination'],
			'group' 		=> 	$dataGroup['data']
		]);
	}

	//create item by user
	public function createItemUser($request, $response, $args)
	{
        // $userId = $_SESSION['login']['id'];
// var_dump($request->getParams());die;
		$query = $request->getQueryParams();
		try {
			$result = $this->client->request('POST', 'item/'.$args['group'], [
				'form_params' => [
					'name'          => $request->getParam('name'),
	                'description'   => $request->getParam('description'),
	                'recurrent'     => $request->getParam('recurrent'),
	                'start_date'    => $request->getParam('start_date'),
	                'user_id'    	=> $_SESSION['login']['id'],
	                'group_id'      => $args['group'],
	                'creator'    	=> $_SESSION['login']['id'],
	                'privacy'       => $request->getParam('privacy'),
				]
			]);

            // $this->flash->addMessage('success', 'Berhasil membuat item');
		} catch (GuzzleException $e) {
			$result = $e->getResponse();
		}

        $content = json_decode($result->getBody()->getContents(), true);
// var_dump($content);die;
        // return $response->withRedirect($this->router->pathFor('unreported.item.user.group', [
        //     'user' 		=> 	$_SESSION['login']['id'],
        //     'group'     =>  $args['group']
        // ]));

        $data = json_decode($content, true);

        if ($data['error'] == false) {
            $this->flash->addMessage('success', $data['message']);
            return $response->withRedirect($this->router->pathFor('unreported.item.user.group', [
                'user' 		=> $_SESSION['login']['id'],
                'group' 	=> $args['group'],
            ]));
        } else {
            // return $response->withRedirect($this->router->pathFor('home'));
            return $response->withRedirect($this->router->pathFor('unreported.item.user.group', [
                'user' 		=> $_SESSION['login']['id'],
                'group' 	=> $args['group'],
            ]));
        }
    }

	//Get group item reported
    public function getReportedGroupItem($request, $response, $args)
    {
    	try {
    		$result = $this->client->request('GET', 'item/group/'.$args['group'].'/reported',[
                'query' => [
                    'perpage' => 10,
                    'page' => $request->getQueryParam('page')
                    ]]);

                try {
                    $findGroup = $this->client->request('GET', 'group/find/'.$args['group']);
                } catch (GuzzleException $e) {
                    $findGroup = $e->getResponse();
                }
                $dataGroup = json_decode($findGroup->getBody()->getContents(), true);
    	} catch (GuzzleException $e) {
    		$result = $e->getResponse();
    	}

		$data = json_decode($result->getBody()->getContents(), true);

		return $this->view->render($response, 'users/group/reported-item.twig', [
			'data'			=>	$data['data'],
			'pagination'	=>	$data['pagination'],
			'group' 		=> 	$dataGroup['data'],
		]);
    }

    //create item by user
	public function reportItem($request, $response, $args)
	{
        // var_dump( $_FILES['image']['name']);die();
        if (!empty($_FILES['image']['name'])) {
            $path = $_FILES['image']['tmp_name'];
            $mime = $_FILES['image']['type'];
            $name = $_FILES['image']['name'];
            try {
                $result = $this->client->request('POST', 'item/report/'.$args['item'], [
                    'multipart' => [
                        [
                            'name'     => 'image',
                            'filename' => $name,
                            'Mime-Type'=> $mime,
                            'contents' => fopen( $path, 'r' )
                        ],
                        [
                            'name'     => 'description',
                            'contents' => $request->getParam('description')
                        ],
                        [
                            'name'     => 'user',
                            'contents' => $_SESSION['login']['id']
                        ],
                        [
                            'name'     => 'item',
                            'contents' => $request->getParam('item_id')
                        ]
                    ]
                ]);
            } catch (GuzzleException $e) {
                $result = $e->getResponse();
            }
        } else {
            try {
                $result = $this->client->request('POST', 'item/report/'.$args['item'], [
                    'form_params' => [
                        'description'   => $request->getParam('description'),
                        'user'          => $_SESSION['login']['id'],
                        'item'          => $request->getParam('item_id')
                    ]
                ]);
            } catch (GuzzleException $e) {
                $result = $e->getResponse();
            }
        }

        $content = json_decode($result->getBody()->getContents(), true);
// var_dump($content);die();
    	// return $response->withRedirect("http://localhost/Reporting-App/public/items/group/".$args['group'].'/reported');

        if ($content['error'] == false) {
            $this->flash->addMessage('success', $content['message']);
            return $response->withRedirect($this->router->pathFor('unreported.item.user.group', [
                'group' => 	$request->getParam('group_id'),
                'user'  => 	$_SESSION['login']['id']
            ]));
        } else {
            $this->flash->addMessage('error', $content['message']);

            return $response->withRedirect($this->router->pathFor('unreported.item.user.group', [
                'group' => 	$request->getParam('group_id'),
                'user'  => 	$_SESSION['login']['id']
            ]));
        }
    }

    //     $content = json_decode($result->getBody()->getContents(), true);
    //     // var_dump($content);die();
    // 	return $response->withRedirect($this->router->pathFor('group.user'), [
    //         'group' 		=> 	$args['group']
    //     ]);
	// }
	//Delete item by user
    public function deleteItemByUser($request, $response, $args)
    {
    	try {
    		// $item = $this->client->request('GET', '/items/group/'.$args['group']);
    		$data = $this->client->request('GET', 'item/'.$args['item'].
    			$request->getUri()->getQuery());
            $this->flash->addMessage('success', 'Berhasil menghapus tugas');
    	} catch (GuzzleException $e) {
    		$data = $e->getResponse();
            $this->flash->addMessage('error', 'Ada kesalahan saat menghapus tugas');
    	}

		$dataDetailItem = json_decode($data->getBody()->getContents(), true);

    	try {
    		$result = $this->client->request('GET', 'item/'.$args['item'].'/user'.
    			$request->getUri()->getQuery());
            $this->flash->addMessage('success', 'Berhasil menghapus tugas');
    	} catch (GuzzleException $e) {
    		$result = $e->getResponse();
            $this->flash->addMessage('error', 'Ada kesalahan saat menghapus tugas');
    	}

		$data = json_decode($result->getBody()->getContents(), true);

			return $response->withRedirect($this->router->pathFor('group.item', [
			// 'data'			=>	$data['data'],
			'group' 		=> 	$dataDetailItem['data']['group_id']
		]));
    }

    //Delete item reported
    public function deleteItemReported($request, $response, $args)
    {
    	$query = $request->getQueryParams();

    	try {
    		$data = $this->client->request('GET', 'items/'.$args['item'].
    			$request->getUri()->getQuery());
    	} catch (GuzzleException $e) {
    		$data = $e->getResponse();
    	}

		$dataDetailItem = json_decode($data->getBody()->getContents(), true);

    	try {
    		$result = $this->client->request('GET', 'items/'.$args['item'].'/delete'.
    			$request->getUri()->getQuery());
            $this->flash->addMessage('succes', 'Berhasil menghapus tugas yang telah dilaporkan');
    	} catch (GuzzleException $e) {
    		$result = $e->getResponse();
            $this->flash->addMessage('error', 'Ada kesalahan saat menghapus tugas');
    	}

		$data = json_decode($result->getBody()->getContents(), true);

		return $response->withRedirect($this->router->pathFor('web.reported.group.item', [
			// 'data'			=>	$data['data'],
			'group' 		=> 	$dataDetailItem['data']['group_id']
		]));
    }

    public function searchItemArchive($request, $response, $args)
    {
        // var_dump($request->getParams());die();
        if ($request->getParam('month') == 'all') {
            try {
                $result = $this->client->request('GET', 'item/'.$args['id'].'/year',[
                    'query' => [
                        'perpage' => 999,
                        'year' => $request->getParam('year')
                        ]]);
                    } catch (GuzzleException $e) {
                        $result = $e->getResponse();
                    }
        } else {
            try {
                $result = $this->client->request('GET', 'item/'.$args['id'].'/month',[
                    'query' => [
                        'perpage' => 999,
                        'month' => $request->getParam('month'),
                        'year' => $request->getParam('year')
                        ]]);
                    } catch (GuzzleException $e) {
                        $result = $e->getResponse();
                    }
        }

        try {
            $user = $this->client->request('GET', 'user/detail/'. $args['id']);
        } catch (GuzzleException $e) {
            $user = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);
        $dataUser = json_decode($user->getBody()->getContents(), true);

        return $this->view->render($response, 'users/guard/report-archives.twig', [
        // var_dump($data);die();
            'data'		=>	$data['data'],
            'user_id'	=>	$args['id'],
            'user'      => $dataUser['data'],
            // 'pagination'=>	$data['pagination'],
            'query' 	=> 	[
                'year'  => $request->getParam('year'),
                'month'  => $request->getParam('month')
            ]
        ]);
    }

    public function getItemArchive($request, $response, $args)
    {
        return  $this->view->render($response, 'users/guard/report-archives.twig', [
            'user_id'		=>	$args['id']
        ]);
    }

    //Get group item reported
    public function getReportedUserGroupItem($request, $response)
    {
        // $userId = $_SESSION['login']['id'];
        // $groupId = $args['id'];
        try {
            $result = $this->client->request('GET', 'item/group/user/reported',[
                'query' => [
                    'user_id' =>  $_SESSION['login']['id'],
                    'group_id' =>  $_SESSION['group']['id'],
                    'perpage' => 10,
                    'page' => $request->getQueryParam('page')
                    ]]);

        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
         $data= json_decode($result->getBody()->getContents(), true);
        //  var_dump($dataGroup['data']);die();

        return $this->view->render($response, 'users/group/reported-item.twig', [
            'data'			=>	$data['data'],
            'pagination'	=>	$data['pagination'],
            // 'group' 		=> 	$dataGroup['data'],
        ]);
    }

    //Get group item reported
    public function getUnreportedUserGroupItem($request, $response)
    {
        try {
            $result = $this->client->request('GET', 'item/group/user/unreported',[
                'query' => [
                    'user_id' =>  $_SESSION['login']['id'],
                    'group_id' => $_SESSION['group']['id'],
                    'perpage' => 10,
                    'page' => $request->getQueryParam('page')
                    ]]);

        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $data= json_decode($result->getBody()->getContents(), true);
// var_dump($data);die;
        return $this->view->render($response, 'users/group/unreported-item.twig', [
            'data'			=>	$data['data'],
            'pagination'	=>	$data['pagination'],
            // 'group' 		=> 	$dataGroup['data'],
        ]);
    }

    public function getUserItem($request, $response, $args)
    {
        try {
            $user = $this->client->request('GET', 'user/detail/'. $args['id']);
        } catch (GuzzleException $e) {
            $user = $e->getResponse();
        }

        $dataUser = json_decode($user->getBody()->getContents(), true);
        $_SESSION['user'] = $dataUser['data'];
        return $response->withRedirect($this->router->pathFor('reported.item.user'));
    }
    //Get group item reported
    public function getReportedUserItem($request, $response)
    {
        // var_dump($_SESSION['user']);die;
        try {
            $result = $this->client->request('GET', 'item/user/all-reported',[
                'query' => [
                    'user_id' => $_SESSION['user']['id'],
                    'perpage' => 10,
                    'page' => $request->getQueryParam('page')
                    ]]);

        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $data= json_decode($result->getBody()->getContents(), true);
// var_dump($data);
        return $this->view->render($response, 'users/item/reported.twig', [
            'data'			=>	$data['data'],
            'pagination'	=>	$data['pagination'],
        ]);
    }

    //Get group item reported
    public function getUnreportedUserItem($request, $response)
    {
        try {
            $result = $this->client->request('GET', 'item/user/all-unreported',[
                'query' => [
                    'user_id' => $_SESSION['user']['id'],
                    'perpage' => 10,
                    'page' => $request->getQueryParam('page')
                    ]]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $data = json_decode($result->getBody()->getContents(), true);
// var_dump($data);die;
        return $this->view->render($response, 'users/item/unreported.twig', [
            'data'			=>	$data['data'],
            'pagination'	=>	$data['pagination'],
            // 'group' 		=> 	$dataGroup['data'],
        ]);
    }

    public function deleteImage($request, $response, $args)
	{
        // var_dump($_SESSION['back']);die;
        $id  = $_SESSION['item_id'];

		try {
			$client = $this->client->request('GET', 'item/image/delete/'.$args['image']);
			$content = json_decode($client->getBody()->getContents(), true);
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents(), true );
		}
        if ($content['code'] == 200) {
            $this->flash->addMessage('success', 'Foto item berhasil dihapus');
        } else {
            $this->flash->addMessage('warning', 'Anda tidak diizinkan menghapus foto ini');
        }

        return $response->withRedirect($this->router->pathFor('pic.get.item'));
	}

    public function update($request, $response)
	{
        $id = $_SESSION['item_id'];
		$group = $_SESSION['group'];

        if (empty($request->getParam('user_id'))) {
            $user_id = null;
        } else {
            $user_id = $request->getParam('user_id');
        }

        try {
            $result = $this->client->request('POST', 'item/update/',
            ['form_params' =>  [
                'id'            => $id,
                'name'          => $request->getParam('name'),
                'description'   => $request->getParam('description'),
                'recurrent'     => $request->getParam('recurrent'),
                'start_date'    => $request->getParam('start_date'),
                'user_id'    	=> $user_id,
                'group_id'      => $group['id'],
                'privacy'       => $request->getParam('privacy'),
			]
		]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $data = json_decode($result->getBody()->getContents(), true);

        if (!empty($_FILES['image']['name'])) {
            $path = $_FILES['image']['tmp_name'];
            $mime = $_FILES['image']['type'];
            $name  = $_FILES['image']['name'];
            $id = $_SESSION['item_id'];

            try {
                $result2 = $this->client->request('POST', 'item/add/image', [
                    'multipart' => [
                        [
                            'name'     => 'image',
                            'filename' => $name,
                            'Mime-Type'=> $mime,
                            'contents' => fopen( $path, 'r' )
                        ],
                        [
                            'name'     => 'item_id',
                            'contents' => $id
                        ]
                    ]
                ]);
            } catch (GuzzleException $e) {
                $result2 = $e->getResponse();
            }

        }

        // var_dump($data);die();
        if ($data['code'] == 201) {
            $this->flash->addMessage('success',  $data['message']);
        } else {
            $_SESSION['old'] = $request->getParams();
            $this->flash->addMessage('error', $data['message']);
        }
		return $response->withRedirect($this->router->pathFor('pic.get.item'));
	}

    public function postImage($request, $response)
    {
        // var_dump($_FILES);die();
        $path = $_FILES['image']['tmp_name'];
        $mime = $_FILES['image']['type'];
        $name  = $_FILES['image']['name'];
        $id = $_SESSION['item_id'];

        try {
            $result = $this->client->request('POST', 'item/add/image', [
                'multipart' => [
                    [
                        'name'     => 'image',
                        'filename' => $name,
                        'Mime-Type'=> $mime,
                        'contents' => fopen( $path, 'r' )
                    ],
                    [
                        'name'     => 'item_id',
                        'contents' => $id
                    ]
                ]
            ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);

        // var_dump($newUser);die();
        if ($data['code'] == 200) {
            $this->flash->addMessage('success', $data['message']);
            return $response->withRedirect($this->router->pathFor('get.item'));
            $_SESSION['login'] = $newUser['data'];
        } else {
            $this->flash->addMessage('warning', $data['message']);
            return $response->withRedirect($this->router->pathFor('get.item'));
        }
    }


}

 ?>
