<?php
namespace App\Controllers\web;


use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Exception\BadResponseException as GuzzleException;
use GuzzleHttp;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

class GuardController extends BaseController
{
    // Function show user by guard_id
    public function getUserByGuard(Request $request, Response $response)
    {

        $_SESSION['search'] = 1;
        try {
            $result = $this->client->request('GET',
            $this->router->pathFor('api.guard.show.user'), [
                 'query' => [
                     'perpage' => 10,
                     'page' => $request->getQueryParam('page'),
                     'id' => $_SESSION['login']['id']
 			]]);
             // $content = json_decode($result->getBody()->getContents());
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($_SESSION['search']);die();
        // var_dump($data);die();
        return $this->view->render($response, 'users/guard/all-user.twig', [
            'data'          =>  $data['data'] ,
            'pagination'    =>  $data['pagination']
        ]);    // return $this->view->render($response, 'guard/show-user.twig', $content->reporting);
    }

    // Function Delete Guardian
    public function deleteGuardian(Request $request, Response $response, $args)
    {
        try {
            $result = $this->client->request('GET', $this->router->pathFor('api.guard.delete', ['id' => $args['id']]));
            $content = json_decode($client->getBody()->getContents());
        } catch (GuzzleException $e) {
            $content = json_decode($e->getResponse()->getBody()->getContents());
        }
            var_dump($content);
    }

    // Function Create Guardian
    public function createGuardian(Request $request, Response $response, $args)
    {
        //  var_dump($request->getParam('user_id')); die();
        $guard = $request->getParam('guard_id');
        $user= $request->getParam('user_id');
        $search = $request->getParam('search');
        try {
            $result = $this->client->request('POST', 'guard/create/'. $guard.'/'.$user);
            $data = json_decode($result->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
            $data = json_decode($result->getBody()->getContents(), true);
        }
        // $search = $_SESSION['search'];
        var_dump($data);die();
        if ($data['code'] == 200 ) {
            $this->flash->addMessage('success', $data['message']);
            return $response->withRedirect('/Reporting-App/public/pic/search/user/guard?search='.$search);
        } else {
            $this->flash->addMessage('warning', $data['message']);
            return $response->withRedirect('/Reporting-App/public/pic/search/user/guard?search='.$search);
        }
        // $data = json_decode($result->getBody()->getContents(), true);
    }


    // Function show guard by user_id
    public function showGuardByUser(Request $request,Response $response, $args)
    {
        $id = $_SESSION['login']['id'];
// var_dump($id);die();
        $query = $request->getQueryParams();
        try {
            $result = $this->client->request('GET', $this->router->pathFor('api.guard.show'));
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $data = json_decode($result->getBody()->getContents(), true);
            var_dump($data);die();
    }

    // Function get user
    public function getUser(Request $request, Response $response, $args)
    {
        $guard = new \App\Models\GuardModel($this->db);
        $id = $_SESSION['login']['id'];
        // $guards = $guard->find('guard_id', $id);
        // $findGuard = $guard->findGuards('guard_id', $args['id'], $id);
        // var_dump($guards);die();
        try {
           //  $client = $this->client->request('GET','guard/user',[
           //      'query' => [
           //          'perpage'   => 10,
           //          'page'      => $request->getQueryParam('page'),
           //          'user_id'   => $_SESSION['login']['id']
           // ]]);
            $result = $this->client->request('GET', 'guard/user'. $request->getUri()->getQuery());
        } catch (GuzzleException $e) {
            // $content = json_decode($e->getResponse()->getBody()->getContents(), true);
            $result = $e->getResponse();
        }
        $data = json_decode($result->getBody()->getContents(), true);
        // print_r($data);die();
        // var_dump($data['data']);die();
        // return $this->view->render($response, 'users/guard/all-user.twig', [
        //     'data'          =>  $content['data'],
        //     'pagination'    =>  $content['pagination'],
        //     'guard'         =>  $content['data'],
       if (!isset($data['pagination'])) {
            $data['pagination'] = null;
        }
        return $this->view->render($response, 'users/guard/all-user.twig', [
            'data'          =>  $data['data'] ,
            'pagination'    =>  $data['pagination']
        ]);
    }

    public function getSearch($request, $response)
    {
        // var_dump($_SESSION['search']); die();
        if ($_SESSION['search'] == 1){
            return $this->view->render($response,'users/guard/search-user.twig');

        } else {
            return $this->view->render($response,'users/fellow/search-guard.twig');
        }
    }

    public function searchUser($request, $response, $args)
    {
        $user = new \App\Models\Users\UserModel($this->db);

        $searchParam = $request->getParam('search');
        $_SESSION['search_param'] = $searchParam;
        $search = $_SESSION['search'];
        $userId = $_SESSION['login']['id'];
        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perpage = $request->getQueryParam('perpage');
        $result = $user->search($searchParam, $userId)->setPaginate($page, 8);
        // var_dump($result); die();


        $data['users'] = $result['data'];
        $data['count']    = count($data['users']);
        $data['pagination'] = $result['pagination'];
        $data['search'] = $_SESSION['search_param'];
        // var_dump($search); die();
        if ($search == 1) {
            $data['guard'] = $_SESSION['guard'];
            return $this->view->render($response, 'users/guard/search-user.twig', $data);
        }else {
            $data['guard'] = $_SESSION['login']['id'];
            return $this->view->render($response, 'users/fellow/search-guard.twig', $data);
        }
    }

    // Function show guardian by user_id
    public function getUserGuard(Request $request, Response $response)
    {
        $_SESSION['search'] = 2;
        try {
            $result = $this->client->request('GET',
            $this->router->pathFor('api.guard.show'), [
                 'query' => [
                     'perpage' => 10,
                     'page' => $request->getQueryParam('page'),
                    //  'id' => $_SESSION['login']['id']
 			]]);
            // $content = json_decode($result->getBody()->getContents());
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($_SESSION['search']);die();
        return $this->view->render($response, 'users/fellow/all-guard.twig', [
            'data'          =>  $data['data'] ,
            'pagination'    =>  $data['pagination']
        ]);    // return $this->view->render($response, 'guard/show-user.twig', $content->reporting);
    }
}
