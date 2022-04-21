<?php

use Phalcon\Mvc\Controller;


class IndexController extends Controller
{
    public function indexAction()
    {
        $this->view->product = [];
        $this->view->locale = $this->locale;
        if ($this->request->get('search')) {
            $products = $this->api->searchProductByName($this->request->get('search'));
        } else {
            $products = $this->api->getAllProducts();
        }
        $this->view->products = $products;

        //handling delete  and update request
        if ($this->request->getPost('btn') == 'update') {
            $this->api->updateProduct($this->request->getPost());
            $this->response->redirect();
        } else if ($this->request->getPost('btn') == 'delete') {

            $this->api->deleteProducts($this->request->getPost('id'));
            $this->response->redirect();
        }
    }


    /**
     * addproductAction()
     * 
     * function to add product in database
     *
     * @return void
     */
    public function addproductAction()
    {
        $check = $this->request->isPost();
        $this->view->locale = $this->locale;
        if ($check) {

            $this->api->addProduct($this->request->getPost());
            $this->response->redirect('/');;
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
            $product =  $this->api->getProduct($id);
        } else {
            $this->response->redirect('/');
        }
        return json_encode($product);;
    }
}
