<?php
namespace App\Controllers\web;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Exception\BadResponseException as GuzzleException;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use App\Models\RequestModel;
/**
 *
 */
class RequestController extends BaseController
{
    public function createUserToGroup($request, $response, $args)
	{
		$query = $request->getQueryParams();
        try {
            $result = $this->client->request('POST', 'request/group/'.$args['group'],
                ['query' => [
                    'group_id'  => $args['group'],
                    'user_id'   => $_SESSION['login']['id']
                ]
            ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data);die;
        if ($data['error'] == false) {
            $this->flash->addMessage('success', $data['message']);
        }
        else {
            $this->flash->addMessage('error',  $data['message']);
        }
        return $response->withRedirect($this->router->pathFor('group.user'));
	}

    public function createUserToGuard($request, $response, $args)
    {
        $query = $request->getQueryParams();
        try {
            $result = $this->client->request('POST', 'request/guard/'.$args['guard'],
                ['query' => [
                    'user_id'   => $_SESSION['login']['id'],
                    'guard_id'  => $args['guard']
                ]
            ]);
            $this->flash->addMessage('success', 'Berhasil mengirim permintaan');
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
            $this->flash->addMessage('error', 'Ada kesalahan saat mengirim permintaan');
        }
        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data);die();
        return $this->view->render($response, 'users/group-list.twig', [
            'data'			=> $data['data'],
            'pagination'	=> $data['pagination']
        ]);
    }
    public function createGuardToUser($request, $response, $args)
    {
        $query = $request->getQueryParams();
        try {
            $result = $this->client->request('POST', 'request/user/'.$args['user'],
                ['query' => [
                    'guard_id'  => $_SESSION['login']['id'],
                    'user_id'   => $args['user']
                ]
            ]);
            $this->flash->addMessage('success', 'Berhasil mengirim permintaan');
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
            $this->flash->addMessage('error', 'Ada kesalahan saat mengirim permintaan');
        }
        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data);die();
        return $this->view->render($response, 'users/group-list.twig', [
            'data'			=> $data['data'],
            'pagination'	=> $data['pagination']
        ]);
    }

