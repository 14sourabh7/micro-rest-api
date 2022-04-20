<?php



use Phalcon\Mvc\Controller;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


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
                "user" => $user,
                "password" => $pass
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
        $keyword = explode(" ", urldecode($keyword));
        $products = $this->db->search($key, $keyword);
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
        $key = $this->request->get('key');
        $products = $this->db->getAll($key);
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
        $key = $this->request->get('key');
        $products = $this->db->get($key, $per_page, $page, $select, $filter);
        $response = $this->response->setJsonContent($products);
        return $response;
    }

    /**
     * notfound()
     * 
     * controller to send 404 response
     *
     * @return response
     */
    public function notfound()
    {
        $response = $this->response->setStatusCode(404);
        $response->setJsonContent(["error" => "Page not found"]);
        return $response;
    }
}
