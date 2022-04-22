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
        $acl->addComponent('product', ['index', 'search', 'getAll', 'get', 'getSingle', 'addProduct', 'updateProduct', 'deleteProduct']);
        $acl->addComponent('order', ['getAll', 'getDataByDate', 'getDataByDateFilter', 'adOrder', 'updateOrder']);
        $acl->allow('admin', '*', '*');
        $acl->allow('user', 'order', '*');
        file_put_contents($aclFile, serialize($acl));
        $this->response->setJsonContent(["status" => "permission granted"]);
        return $this->response;
    }
}
