<?php



use Phalcon\Mvc\Controller;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Role;
use Phalcon\Acl\Component;

class ProductController extends Controller
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
            $privateKey = "xP+ZnsWx0SNevsk6fj4+eSZ6RaOIIn5vZK/3avpMT9+DsIwgXMOTvahbYq9JCdEdHr+/t9fkKyvMzrkwQiykIw==";
            $payload = array(
                "bearer" => "admin"
            );
            $jwt = JWT::encode($payload, $privateKey, 'EdDSA');
            $response = $this->response->setJsonContent(["key" => $jwt]);
            return $response;
        } else {
            $response = $this->response->setStatusCode(401);
            $this->response->setJsonContent(array('error' => "invalid credentials"));
            return $response;
        }
    }


    /**
     * search($keyword)
     *
     * controller function to send search response
     * 
     * @param [type] $keyword
     * @return response
     */
    public function search($keyword)
    {
        $key = $this->request->get('key');
        if ($key) {
            if (!$this->checkKey($key)) {
                return $this->sendErrorResponse();
            }
            $keyword = explode(" ", urldecode($keyword));
            $products = $this->db->search($key, $keyword);
            $response = $this->response->setJsonContent($products);
            return $response;
        } else {
            return $this->keyNotFound();
        }
    }

    /**
     * getAll()
     * 
     * controller function to return all products
     *
     * @return response
     */
    public function getAll()
    {
        $key = $this->request->get('key');
        if ($key) {
            if (!$this->checkKey($key)) {
                return $this->sendErrorResponse();
            }
            $products = $this->db->getAll($key);
            $response = $this->response->setJsonContent($products);
            return $response;
        } else {
            return $this->keyNotFound();
        }
    }


    /**
     * get($key, $per_page, $page, $select, $filter)
     * 
     * controller to send products
     *
     * @param integer $per_page
     * @param integer $page
     * @param string $select
     * @param string $filter
     * @return response
     */
    public function get($per_page = 10, $page = 1, $select = "", $filter = "")
    {
        $key = $this->request->get('key');
        if ($key) {
            if (!$this->checkKey($key)) {
                return $this->sendErrorResponse();
            }
            $products = $this->db->get($key, $per_page, $page, $select, $filter);
            $response = $this->response->setJsonContent($products);
            return $response;
        } else {
            return $this->keyNotFound();
        }
    }

    /**
     * notfound()
     * 
     * controller to send 404 response
     *..
     * @return response
     */
    public function notfound()
    {
        $response = $this->response->setStatusCode(404);
        $response->setJsonContent(["error" => "Page not found"]);
        return $response;
    }


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
            $publicKey = "g7CMIFzDk72oW2KvSQnRHR6/v7fX5CsrzM65MEIspCM=";
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
        $response = $this->response->setStatusCode(401);
        $this->response->setJsonContent(array('error' => "invalid key or access not granted"));
        return $response;
    }

    /**
     * function to send key missing response
     *
     * @return void
     */
    public function keyNotFound()
    {
        $response = $this->response->setStatusCode(404);
        $this->response->setJsonContent(array('error' => "key missing"));
        return $response;
    }
}
