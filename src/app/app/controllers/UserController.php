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
            $this->response->redirect('/');
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
            $this->view->token = $token['key'];
            $this->view->auth = 1;
        }
        if ($this->request->getPost('access')) {
            $token = $this->api->getAccess($this->request->getPost('token'));
        }
    }
}
