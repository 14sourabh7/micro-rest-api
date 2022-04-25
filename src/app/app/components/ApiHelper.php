<?php
//db queries
namespace App\Components;

use Phalcon\Di\Injectable;
use GuzzleHttp\Client;


class ApiHelper extends Injectable
{

    private $client;
    private $key;
    public function __construct()
    {

        $this->client = $this->setClient();
        $this->checkToken();
        $this->key = $this->session->get('token');
    }


    /**
     * setClient()
     * 
     * function to initialze Guzzle
     *
     * @return $client object of class Client
     */
    private function setClient()
    {
        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->config->get('api')->get('base_uri'),
        ]);
        return $client;
    }




    /**
     * getAll($document)
     * 
     * class function to find all from document
     *
     * @param [type] $document
     * @return void
     */
    private function getAll($controller)
    {
        $response = $this->client->request(
            'GET',
            "/$controller/get?key=" . $this->key
        );

        $data = $response->getBody();
        return
            json_decode($data, TRUE);
    }

    /**
     * getProduct($id)
     * 
     * class function to find a single product
     *
     * @param [type] $document
     * @param [type] $id
     * @return object
     */
    private function getSingleProduct($id)
    {
        $response = $this->client->request(
            'GET',
            "/product/get/$id?key=" . $this->key
        );

        $data = $response->getBody();
        return
            json_decode($data, TRUE);
    }


    /**
     * searchName($document,$name)
     * 
     * class function to search in db
     *
     * @param [type] $document
     * @param [type] $name
     * @return void
     */
    private function searchName($name)
    {

        $response = $this->client->request(
            'GET',
            "/product/search/$name?key=" . $this->key
        );

        $data = $response->getBody();
        return
            json_decode($data, TRUE);
    }

    /**
     * addData($data)
     * 
     * class function to add data 
     *
     * @param [type] $data
     * @return void
     */
    private function addData($controller, $data)
    {
        $response = $this->client->request(
            'POST',
            "/$controller/post/?key=" . $this->key,
            ['form_params' => $data]
        );

        $data = $response->getBody();
        return
            json_decode($data, TRUE);
    }

    /**
     * updateData($id,$data)
     * 
     * class function to update data
     *
     * @param [type] $document
     * @param [type] $id
     * @param [type] $data
     * @return void
     */
    private function updateData($controller, $data)
    {
        $response = $this->client->request(
            'PUT',
            "/$controller/put/?key=" . $this->key,
            ['form_params' => $data]
        );

        $data = $response->getBody();

        return
            json_decode($data, TRUE);
    }

    /**
     * deleteData($document,$id)
     * 
     * function to delete data
     *
     * @param [type] $document
     * @param [type] $id
     * @return void
     */
    private function deleteProduct($id)
    {
        $response = $this->client->request(
            'DELETE',
            "/product/delete/$id/?key=" . $this->key

        );

        $data = $response->getBody();
        return
            json_decode($data, TRUE);
    }


    /**
     * function to filter data by date only
     *
     * @param [type] $document
     * @param [type] $start
     * @param [type] $end
     * @return void
     */
    private function getDataByDate($start, $end)
    {
        // return  $this->mongo->store->$document->find(['date' => ['$gte' => $start, '$lte' => $end]]);
        $response = $this->client->request(
            'GET',
            "/order/get/$start/$end?key=" . $this->key
        );

        $data = $response->getBody();
        return
            json_decode($data, TRUE);
    }

    /**
     * function to flter data by date and status
     *
     * @param [type] $start
     * @param [type] $end
     * @param [type] $statusfilter
     * @return void
     */
    private function getDataByfilterDate($start, $end, $statusfilter)
    {
        // return
        //     $this->mongo->store->orders->find(['date' => ['$gte' => $start, '$lte' => $end], 'status' => $statusfilter]);
        $response = $this->client->request(
            'GET',
            "/order/get/$start/$end/$statusfilter?key=" . $this->key
        );

        $data = $response->getBody();
        return
            json_decode($data, TRUE);
    }



    /**
     * public functions for products
     */

    public function getAllProducts()
    {
        return
            $this->getAll('product');
    }

    public function getProduct($id)
    {
        return
            $this->getSingleProduct($id);
    }

    public function searchProductByName($name)
    {
        return
            $this->searchName($name);
    }

    public function addProduct($product)
    {
        $this->addData('product', $product);
    }

    public function updateProduct($data)
    {
        $this->updateData('product', $data);
    }

    public function deleteProducts($id)
    {
        $this->deleteProduct($id);
    }



    /**
     * public functions for orders
     */

    public function addOrder($data)
    {
        $this->addData('order', $data);
    }


    //
    public function getAllOrders()
    {
        return
            $this->getAll('order');
    }

    //
    public function updateOrderStatus($data)
    {
        $this->updateData('order', $data);
    }


    public function orderByDate($start, $end, $statusfilter)
    {
        if ($statusfilter == 'all') {

            return  $this->getDataByDate($start, $end);
        } else {

            return
                $this->getDataByfilterDate($start, $end, $statusfilter);
        }
    }


    /**
     * function to return login data
     *
     * @param [type] $name
     * @param [type] $password
     * @return void
     */
    public function checkUser($name, $password)
    {
        $result = $this->mongo->store->user->find([
            "username" => $name, "password" => $password
        ]);

        foreach ($result as $user => $details) {
            return $details;
        }
    }



    /**
     * function to check if user exists
     *
     * @param [type] $user
     * @param [type] $email
     * @return void
     */
    public function checkUserExists($user, $email)
    {
        $result = $this->mongo->store->user->find([
            '$or' => [
                ["username" => $user], ["email" => $email]
            ]
        ]);

        foreach ($result as $user => $details) {
            return true;
        }
    }


    /**
     * function to add user in db
     *
     * @param [type] $user
     * @param [type] $email
     * @param [type] $password
     * @return void
     */
    public function addUser($user,  $email, $password)
    {
        if ($this->checkUserExists($user, $email)) {
            return false;
        } else {
            $this->mongo->store->user->insertOne([
                "username" => $user, "email" => $email, "password" => $password, "role" => "user"
            ]);
            return true;
        }
    }



    /**
     * function making api request to get authorisation code
     *
     * @return void
     */
    public function getAuth()
    {
        $response = $this->client->request(
            'GET',
            "/user?user=" . $this->session->get('name') . "&email=" . $this->session->get('email')
        );

        $data = $response->getBody();
        return
            json_decode($data, TRUE);
    }


    /**
     * function making api request to get access token
     *
     * @param [type] $token
     * @return void
     */
    public function getAccess($token)
    {
        $response = $this->client->request(
            'GET',
            "/user/accesstoken?token=" . $token
        );

        $data = $response->getBody();
        $data = json_decode($data, TRUE);
        $this->mongo->store->user->updateOne(
            ['email' => $this->session->get('email')],
            ['$set' =>
            [
                'token' => $data['key']
            ]]
        );
        return
            $data;
    }


    /**
     * function to handle login logout and token availability check
     *
     * @return void
     */
    public function checkToken()
    {
        $login  = $this->session->get('login');
        if ($login) {
            $user =  $this->mongo->store->user->find(['email' => $this->session->get('email')]);
            foreach ($user as $users => $details) {
                if (isset($details->token)) {
                    $this->session->set('token', $details->token);
                } else {
                    $this->response->redirect('/user/connectapi');
                }
            }
        } else {
            $this->response->redirect('/user');
        }
    }
}
