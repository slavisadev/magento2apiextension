<?php

namespace CerseiLabs\APIextension\Model\Api;

use CerseiLabs\APIextension\Api\Data\CategoryPathInterface;

class CategoryPath implements CategoryPathInterface
{

//    /** @var $skip boolean */
//    private $skip;
//
//    /** @var $id string */
//    private $id;
//
//    /** @var $paths array */
//    private $paths;

    /** @var $data array */
    private $data;
//
//    /**
//     * @return bool
//     */
//    public function isSkip()
//    {
//        return $this->skip;
//    }
//
//    /**
//     * @param $id
//     */
//    public function setId($id)
//    {
//        $this->id = $id;
//    }
//
//    /**
//     * @return string
//     */
//    public function getId()
//    {
//        return $this->id;
//    }
//
//    /**
//     * @param $paths
//     */
//    public function setPaths($paths)
//    {
//        $this->paths = $paths;
//    }
//
//    /**
//     * @return array
//     */
//    public function getPaths()
//    {
//        return $this->paths;
//    }

    /**
     * @api
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @api
     * @param mixed $data
     * @return null
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}
