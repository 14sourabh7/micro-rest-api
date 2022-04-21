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
        $email = $this->request->get('email');

        if ($user && $email) {
            $userExists =   $this->user->checkUser($user, $email);
            if ($userExists) {
                $privateKey = $this->config->get('api')->get('privatekey');

                $payload = array(
                    "user" => $user,
                    "email" => $email,
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
            $email = $data['email'];
            $userExists =   $this->user->getRole($user, $email);
            if ($userExists) {
                $privateKey = $this->config->get('api')->get('privatekey');

                $payload = array(
                    "bearer" => $userExists,
                    "email" => $email,
                    "user" => $user
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

    public function postUser($username, $email, $password)
    {

        $username = $this->request->getPost('name');
        $password = $this->request->getPost('password');
        $email = $this->request->getPost('email');
        if ($username && $password && $email) {
            $result = $this->user->addUser($username, $email, $password);
            if ($result) {
                $response = $this->response->setStatusCode(201);
                $this->response->setJsonContent(["success" => "created"]);
                return $response;
            } else {
                $response = $this->response->setStatusCode(201);
                $this->response->setJsonContent(["error" => "user already exists"]);
                return $response;
            }
        }
    }
}
