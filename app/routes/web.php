<?php
$app->get('/signup', 'App\Controllers\web\UserController:getSignUp')->setName('signup');
$app->post('/signup', 'App\Controllers\web\UserController:signUp')->setName('post.signup');
$app->post('/reset', 'App\Controllers\web\UserController:forgotPassword')->setName('password.reset');
$app->get('/test', 'App\Controllers\web\HomeController:test')->setName('test');
$app->get('/404', 'App\Controllers\web\HomeController:notFound')->setName('not.found');
$app->get('/admin', 'App\Controllers\web\UserController:getLoginAsAdmin')->setName('login.admin');
$app->post('/admin', 'App\Controllers\web\UserController:loginAsAdmin');
$app->get('/user', 'App\Controllers\web\UserController:getAllUser');
$app->get('/', 'App\Controllers\web\UserController:getLogin')->setName('login');
$app->post('/', 'App\Controllers\web\UserController:login')->setName('post.login');
$app->get('/password/reset/{token}', 'App\Controllers\web\UserController:getResetPassword')->setName('get.reset');
$app->post('/password/reset', 'App\Controllers\web\UserController:resetPassword')->setName('post.reset');
$app->get('/recovery', 'App\Controllers\web\UserController:getRecoveryPage')->setName('recovery.password');
//
// $app->get('/guard/show', 'App\Controllers\web\GuardController:showGuardByUser');
// $app->get('/guard/show/{id}', 'App\Controllers\web\GuardController:showUserByGuard');
// $app->get('/guard/delete/{id}', 'App\Controllers\web\GuardController:deleteGuardian');
$app->group('', function() use ($app, $container) {
    $app->get('/home', 'App\Controllers\web\HomeController:index')->setName('home');
    $app->get('/logout', 'App\Controllers\web\UserController:logout')->setName('logout');
    // $app->get('/profile', 'App\Controllers\web\UserController:viewProfile')->setName('user.profile');
    // $app->get('/setting', 'App\Controllers\web\UserController:getSettingAccount')->setName('user.setting');
    // $app->post('/setting', 'App\Controllers\web\UserController:settingAccount');
    $app->group('/group', function() use ($app, $container) {
        $app->get('', 'App\Controllers\web\GroupController:index')->setName('group');
        $app->get('/{id}', 'App\Controllers\web\GroupController:enter')->setName('pic.group');
        $app->get('/enter/{id}', 'App\Controllers\web\GroupController:enterGroup')->setName('enter.group');
        $app->post('/create', 'App\Controllers\web\GroupController:add')->setName('web.group.add');
        $app->post('/group/create', 'App\Controllers\web\GroupController:createByUser')->setName('pic.create.group');
        $app->get('/{id}/leave', 'App\Controllers\web\GroupController:leaveGroup')->setName('web.leave.group');
        $app->get('/{user}/{group}/item/reported', 'App\Controllers\web\ItemController:getReportedUserGroupItem')->setName('reported.item.user.group');
        $app->get('/{user}/{group}/item/unreported', 'App\Controllers\web\ItemController:getUnreportedUserGroupItem')->setName('unreported.item.user.group');
        $app->get('/{id}/members', 'App\Controllers\web\GroupController:getAllGroupMember')->setName('get.group.member');
        // $app->get('item/{user}/member', 'App\Controllers\web\GroupController:getUnreportedUserGroupItem')->setName('unreported.item.user.group');
        // $app->get('/item/{user}/pic', 'App\Controllers\web\GroupController:getUnreportedUserGroupItem')->setName('unreported.item.user.group');
        // $app->get('/{id}/pics', 'App\Controllers\web\GroupController:getGroupPic')->setName('get.group.pic');
        // $app->get('/pic/create', 'App\Controllers\web\GroupController:createByUser')->setName('pic.create.group');
        // $app->get('/item/{group}', 'App\Controllers\web\ItemController:createItemUser')->setName('web.item.user.create');
    });

    $app->group('/item', function() use ($app, $container) {
        $app->get('/show/{id}', 'App\Controllers\web\HomeController:showItem')->setName('show.item');
        $app->get('/group/{group}', 'App\Controllers\web\ItemController:getGroupItem')->setName('group.item');
        $app->get('/group/{group}/reported', 'App\Controllers\web\ItemController:getReportedGroupItem')->setname('web.reported.group.item');
        // $app->get('/report/{item}', 'App\Controllers\web\ItemController:reportItem')->setname('web.report.item');
        $app->post('/create/{group}', 'App\Controllers\web\ItemController:createItemUser')->setName('web.item.user.create');
        $app->post('/report/{item}', 'App\Controllers\web\ItemController:reportItem')->setname('report.item');
        $app->get('/{item}/user', 'App\Controllers\web\ItemController:deleteItemByUser')->setname('web.user.delete.item');
        $app->post('/comment', 'App\Controllers\web\CommentController:postComment')->setName('post.comment');
        $app->get('/archive/{id}', 'App\Controllers\web\ItemController:getItemArchive')->setName('item.archive');
        $app->post('/archive/{id}', 'App\Controllers\web\ItemController:searchItemArchive')->setName('search.item.archive');
    });
    $app->group('/user', function() use ($app, $container) {
        $app->get('/groups', 'App\Controllers\web\GroupController:getGeneralGroup')->setName('group.user');
        $app->get('/profile', 'App\Controllers\web\UserController:viewProfile')->setName('user.view.profile');
        $app->get('/profile/setting', 'App\Controllers\web\UserController:settingProfile')->setName('user.setting.profile');
        $app->post('/profile/update/{id}', 'App\Controllers\web\UserController:updateProfile')->setName('user.update.profile');
        $app->post('/image/change', 'App\Controllers\web\UserController:changeImage')->setName('user.change.image');
        $app->get('/change/password', 'App\Controllers\web\UserController:getChangePassword')->setName('change.password');
        $app->post('/change/password', 'App\Controllers\web\UserController:postChangePassword')->setName('post.change.password');
    });


    $app->group('/guard', function() use ($app, $container) {
        // $app->get('/show/user', 'App\Controllers\web\GuardController:showGuardByUser');
        $app->get('/user', 'App\Controllers\web\GuardController:getUser');
        $app->get('/show/user', 'App\Controllers\web\GuardController:getUserByGuard')->setName('guard.show.user');
        $app->get('/show/guard', 'App\Controllers\web\GuardController:getUserGuard')->setName('guard.show');
        $app->delete('/delete/{id}', 'App\Controllers\web\GuardController:deleteGuardian');
        $app->get('/search', 'App\Controllers\web\GuardController:getSearch')->setName('guard.search');
        $app->get('/search/user', 'App\Controllers\web\GuardController:searchUser')->setName('guard.search.user');
        $app->get('/request/guardian/{user}', 'App\Controllers\web\GuardController:requestToBeGuard')->setName('web.guard.request');
        $app->get('/request/fellow/{guard}', 'App\Controllers\web\GuardController:requestToBeFellow')->setName('web.fellow.request');

    });
    $app->group('/pic', function() use ($app, $container){
        $app->get('/items/group/{id}', 'App\Controllers\web\PicController:getUnreportedItem')->setName('pic.item.group');
        $app->get('/items/group/{id}/reported', 'App\Controllers\web\PicController:getReportedItem')->setName('web.pic.group.reported');
        $app->get('/items/delete/{id}', 'App\Controllers\web\PicController:deleteTugas')->setName('web.pic.delete.tugas');
        $app->get('/search/user', 'App\Controllers\web\UserController:searchUser')->setName('web.pic.search.user');
        $app->get('/search/{id}', 'App\Controllers\web\PicController:getSearchUser')->setName('web.pic.search');
        $app->get('/search/user/guard', 'App\Controllers\web\PicController:searchUser')->setName('web.pic.search.user2');
        $app->get('/group/{id}/member', 'App\Controllers\web\PicController:getMemberGroup')->setName('pic.group.member');
        $app->post('/set/member', 'App\Controllers\web\GroupController:setMemberGroup')->setName('web.pic.set.member');
        $app->get('/delete/member/{id}/{group}', 'App\Controllers\web\GroupController:deleteUser')->setName('web.pic.delete.member');
        $app->get('/set/status/{id}/{group}', 'App\Controllers\web\GroupController:setAsPicGroup')->setName('web.pic.set.status');
        $app->get('/set/member/{id}/{group}', 'App\Controllers\web\GroupController:setAsMemberGroup')->setName('web.pic.set.as.member');
        $app->post('/create/item', 'App\Controllers\web\PicController:createItem')->setName('web.pic.create.item');
        $app->get('/show/item/{id}', 'App\Controllers\web\PicController:showItem')->setName('web.pic.show.item');
        $app->post('/set/guardian', 'App\Controllers\web\GuardController:createGuardian')->setName('web.pic.set.guardian');
        $app->post('/comment', 'App\Controllers\web\CommentController:postPicComment')->setName('pic.post.comment');
        $app->post('/edit/item/{id}', 'App\Controllers\web\PicController:editItem')->setName('pic.edit.item');
    });
    // ->add(new \App\Middlewares\web\GuardMiddleware($container));
    // )->add(new \App\Middlewares\web\AuthMiddleware($container)
})->add(new \App\Middlewares\web\AuthMiddleware($container));
