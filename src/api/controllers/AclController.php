<?php

use Phalcon\MVC\Controller;
use Phalcon\Acl\Adapter\Memory;

class AclController extends Controller
{
    public function index()
    {
        $aclFile = './acl.cache';
        $acl = new Memory();
        $acl->addRole('admin');
        $acl->addRole('user');
        $acl->addComponent('product', ['index', 'search', 'getAll', 'get']);
        $acl->allow('admin', '*', '*');
        $acl->allow('user', 'product', '*');
        file_put_contents($aclFile, serialize($acl));
        $this->response->setJsonContent(["status" => "permission granted"]);
        return $this->response;
    }
}
