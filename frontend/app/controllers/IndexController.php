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

        //handling delete  and update request
        if ($this->request->getPost('btn') == 'update') {
            $this->dbHelper->updateProduct($this->request->getPost());
        } else if ($this->request->getPost('btn') == 'delete') {

            $this->dbHelper->deleteProduct($this->request->getPost('id'));
            $this->response->redirect();
        }
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
        $check = $this->request->getPost();
        if ($check); {
            $products = $this->request->getPost();
            $secret = $this->request->getPost('secret') == '12345';
            if ($secret) {
                foreach ($products as $key => $value) {
                    $this->dbHelper->addUpdatedProduct($value);
                }
            }
        }
    }
}
