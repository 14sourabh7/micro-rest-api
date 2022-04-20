<?php

namespace App\Db;

use Phalcon\Di\Injectable;

class MongoHelper extends Injectable
{

    /**
     * search($key, $keyword)
     * 
     * function to search 
     *
     * @param [type] $key
     * @param [type] $keyword
     * @return json
     */
    public function search($keyword)
    {
        $keywords = [];

        foreach ($keyword as $key => $value) {
            array_push($keywords, array('name' => ['$regex' => $value]));
            array_push($keywords, array('variations' => [
                '$elemMatch' => [
                    "name" => [
                        '$regex' => $value
                    ]
                ]
            ]));
        }

        $result =  $this->mongo->store->products->find(['$or' => $keywords]);

        return $this->setResponse($result);
    }


    /**
     * getAll($key)
     * 
     * function returning all the products
     *
     * @param [type] $key
     * @return json
     */
    public function getAll()
    {
        $result = $this->mongo->store->products->find();
        return $this->setResponse($result);
    }

    /**
     * get($key, $per_page, $page, $select, $filter)
     * 
     * function returning products based on conditions
     *
     * @param [type] $key
     * @param [type] $per_page
     * @param [type] $page
     * @param [type] $select
     * @param [type] $filter
     * @return json
     */
    public function get($per_page, $page, $select, $filter)
    {

        $columns = ["_id" => 0];

        foreach (explode(" ", urldecode($select)) as $column => $value) {
            $columns[$value] = 1;
        }


        $count =  $this->mongo->store->products->find(['name' => ['$regex' => $filter]]);
        $count = count(iterator_to_array($count));
        $pages = $count / $per_page;
        if ($pages < $page) {
            return (["info" => "page limit exceeded available data:$count"]);
        } else if ($page == 0) {
            return (["info" => "page number starts from 1"]);
        } else {
            $result = $this->mongo->store->products->find(['name' => ['$regex' => $filter]], [
                "skip" => (int)$page == 1 ? 0 : ((int) $page) - 1,
                "limit" => (int) $per_page,
                "projection" => $columns
            ]);
            $result =  $this->setResponse($result);
            if ($page == $pages) {
                $result['next_page'] = false;
            } else {
                $result['next_page'] = true;
            }
            return $result;
        }
        // die;

    }





    /**
     * setResponse($result)
     * 
     * function preparing json response
     *
     * @param [type] $result
     * @return json
     */
    public function setResponse($result)
    {
        $response = [];

        foreach ($result as $k => $v) {
            $response[$k] = iterator_to_array($v);

            if (isset($response[$k]["_id"]))
                $response[$k]["_id"] = (array) $v->_id;

            if (isset($v->additional)) {

                $response[$k]['additional'] = iterator_to_array($v->additional);
            }
            if (isset($v->variation)) {
                $response[$k]['variation'] = iterator_to_array($v->variation);
                foreach ($v->variation as $key => $value) {
                    $response[$k]['variation'][$key] = iterator_to_array($value);
                }
            }
        }

        if (count($response) < 1) {
            $response = array("info" => "no data found for the given conditions");
        }
        return $response;
    }
}
