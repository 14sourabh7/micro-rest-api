<?php
//db queries
namespace App\Components;

use Phalcon\Di\Injectable;

class MongoHelper extends Injectable
{

    /**
     * createID($id)
     * 
     * function to create  \MongoDB\BSON\ObjectID($id)
     *
     * @param [type] $id
     * @return void
     */
    private function createID($id)
    {
        return new \MongoDB\BSON\ObjectID($id);
    }

    /**
     * getAll($document)
     * 
     * class function to find all from document
     *
     * @param [type] $document
     * @return void
     */
    private function getAll($document)
    {
        return
            $this->mongo->store->$document->find();
    }

    /**
     * getSingle($document, $id)
     * 
     * class function to find a single product or order
     *
     * @param [type] $document
     * @param [type] $id
     * @return object
     */
    private function getSingle($document, $id)
    {
        return
            $this->mongo->store->$document->findOne([
                '_id' => $this->createID($id)
            ]);
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
    private function searchName($document, $name)
    {
        return
            $this->mongo->store->$document->find(['name' => $name]);
    }

    /**
     * addData($data)
     * 
     * class function to add data 
     *
     * @param [type] $data
     * @return void
     */
    private function addData($document, $data)
    {
        $this->mongo->store->$document->insertOne($data);
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
    private function updateData($document, $data)
    {
        $this->mongo->store->$document->updateOne(
            [

                '_id' => $this->createID($data['id'])
            ],
            [
                '$set' => $data
            ]
        );
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
    private function deleteData($document, $id)
    {
        $this->mongo->store->$document->deleteOne(
            [
                '_id' => $this->createID($id)
            ]
        );
    }


    /**
     * function to filter data by date only
     *
     * @param [type] $document
     * @param [type] $start
     * @param [type] $end
     * @return void
     */
    private function getDataByDate($document, $start, $end)
    {
        return  $this->mongo->store->$document->find(['date' => ['$gte' => $start, '$lte' => $end]]);
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
        return
            $this->mongo->store->orders->find(['date' => ['$gte' => $start, '$lte' => $end], 'status' => $statusfilter]);
    }



    /**
     * public functions for products
     */

    public function getAllProducts()
    {
        return
            $this->getAll('products');
    }

    public function getProduct($id)
    {
        return
            $this->getSingle('products', $id);
    }

    public function searchProductByName($name)
    {
        return
            $this->searchName('products', $name);
    }

    public function addProduct($product)
    {
        $this->addData('products', $product);
    }

    public function updateProduct($data)
    {
        $this->updateData('products', $data);
    }

    public function deleteProduct($id)
    {
        $this->deleteData('products', $id);
    }



    /**
     * public functions for orders
     */

    public function addOrder($data)
    {
        $this->addData('orders', $data);
        $quantity = $data['quantity'];
        $stock = $this->getSingle('products', $data['product_id'])->stock - $quantity;
        $this->updateData('products', ['id' => $data['product_id'], 'stock' => $stock]);
    }
    public function searchOrderByName($name)
    {
        return $this->searchName('orders', $name);
    }
    public function getAllOrders()
    {
        return
            $this->getAll('orders');
    }
    public function updateOrderStatus($data)
    {
        $this->updateData('orders', $data);
    }

    public function orderByDate($start, $end, $statusfilter)
    {
        if ($statusfilter == 'all') {

            return  $this->getDataByDate('orders', $start, $end);
        } else {

            return
                $this->getDataByfilterDate($start, $end, $statusfilter);
        }
    }

    public function addUpdatedProduct($data)
    {
        $this->mongo->frontend->products->UpdateOne(['_id' => $this->createID($data['id'])], ['$set' => $data]);
    }
}
