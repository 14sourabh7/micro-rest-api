<?php

use Phalcon\Mvc\Controller;


class OrderController extends Controller
{
    public function indexAction()
    {
        $this->view->locale = $this->locale;
        // if ($this->request->get('search')) {

        //     //calling search function of db
        //     $orders = $this->dbHelper->searchOrderByName(
        //         $this->request->get('search')
        //     );
        // } else
        if ($this->request->get('statusfilter')) {
            $startdate = $this->request->get('start');
            $enddate = $this->request->get('end');


            if ($startdate && $enddate) {
                $orders = $this->dbHelper->orderByDate($startdate, $enddate, $this->request->get('statusfilter'));
            } else {
                $orders = $this->dbHelper->orderByDate(
                    $this->request->get('date'),
                    date('Y-m-d'),
                    $this->request->get('statusfilter')
                );
            }
        } else if ($this->request->get('btn') == 'custom') {

            //custom date filter


        } else {

            //bydefault displaying all orders
            $orders = $this->dbHelper->getAllOrders();
        }
        $this->view->orders = $orders;
    }



    /**
     * addorderAction()
     * 
     * function to add a new order
     *
     * @return void
     */
    public function addorderAction()
    {
        $this->view->locale = $this->locale;
        if ($this->request->getPost()) {
            $data = $this->request->getPost();
            $data['date'] = date("Y-m-d");
            $this->dbHelper->addOrder($data);
            $this->response->redirect('/order');
        }
    }


    /**
     * getproductsAction()
     * 
     * function to get products to display in order form
     *
     * @return void
     */
    public function getproductsAction()
    {
        $products = $this->dbHelper->getAllProducts();

        $response = [];
        foreach ($products as $product => $details) {
            array_push($response, $details);
        }
        return json_encode($response);
    }


    /**
     * updatestatusAction()
     * 
     * action to update the status of order
     *
     * @return void
     */
    public function updatestatusAction()
    {
        $data = $this->request->get();

        //calling dbhelper class
        $this->dbHelper->updateOrderStatus($data);

        $this->response->redirect('/order');
    }
}
