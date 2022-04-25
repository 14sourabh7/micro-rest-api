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
            $this->mongo->store->$document->find(['name' => ['$regex' => $name]]);
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
}
