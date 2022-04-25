<?php

use Phalcon\Mvc\Controller;


class IndexController extends Controller
{
    public function indexAction()
    {
        $this->view->product = [];
        $this->view->locale = $this->locale;
        if ($this->request->get('search')) {
            $products = $this->dbHelper->searchProductByName($this->request->get('search'));
        } else {
            $products = $this->dbHelper->getAllProducts();
        }

        $this->view->products = $products;
    }

    /**
     * viewproductAction()
     *
     * function returning single product data to a ajax request
     * 
     * @return json
     */
    public function viewproductAction()
    {
        $id = $this->request->getPost('id');
        if ($id) {
            $product =  $this->dbHelper->getProduct($id);
        } else {
            $this->response->redirect('/');
        }
        return json_encode($product);;
    }

    public function recieveproductsAction()
    {
        $products = $this->request->getPost('product');

        if ($products['key'] == '123' && $products['email'] == 'sourabh@mail.com') {
            $this->mongo->frontend->products->deleteMany([]);
            foreach ($products as $key => $value) {
                $this->mongo->frontend->products->insertOne($value);
            }
        }
    }
}
