<?php

use Phalcon\Mvc\Controller;
use GuzzleHttp\Client;

class ProductController extends Controller
{


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

        $keyword = explode(" ", urldecode(
            $this->escaper->sanitize($keyword)
        ));
        $products = $this->product->search($keyword);
        $this->response->setStatusCode(200);
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

        $products = $this->product->getAll();
        $this->response->setStatusCode(200);
        $response = $this->response->setJsonContent($products);
        return $response;
    }


    /**
     * getSingle($id)
     * 
     * controller function to return all products
     *
     * @return response
     */
    public function getSingle($id)
    {

        $products = $this->product->getSingle(
            $this->escaper->sanitize($id)
        );
        $this->response->setStatusCode(200);
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

        $products = $this->product->get(
            $this->escaper->sanitize(
                $per_page
            ),
            $this->escaper->sanitize(
                $page
            ),
            $this->escaper->sanitize(
                $select
            ),
            $this->escaper->sanitize(
                $filter
            )
        );
        $this->response->setStatusCode(200);
        $response = $this->response->setJsonContent($products);
        return $response;
    }

    /**
     * addProduct()
     * 
     * calling post product function and returning the response
     *
     * @return json
     */
    public function addProduct()
    {
        if ($this->request->getPost()) {
            $data = $this->request->getPost();
            foreach ($data as $key => $value) {
                $data['key'] = $this->escaper->sanitize($value);
            };
            if (isset($data['name']) && isset($data['price']) && isset($data['category']) && isset($data['stock'])) {

                if ($data['price'] > 0 && $data['stock'] > 0) {
                    $status = $this->product->postProduct($data);
                    $id =  $status->getInsertedId();
                    $id = (array)$id;
                    if ($status) {
                        $this->response->setStatusCode(201);
                        return $this->response->setJsonContent(['message' => 'product added successfully with id -  ' . $id['oid']]);
                    }
                } else {
                    $this->response->setStatusCode(401);
                    return $this->response->setJsonContent(['error' => 'name and stock must be greater than 0']);
                }
            } else {
                $this->response->setStatusCode(401);
                return $this->response->setJsonContent(['error' => 'name ,price, category, stock must be provided']);
            }
        } else {
            $this->response->setStatusCode(401);
            return $this->response->setJsonContent(['error' => 'no data provided']);
        }
    }


    /**
     * updateProduct()
     * 
     * function to return update response
     *
     * @return json
     */
    public function updateProduct()
    {

        if ($this->request->getPut()) {
            $data = $this->request->getPut();

            if (isset($data['id'])) {
                $status = $this->product->putProduct($data);
                if ($status) {
                    $this->response->setStatusCode(201);
                    return $this->response->setJsonContent(['message' => 'updated']);
                }
            } else {
                $this->response->setStatusCode(401);
                return $this->response->setJsonContent(['error' => 'product id must be provided']);
            }
        } else {
            $this->response->setStatusCode(401);
            return $this->response->setJsonContent(['error' => 'no data provided']);
        }
    }

    /**
     * deleteProduct($id)
     * 
     * function to call delete product method and return response
     *
     * @param [type] $id
     * @return json
     */
    public function deleteProduct($id)
    {
        if ($id) {
            $status = $this->product->deleteProduct($this->escaper->sanitize($id));
            if ($status) {
                $this->response->setStatusCode(201);
                return $this->response->setJsonContent(['message' => 'deleted']);
            }
        }
    }
}
