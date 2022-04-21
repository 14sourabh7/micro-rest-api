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

            if ($data['product_id']) {
                $status = $this->order->postOrder($data);
                if ($status) {
                    $this->response->setStatusCode(201);
                    return $this->response->setJsonContent(['message' => 'created']);
                }
            } else {
                $this->response->setStatusCode(404);
                return $this->response->setJsonContent(['error' => 'product id required']);
            }
        }
    }

    public function updateOrder()
    {

        if ($this->request->getPut()) {
            $data = $this->request->getPut();
            foreach ($data as $key => $value) {
                $data['key'] = $this->escaper->sanitize($value);
            }

            $status = $this->order->putOrder($data);
            if ($status) {
                $this->response->setStatusCode(201);
                return $this->response->setJsonContent(['message' => 'updated']);
            }
        }
    }
}
