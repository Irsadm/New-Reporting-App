<?php

$app->get('/register', 'App\Controllers\web\UserController:getRegister')->setName('register');
$app->post('/register', 'App\Controllers\web\UserController:postRegister');
$app->get('/activateaccount/{token}', 'App\Controllers\web\UserController:activateAccount')->setName('register');
$app->get('/admin', 'App\Controllers\web\UserController:getLoginAsAdmin')->setName('login.admin');
$app->post('/admin', 'App\Controllers\web\UserController:loginAsAdmin');
$app->get('/user', 'App\Controllers\web\UserController:getAllUser');
$app->get('/', 'App\Controllers\web\UserController:getLogin')->setName('login');

$app->post('/', 'App\Controllers\web\UserController:login')->setName('post.login');

$app->get('/user/profile', 'App\Controllers\web\UserController:viewProfile')->setName('user.view.profile');

$app->group('', function() use ($app, $container) {
    $app->get('/home', 'App\Controllers\web\HomeController:index')->setName('home');
    $app->get('/logout', 'App\Controllers\web\UserController:logout')->setName('logout');
    $app->get('/setting', 'App\Controllers\web\UserController:getSettingAccount')->setName('user.setting');
    $app->post('/setting', 'App\Controllers\web\UserController:settingAccount');
    $app->get('/group', 'App\Controllers\web\GroupController:index')->setName('group');
    $app->get('/group/{id}', 'App\Controllers\web\GroupController:enter')->setName('pic.group');
    // $app->get('/group/{id}', function ($request, $response, $args) {
    //     return $this->view->render($response, 'user/group-list.twig');
    // });
    // $app->get('/grop', function ($request, $response) {
    //     return $this->view->render($response, 'users/group-list.twig');
    // });
    // $app->get('/grup', function ($request, $response) {
    //     return $this->view->render($response, 'users/group-list.twig');
    // });
    // ->add(new \App\Middlewares\web\GuardMiddleware($container));
})->add(new \App\Middlewares\web\AuthMiddleware($container));