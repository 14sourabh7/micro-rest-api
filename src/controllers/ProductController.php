<?php

use Phalcon\Mvc\Controller;
use Firebase\JWT\JWT;

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

            $privateKey = $this->config->get('api')->get('privatekey');

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

        $keyword = explode(" ", urldecode($keyword));
        $products = $this->db->search($keyword);

        $response = $this->response->setJsonContent($products);
        return $response;
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

        $products = $this->db->getAll();

        $response = $this->response->setJsonContent($products);
        return $response;
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

        $products = $this->db->get($per_page, $page, $select, $filter);

        $response = $this->response->setJsonContent($products);
        return $response;
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
}