    public function guardRequest($request, $response, $args)
    {
        try {
            $result = $this->client->request('GET', 'request/guard',
                ['query' => [
                    'user_id'  => $_SESSION['login']['id'],
                    'page'     => $request->getQueryParam('page'),
                    'perpage'  => 10
                ]
            ]);
            $this->flash->addMessage('success', 'Berhasil mengirim permintaan');
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
            $this->flash->addMessage('error', 'Ada kesalahan saat mengirim permintaan');
        }
        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data);die();
        return $this->view->render($response, 'users/group-list.twig', [
            'data'			=> $data['data'],
            'pagination'	=> $data['pagination']
        ]);
    }

    public function userRequest($request, $response, $args)
    {
        try {
            $result = $this->client->request('GET', 'request/notif/user',
                ['query' => [
                    'user_id'  => $_SESSION['login']['id'],
                    'page'     => $request->getQueryParam('page'),
                    'perpage'  => 10
                ]
            ]);
            $this->flash->addMessage('success', 'Berhasil mengirim permintaan');
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
            $this->flash->addMessage('error', 'Ada kesalahan saat mengirim permintaan');
        }
        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data);die();
        return $this->view->render($response, 'users/group-list.twig', [
            'data'			=> $data['data'],
            'pagination'	=> $data['pagination']
        ]);
    }

    public function groupRequest($request, $response, $args)
    {
        try {
            $result = $this->client->request('GET', 'request/notif/group',
                ['query' => [
                    'group_id'  => $_SESSION['login']['id'],
                    'page'     => $request->getQueryParam('page'),
                    'perpage'  => 10
                ]
            ]);
            $this->flash->addMessage('success', 'Berhasil mengirim permintaan');
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
            $this->flash->addMessage('error', 'Ada kesalahan saat mengirim permintaan');
        }
        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data);die();
        return $this->view->render($response, 'users/group-list.twig', [
            'data'			=> $data['data'],
            'pagination'	=> $data['pagination']
        ]);
    }

    public function allGroupRequest($request, $response, $args)
    {
        try {
            $result = $this->client->request('GET', 'request/group/all',
                ['query' => [
                    'user_id'  => $_SESSION['login']['id'],
                    'page'     => $request->getQueryParam('page'),
                    'perpage'  => 10
                ]
            ]);
            $this->flash->addMessage('success', 'Berhasil mengirim permintaan');
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
            $this->flash->addMessage('error', 'Ada kesalahan saat mengirim permintaan');
        }
        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data);die();
        return $this->view->render($response, 'users/group-list.twig', [
            'data'			=> $data['data'],
            'pagination'	=> $data['pagination']
        ]);
    }

    public function allRequest($request, $response, $args)
    {
        try {
            $result = $this->client->request('GET', 'request/all',
                ['query' => [
                    'user_id'  => $_SESSION['login']['id'],
                    'page'     => $request->getQueryParam('page'),
                    'perpage'  => 10
                ]
            ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data['data']['user']);die();
        if ($data['message'] == "Data ditemukan") {
            return $this->view->render($response, 'users/notif.twig', $data);
        } else {
            $this->flash->addMessage('error', 'Tidak ada pemberitahuan]');
            return $this->view->render($response, 'users/notif.twig', $data);
            # code...
        }

    }

    public function requestToBeFellow($request, $response, $args)
    {
        $guard  = $args['guard'];
        $search = $_SESSION['search_param'];
        try {
            $result = $this->client->request('POST','request/guardian/'.$guard);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $data = json_decode($result->getBody()->getContents(), true);
            // var_dump($data);die();

        if ($data['code'] == 201) {
            $this->flash->addMessage('success', $data['message']);
            return $response->withRedirect('/Reporting-App/public/guard/search/user?search='.$search);
        }else {
            $this->flash->addMessage('warning', $data['message']);
            return $response->withRedirect('/Reporting-App/public/guard/search/user?search='.$search);
        }
    }

    public function requestToBeGuard($request, $response, $args)
    {
        $fellow= $args['user'];
        $search = $_SESSION['search_param'];
        $reques = new \App\Models\RequestModel($this->db);


        try {
            $result = $this->client->request('POST','request/fellow/'.$fellow);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $data = json_decode($result->getBody()->getContents(), true);
            // var_dump($data);die();

        if ($data['code'] == 201) {
            $this->flash->addMessage('success', $data['message']);
            return $response->withRedirect('/Reporting-App/public/guard/search/user?search='.$search);
        }else {
            $this->flash->addMessage('warning', $data['message']);
            return $response->withRedirect('/Reporting-App/public/guard/search/user?search='.$search);
        }
    }

    public function confirm($request, $response, $args)
    {
        $reques = new \App\Models\RequestModel($this->db);
        $findRequest = $reques->find('id', $args['id']);
        $userId      = $findRequest['user_id'];
        $guardId     = $findRequest['guard_id'];
        $category    = $findRequest['category'];
        // var_dump($key); die();

        try {
            $result = $this->client->request('POST', 'request/confirm/'.$args['id']);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data); die();

        if ($data['code'] == 200) {
            $this->flash->addMessage('success', $data['message']);
            try {
                $result1 = $this->client->request('GET', 'request/all',
                ['query' => [
                    'user_id'  => $_SESSION['login']['id'],
                    'page'     => $request->getQueryParam('page'),
                    'perpage'  => 10
                    ]
                ]);
            } catch (GuzzleException $e) {
                $result1 = $e->getResponse();
            }
            $notif = json_decode($result1->getBody()->getContents(), true);

            if ($notif['message'] == 'Data ditemukan') {
                $_SESSION['notif'] = $notif['data'];
            }
                return $response->withRedirect($this->router->pathFor('notification'));
        } else {
            $this->flash->addMessage('warning', $data['message']);
            return $response->withRedirect($this->router->pathFor('notification'));

        }

    }

    public function deleteRequest($request, $response, $args)
    {

        try {
            $result = $this->client->request('DELETE', 'request/'.$args['id']);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($resul->getBody()->getContents(), true);

        if ($data['code'] == 200) {
            $this->flash->addMessage('success', $data['message']);
            try {
                $result1 = $this->client->request('GET', 'request/all',
                ['query' => [
                    'user_id'  => $_SESSION['login']['id'],
                    'page'     => $request->getQueryParam('page'),
                    'perpage'  => 10
                    ]
                ]);
            } catch (GuzzleException $e) {
                $result1 = $e->getResponse();
            }
            $notif = json_decode($result1->getBody()->getContents(), true);

            if ($notif['message'] == 'Data ditemukan') {
                $_SESSION['notif'] = $notif['data'];
            }
                return $response->withRedirect($this->router->pathFor('notification'));
        } else {
            $this->flash->addMessage('warning', $data['message']);
            return $response->withRedirect($this->router->pathFor('notification'));

        }

    }
}
