<?php

use Phalcon\Mvc\Controller;

class OrderController extends Controller
{

    /**
     * getAll()
     * 
     * controller function to return all orders
     *
     * @return response
     */
    public function getAll()
    {
        $orders = $this->order->getAll();
        $this->response->setStatusCode(200);
        $response = $this->response->setJsonContent($orders);
        return $response;
    }


    public function getDataByDate($start, $end)
    {
        $orders = $this->order->getOrderByDate(
            $this->escaper->sanitize($start),
            $this->escaper->sanitize($end)
        );
        $this->response->setStatusCode(200);
        $response = $this->response->setJsonContent($orders);
        return $response;
    }

    public function getDataByDateFilter($start, $end, $filter)
    {
        $orders = $this->order->getOrderByDateFilter(
            $this->escaper->sanitize($start),
            $this->escaper->sanitize(
                $end
            ),
            $this->escaper->sanitize(
                $filter
            )
        );
        $this->response->setStatusCode(200);
        $response = $this->response->setJsonContent($orders);
        return $response;
    }
    public function addOrder()
    {
        if ($this->request->getPost()) {
            $data = $this->request->getPost();
            $data['email'] = $this->session->get('email');

            foreach ($data as $key => $value) {
                $data['key'] = $this->escaper->sanitize($value);
            }

            if (isset($data['product_id']) && isset($data['quantity'])) {

                if ($data['quantity'] > 0) {
                    $status = $this->order->postOrder($data);

                    if ($status) {
                        return $this->response->setJsonContent($status);
                    }
                } else {
                    $this->response->setStatusCode(404);
                    return $this->response->setJsonContent(['error' => 'quantity must be greater than 0']);
                }
            } else {
                $this->response->setStatusCode(404);
                return $this->response->setJsonContent(['error' => 'product id and quantity must be provided']);
            }
        } else {
            $this->response->setStatusCode(404);
            return $this->response->setJsonContent(['error' => 'no data provided']);
        }
    }

    public function updateOrder()
    {

        if ($this->request->getPost()) {
            $data = $this->request->getPost();

            foreach ($data as $key => $value) {
                $data['key'] = $this->escaper->sanitize($value);
            }
            if (isset($data['id']) && isset($data['status'])) {
                $status = $this->order->putOrder($data);
                if ($status) {
                    $this->response->setStatusCode(201);
                    return $this->response->setJsonContent(['message' => 'updated']);
                }
            } else {
                $this->response->setStatusCode(201);
                return $this->response->setJsonContent(['error' => 'id and status is required']);
            }
        }
    }
}
