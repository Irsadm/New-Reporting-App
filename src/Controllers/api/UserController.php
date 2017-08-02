<?php

namespace App\Controllers\api;

use App\Models\Users\UserModel;
use App\Models\Users\UserToken;

class UserController extends BaseController
{
    //Get all user
    public function index($request, $response)
    {
        $user = new UserModel($this->db);

        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perPage = $request->getParsedBody()['perpage'];
        $getUser = $user->getAllUser()->setPaginate($page, 2);

        if ($getUser) {
            $data = $this->responseDetail(200, false, 'Data tersedia', [
                'data' => $getUser['data'],
                'pagination' => $getUser['pagination']
            ]);

        } else {
            $data = $this->responseDetail(200, false, 'Berhasil');
        }

        return $data;
    }

    //User register
    public function register($request, $response)
    {
        $mailer = new \App\Extensions\Mailers\Mailer();
        $registers = new \App\Models\RegisterModel($this->db);
        $user = new UserModel($this->db);

        $this->validator
        ->rule('required', ['username', 'password', 'email'])
        ->message('{field} tidak boleh kosong!')
        ->label('Username', 'Password', 'Email');

        $this->validator->rule('email', 'email');
        $this->validator->rule('alphaNum', 'username');
        $this->validator->rule('lengthMax', [
        'username',
        'name',
        'password'
        ], 30);

        $this->validator->rule('lengthMin', ['username','password'], 5);

        if ($this->validator->validate()) {

            $base = $request->getUri()->getBaseUrl();

            if (!empty($request->getUploadedFiles()['image'])) {
                $storage = new \Upload\Storage\FileSystem('assets/images');
                $image = new \Upload\File('image',$storage);

                $image->setName(uniqid('img-'.date('Ymd').'-'));
                $image->addValidations(array(
                new \Upload\Validation\Mimetype(array('image/png', 'image/gif',
                'image/jpg', 'image/jpeg')),
                new \Upload\Validation\Size('5M')
                ));

                $image->upload();
                $imageName = $base.'/assets/images'.$image->getNameWithExtension();

            } else {
                $imageName = $base.'/assets/images/avatar.png';
            }

            $register = $user->checkDuplicate($request->getParsedBody()['username'],
            $request->getParsedBody()['email']);

            if ($register == 3) {
                return $this->responseDetail(409, true, 'Email & username sudah digunakan');

            } elseif ($register == 1) {
                return $this->responseDetail(409, true, 'Username sudah digunakan');

            } elseif ($register == 2) {
                return $this->responseDetail(409, true, 'Email sudah digunakan');

            } else {
                $userId = $user->createUser($request->getParsedBody(), $imageName);
                $newUser = $user->getUser('id', $userId);

                $token = md5(openssl_random_pseudo_bytes(8));
                $tokenId = $registers->setToken($userId, $token);
                $userToken = $registers->find('id', $tokenId);

                $keyToken = $userToken['token'];

                $activateUrl = '<a href ='.$base ."/activateaccount/".$keyToken.'>
                <h3>AKTIFKAN AKUN</h3></a>';
                $content = "Terima kasih telah mendaftar di Reporting App.
                Untuk mengaktifkan akun Anda, silakan klik link di bawah ini.
                <br /> <br />" .$activateUrl."<br /> <br />
                Jika link tidak bekerja, Anda dapat menyalin atau mengetik kembali
                link di bawah ini. <br /><br /> " .$base ."/activateaccount/".$keyToken.
                " <br /><br /> Terima kasih, <br /><br /> Admin Reporting App";

                $mail = [
                'subject'   =>  'Reporting App - Verifikasi Email',
                'from'      =>  'reportingmit@gmail.com',
                'to'        =>  $newUser['email'],
                'sender'    =>  'Reporting App',
                'receiver'  =>  $newUser['name'],
                'content'   =>  $content,
                ];

                $mailer->send($mail);

                return  $this->responseDetail(201, false, 'Pendaftaran berhasil.
                silakan cek email anda untuk mengaktifkan akun');
            }
        } else {
            $errors = $this->validator->errors();

            return  $this->responseDetail(422, $errors);
        }

    }


    public function postImage($request, $response, $args)
    {
        $user = new UserModel($this->db);

        $findUser = $user->getUser('id', $args['id']);

        if (!$findUser) {
            return $this->responseDetail(404, true, 'Akun tidak ditemukan');
        }
        if ($this->validator->validate()) {

            if (!empty($request->getUploadedFiles()['image'])) {
                $storage = new \Upload\Storage\FileSystem('assets/images');
                $image = new \Upload\File('image',$storage);

                $image->setName(uniqid('img-'.date('Ymd').'-'));
                $image->addValidations(array(
                    new \Upload\Validation\Mimetype(array('image/png', 'image/gif',
                    'image/jpg', 'image/jpeg')),
                    new \Upload\Validation\Size('5M')
                ));

                $image->upload();
                $data['image'] = $image->getNameWithExtension();

                $user->updateData($data, $args['id']);
                $newUser = $user->getUser('id', $args['id']);

                return  $this->responseDetail(200, false, 'Foto berhasil diunggah', [
                    'result' => $newUser
                ]);

            } else {
                return $this->responseDetail(400, true, 'File foto belum dipilih');

            }
        } else {
            $errors = $this->validator->errors();

            return  $this->responseDetail(422, $errors);
        }

    }

    //Delete user account by id
    public function deleteUser($request, $response, $args)
    {
        $user = new UserModel($this->db);
        $findUser = $user->find('id', $args['id']);
        $token = $request->getHeader('Authorization')[0];

        if ($findUser) {
            $user->hardDelete($args['id']);
            $data['id'] = $args['id'];
            $data = $this->responseDetail(200, false, 'Akun berhasil dihapus');
        } else {
            $data = $this->responseDetail(400, true, 'Akun tidak ditemukan');
        }

        return $data;
    }

    //Delete user account
    public function delAccount($request, $response)
    {
        $users = new UserModel($this->db);
        $userToken = new \App\Models\Users\UserToken($this->container->db);

        $token = $request->getHeader('Authorization')[0];

        $findUser = $userToken->find('token', $token);
        $user = $users->find('id', $findUser['user_id']);

        if ($user) {
            $users->hardDelete($user['id']);
            $data['id'] = $user['id'];
            $data = $this->responseDetail(200, false, 'Akun berhasil dihapus');
        } else {
            $data = $this->responseDetail(400, true, 'Akun tidak ditemukan');
        }
        return $data;
    }

    //Update user account by id
    public function updateUser($request, $response, $args)
    {
        $user = new UserModel($this->db);
        $findUser = $user->find('id', $args['id']);

        if ($findUser) {
            $this->validator->rule('required', ['name', 'email', 'username',
            'password', 'gender', 'address', 'phone', 'image']);
            $this->validator->rule('email', 'email');
            $this->validator->rule('alphaNum', 'username');
            $this->validator->rule('numeric', 'phone');
            $this->validator->rule('lengthMin', ['name', 'email', 'username', 'password'], 5);
            $this->validator->rule('integer', 'id');

            if ($this->validator->validate()) {
                $user->updateData($request->getParsedBody(), $args['id']);
                $data['update data'] = $request->getParsedBody();

                $data = $this->responseDetail(200, false, 'Data berhasil diperbarui', [
                    'data'  => $data,
                ]);
            } else {
                $data = $this->responseDetail(400, true, $this->validator->errors());
            }
        } else {
            $data = $this->responseDetail(404, true, 'Akun tidak ditemukan');
        }
        return $data;
    }

    //Update user account
    public function editAccount($request, $response)
    {
        $users = new UserModel($this->db);
        $userToken = new \App\Models\Users\UserToken($this->container->db);

        $token = $request->getHeader('Authorization')[0];
        $user = $userToken->find('token', $token);
        $findUser = $users->find('id', $user['user_id']);

        if ($findUser) {
            $this->validator->rule('required', ['name', 'email', 'username',
            'password', 'gender', 'address', 'phone', 'image']);
            $this->validator->rule('email', 'email');
            $this->validator->rule('alphaNum', 'username');
            $this->validator->rule('numeric', 'phone');
            $this->validator->rule('lengthMin', ['name', 'email', 'username', 'password'], 5);
            $this->validator->rule('integer', 'id');
            if ($this->validator->validate()) {
                $users->updateData($request->getParsedBody(), $user['user_id']);
                $data['update data'] = $request->getParsedBody();

                $data = $this->responseDetail(200, false, 'Data berhasil diupdate', [
                    'data'  => $data
                    ]);
            } else {
                $data = $this->responseDetail(400, true, $this->validator->errors());
            }
        } else {
            $data = $this->responseDetail(400, true, 'Data tidak ditemukan');
        }
        return $data;
    }

    //Find User by id
    public function findUser($request, $response, $args)
    {
        $user = new UserModel($this->db);
        $findUser = $user->find('id', $args['id']);

        if ($findUser) {
            $data = $this->responseDetail(200, false, 'Data tersedia', [
            'data'    => $findUser,
            ]);
        } else {
            $data = $this->responseDetail(400, true, 'Akun tidak ditemukan');
        }

        return $data;
    }

    //Find User by id
    public function detailAccount($request, $response)
    {
        $users = new UserModel($this->db);
        $userToken = new \App\Models\Users\UserToken($this->container->db);

        $token = $request->getHeader('Authorization')[0];
        $user = $userToken->find('token', $token);
        $findUser = $users->find('id', $user['user_id']);

        if ($findUser) {
            $data = $this->responseDetail(200, false, 'Data tersedia', [
                'data'  => $findUser
                ]);
        } else {
            $data = $this->responseDetail(400, true, 'Data tidak ditemukan');
        }

        return $data;
    }

    //User login
    public function login($request, $response)
    {
        $user = new UserModel($this->db);
        $login = $user->find('username', $request->getParam('username'));
        $user = $user->getUser('username', $request->getParam('username'));

        if (empty($login)) {
            $data = $this->responseDetail(401, true, 'Username tidak terdaftar');
        } else {
            $check = password_verify($request->getParam('password'), $login['password']);
            if ($check) {
                $token = new UserToken($this->db);
                $token->setToken($login['id']);
                $getToken = $token->find('user_id', $login['id']);

                $key = [
                'key_token' => $getToken['token'],
                ];
                $data = $this->responseDetail(200, false, 'Login berhasil', [
                'data'   => $user,
                'meta'     => $key
                ]);
            } else {
                $data = $this->responseDetail(401, true, 'Password salah');
            }
        }
        return $data;
    }

    //Set item to user in group
    // public function setItemUser($request, $response, $args)
    // {
    //     $user = new UserModel($this->db);
    //     $findUser = $user->find('id', $request->getParsedBody()['user_id']);
    //     $group = new \App\Models\GroupModel($this->db);
    //     $findGroup = $group->find('id', $args['group']);
    //
    //     $token = $request->getHeader('Authorization')[0];
    //
    //     $userToken = new \App\Models\Users\UserToken($this->db);
    //
    //     if ($findUser && $findGroup) {
    //         $data['user_id'] = $findUser['id'];
    //         $item = new \App\Models\UserItem($this->db);
    //         // $findUserGroup = $item->findUser('user_id', $args['id'], 'group_id', $args['group']);
    //
    //         $this->validator->rule('required', ['item_id', 'user_id']);
    //         $this->validator->rule('integer', ['id']);
    //
    //         if ($this->validator->validate()) {
    //             $item->setItem($request->getParsedBody(), $args['group']);
    //             $data = $request->getParsedBody();
    //
    //
    //             $data = $this->responseDetail(201, false, 'Succes managed to select the item', $data, $findUser);
    //         } else {
    //             // $data['status_code'] = 400;
    //             // $data['status_message'] = "Error";
    //             // $data['data'] = $this->validator->errors();
    //
    //             $data = $this->responseDetail(400, true, $this->validator->errors());
    //         }
    //
    //         return $data;
    //
    //         $items = $user->find('id', $args['id']);
    //         $item = $request->getParsedBody();
    //
    //         $data = $this->responseDetail(201, false, 'user Succes Purchased', $items, $item);
    //     } else {
    //         $data = $this->responseDetail(404, true, 'user Not Found');
    //     }
    //
    //     return $data;
    //
    // }

    public function activateAccount($request, $response, $args)
    {
        $users = new UserModel($this->db);
        $registers = new \App\Models\RegisterModel($this->db);

        $userToken = $registers->find('token', $args['token']);
        $base = $request->getUri()->getBaseUrl();
        $now = date('Y-m-d H:i:s');

        if ($userToken && $userToken['expired_date'] > $now) {

            $user = $users->setActive($userToken['user_id']);
            $registers->hardDelete($userToken['id']);

            return  $this->view->render($response, 'response/activation.twig', [
                'message' => 'Akun telah berhasil diaktivasi'
            ]);

        } elseif ($userToken['expired_date'] > $now) {

            return  $this->view->render($response, 'response/activation.twig', [
                'message' => 'Token telah kadaluarsa'
            ]);
            // return $this->responseDetail(400, true, 'Token telah kadaluarsa');

        } else{

            return  $this->view->render($response, 'response/activation.twig', [
                'message' => 'Token salah atau anda belum mendaftar'
            ]);
            // return $this->responseDetail(400, true, 'Anda belum mendaftar');
        }

    }

    public function logout($request, $response )
    {
        $token = $request->getHeader('Authorization')[0];

        $userToken = new UserToken($this->db);
        $findUser = $userToken->find('token', $token);

        $userToken->delete('user_id', $findUser['user_id']);
        return $this->responseDetail(200, false, 'Logout berhasil');
    }

    public function forgotPassword($request, $response)
    {
        $users = new UserModel($this->db);
        $mailer = new \App\Extensions\Mailers\Mailer();
        $registers = new \App\Models\RegisterModel($this->db);

        $findUser = $users->find('email', $request->getParsedBody()['email']);
        $token = 'rec-'.md5(openssl_random_pseudo_bytes(8));
        $tokenId = $registers->setToken($findUser['id'], $token);
        $tokenSet = $registers->find('token', $token);

        if (!$findUser) {
            return $this->responseDetail(404, true, 'Email tidak terdaftar');

        } elseif ($findUser && $tokenSet) {
            $base = $request->getUri()->getBaseUrl();

            $activateUrl = '<a href ='.$base ."/reset/".$token.'>
            <h3>SETEL ULANG SANDI</h3></a>';

            $content = "Yang terhormat ".$findUser['name'].",
            <br /> <br /> Baru-baru ini Anda meminta untuk menyetel ulang kata sandi akun Reporting App Anda.
            Untuk mengubah kata sandi akun Anda, silakan klik tautan di bawah ini.
            <br /> <br />" .$activateUrl."<br /> <br />
            Jika tautan tidak bekerja, Anda dapat menyalin atau mengetik kembali
            tautan di bawah ini. <br /><br /> " .$base ."/api/recovery/".$token.
            " <br /><br /> Jika Anda tidak seharusnya menerima email ini, mungkin pengguna lain
            memasukkan alamat email Anda secara tidak sengaja saat mencoba menyetel
            ulang sandi. Jika Anda tidak memulai permintaan ini, Anda tidak perlu
            melakukan tindakan lebih lanjut dan dapat mengabaikan email ini dengan aman. <br /><br />
            Terima kasih, <br /><br /> Admin Reporting App";

            $mail = [
            'subject'   =>  'Setel Ulang Sandi',
            'from'      =>  'reportingmit@gmail.com',
            'to'        =>  $findUser['email'],
            'sender'    =>  'Reporting App Account Recovery',
            'receiver'  =>  $findUser['name'],
            'content'   =>  $content,
            ];

            $mailer->send($mail);

            return $this->responseDetail(200, false, 'Silakan cek email anda untuk mengubah password');
        }

    }

    //Change password
    public function changePassword($request, $response, $args)
    {
        $users = new UserModel($this->db);
        $token = new \App\Models\Users\UserToken($this->container->db);

        $findUser = $users->getUser('email', $request->getParsedBody()['email']);
        $findToken = $token->find('token', $request->getParsedBody()['token']);
        // var_dump($user);die();

        if ($findUser['id'] == $findToken['user_id']) {
            $this->validator->rule('required', ['email', 'password']);
            $this->validator->rule('equals', 'password2', 'password');
            $this->validator->rule('email', 'email');
            $this->validator->rule('lengthMin', ['password'], 5);

            if ($this->validator->validate()) {
                $newData = [
                'password'  => password_hash($request->getParsedBody()['password'], PASSWORD_BCRYPT)
                ];
                $users->updateData($newData, $findUser['id']);
                $data['result'] = $findUser;

                $data = $this->responseDetail(200, false, 'Update Data Succes', [
                    'data'  => $data
                    ]);
            } else {
                $data = $this->responseDetail(400, true, $this->validator->errors());
            }
        } else {
            $data = $this->responseDetail(404, true, 'Data Not Found');
        }
        return $data;
    }
}
