<?php

use Phalcon\Mvc\Controller;

class UserController extends Controller
{
    public function indexAction()
    {



        $check = $this->request->isPost();
        if ($check) {
            $name = $this->escaper->sanitize($this->request->getPost('name'));
            $password = $this->escaper->sanitize($this->request->getPost('password'));
            $result = $this->api->checkUser($name, $password);
            if ($result) {
                $this->session->set('login', 1);
                $this->session->set('uid', $result->_id);
                $this->session->set('name', $result->username);
                $this->session->set('email', $result->email);
                $this->session->set('role', $result->role);
                $this->response->redirect('/');
            }
        }

        if ($this->session->get('login')) {
            $this->response->redirect('/user/connectapi');
        }
    }

    public function signupAction()
    {
        if ($this->session->get('login')) {
            $this->response->redirect('/');
        }
        $this->view->error = "";
        $check = $this->request->isPost();
        if ($check) {
            $name = $this->escaper->sanitize($this->request->getPost('username'));
            $email = $this->escaper->sanitize($this->request->getPost('email'));
            $password = $this->escaper->sanitize($this->request->getPost('password'));
            $result = $this->api->addUser($name, $email, $password);
            if ($result) {
                $this->response->redirect('/user');
            } else {
                $this->view->error = "user already exists";
            }
        }
    }


    public function connectapiAction()
    {
        $this->view->token = "";
        $this->view->auth = 0;
        if ($this->request->getPost('authorise')) {
            $token = $this->api->getAuth();
            if ($token['key']) {
                $token = $this->api->getAccess($token['key']);
                $this->response->redirect('/');
            }
        }
    }

    public function logoutAction()
    {
        $this->session->set('login', 0);
        $this->session->set('uid', 0);
        $this->session->set('name', 0);
        $this->session->set('email', 0);
        $this->session->set('role', 0);
        $this->response->redirect('/user');
    }

    public function webhookAction()
    {
        $this->view->url = "";
        if (!$this->session->get('login')) {
            $this->response->redirect('/user');
        }
        $name = $this->request->get('name');
        $secret = $this->request->get('secret');
        $event = $this->request->get('event');
        $url = urlencode($this->request->get('url'));
        if ($name && $secret && $event && $url) {
            $this->mongo->store->webhooks->insertOne(

                ['name' => $name, 'url' => $url, 'secret' => $secret, 'event' => $event,  "email" => $this->session->get('email')]
            );
            $this->view->url = "updated";
        }
    }
}
