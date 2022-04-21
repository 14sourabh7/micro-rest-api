<?php

namespace App\Db;

use Phalcon\Di\Injectable;

class UserHelper extends Injectable
{


    public function checkUser($user, $email)
    {
        $result = $this->mongo->store->user->find(["username" => $user, "email" => $email]);
        foreach ($result as $user => $details) {
            return true;
        }
    }

    public function getRole($user, $email)
    {
        $result = $this->mongo->store->user->find(["username" => $user, "email" => $email]);
        foreach ($result as $user => $details) {
            return $details->role;
        }
    }
}
