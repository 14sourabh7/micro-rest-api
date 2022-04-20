<?php

use Phalcon\Mvc\Controller;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UserController extends Controller
{

    /**
     * function returning jwt token
     *
     * @return void
     */
    public function index()
    {

        $user = $this->request->get('user');
        $pass = $this->request->get('password');

        if ($user && $pass) {
            $userExists =   $this->db->checkUser($user, $pass);
            if ($userExists) {
                $privateKey = $this->config->get('api')->get('privatekey');

                $payload = array(
                    "user" => $user,
                    "password" => $pass
                );

                $jwt = JWT::encode($payload, $privateKey, 'EdDSA');

                $response = $this->response->setJsonContent(["key" => $jwt]);
                return $response;
            } else {
                $response = $this->response->setStatusCode(401);
                $this->response->setJsonContent(array('error' => "invalid user credentials"));
                return $response;
            }
        } else {

            $response = $this->response->setStatusCode(401);
            $this->response->setJsonContent(array('error' => "user and password not provided"));
            return $response;
        }
    }

    public function userAccesToken()
    {
        $token = $this->request->get('token');
        if ($token) {
            $publicKey = $this->config->get('api')->get('publickey');
            $decoded = JWT::decode($token, new Key($publicKey, 'EdDSA'));
            $data = (array)$decoded;
            $user = $data['user'];
            $password = $data['password'];
            $userExists =   $this->db->getRole($user, $password);
            if ($userExists) {
                $privateKey = $this->config->get('api')->get('privatekey');

                $payload = array(
                    "role" => $userExists
                );

                $jwt = JWT::encode($payload, $privateKey, 'EdDSA');
                $this->response->setStatusCode(200);
                $response = $this->response->setJsonContent(["key" => $jwt]);
                return $response;
            } else {
                $this->response->setStatusCode(401);
                $response = $this->response->setJsonContent(["error" => "invalid token"]);
                return $response;
            }
        } else {
            $this->response->setStatusCode(401);
            $response = $this->response->setJsonContent(["error" => "token not provided"]);
            return $response;
        }
    }
}
