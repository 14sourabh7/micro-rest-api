<?php

use Phalcon\MVC\Controller;
use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Role;
use Phalcon\Acl\Component;

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
        return 'permissions granted';
    }
}
