<?php

namespace App\db;

use Phalcon\Di\Injectable;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class MiddlewareHelper extends Injectable
{
    /**
     * checkKey($key)
     * 
     * function to check key
     *
     * @param [type] $key
     * @return bool
     */
    public function checkKey($key)
    {
        try {
            $publicKey = $this->config->get('api')->get('publickey');
            $decoded = JWT::decode($key, new Key($publicKey, 'EdDSA'));
            $credentials = (array) $decoded;


            $aclFile = './acl.cache';
            $acl = unserialize(file_get_contents($aclFile));

            $application = new \Phalcon\Mvc\Application();
            $controller
                = $application->router->getControllerName();
            $action
                = $application->router->getActionName() ? $application->router->getActionName() : 'index';
            $bearer = $credentials['bearer'];


            if (true !== $acl->isAllowed($bearer, $controller, $action)) {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }


    /**
     * function to send invalid key response
     *
     * @return void
     */
    public function sendErrorResponse()
    {
        $this->response->setStatusCode(401);
        $this->response->setJsonContent(array('error' => "invalid key or access not granted"));
        $this->response->send();
        die;
    }

    /**
     * function to send key missing response
     *
     * @return void
     */
    public function keyNotFound()
    {
        $this->response->setStatusCode(404);
        $this->response->setJsonContent(array('error' => "key not provided"));
        $this->response->send();
        die;
    }
}
