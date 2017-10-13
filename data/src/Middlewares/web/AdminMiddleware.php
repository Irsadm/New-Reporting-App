<?php

namespace App\Middlewares\web;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AdminMiddleware extends BaseMiddleware
{
    public function __invoke($request, $response, $next)
    {
        if ($_SESSION['login']['status'] == 1) {

            $response = $next($request, $response);

            return $response;
        } else {
            $this->container->flash->addMessage('warning', 'Anda login sebagai admin untuk mengakses halaman ini!');

            return $response->withRedirect($this->container->router->pathFor('login.admin'));
        }
    }
}
