<?php

namespace App\Controllers\web;
use App\Models\Item as Item;

use GuzzleHttp\Exception\BadResponseException as GuzzleException;


class HomeController extends BaseController
{
    public function index($request, $response)
    {
        $id = $_SESSION['login']['id'];

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

        try {
            $result = $this->client->request('GET', 'user/timeline/'.$id.'?',[
                'query' => [
                    'perpage' => 5,
                    'page' => $request->getQueryParam('page')
                    ]]);
                } catch (GuzzleException $e) {
                    $result = $e->getResponse();
                }

        $data = json_decode($result->getBody()->getContents(), true);
        if (!isset($data['pagination'])) {
        $data['pagination'] = null;
        }
        // var_dump($data);die();
        if ($_SESSION['login']['status'] == 2) {
            return $this->view->render($response, 'users/home.twig', [
                'data'       =>	$data['data'],
                'pagination' =>	$data['pagination']
    		]);

        } elseif ($_SESSION['login']['status'] == 1) {

        }

    }


    public function notFound($request, $response)
    {
        return $this->view->render($response, 'response/404.twig');
    }

    public function test($request, $response)
    {
        // $now = date('Y-m-d');
        // $date = date('Y-m-d', strtotime($now. '-1 day'));
        // var_dump($date);die;
        return  $this->view->render($response, 'response/test.twig');
    }

}
