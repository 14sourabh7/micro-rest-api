<?php



use Phalcon\Mvc\Controller;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


class ProductController extends Controller
{


    public function index()
    {


        $privateKey = "xP+ZnsWx0SNevsk6fj4+eSZ6RaOIIn5vZK/3avpMT9+DsIwgXMOTvahbYq9JCdEdHr+/t9fkKyvMzrkwQiykIw==";

        $publicKey = "g7CMIFzDk72oW2KvSQnRHR6/v7fX5CsrzM65MEIspCM=";

        $payload = array(
            "access" => 1
        );
        $jwt = JWT::encode($payload, $privateKey, 'EdDSA');
        return $jwt;
    }

    public function search($key, $keyword)
    {

        $keyword = explode(" ", urldecode($keyword));
        $products = $this->db->search($key, $keyword);
        return $products;
    }

    public function get($key, $per_page = 10, $page = 1, $select = "", $filter = "")
    {
        $products = $this->db->get($key, $per_page, $page, $select, $filter);
        return $products;
    }
}
