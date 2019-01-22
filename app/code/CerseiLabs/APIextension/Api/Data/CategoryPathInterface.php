<?php

namespace CerseiLabs\APIextension\Api\Data;

interface CategoryPathInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */

    /**
     * @api
     * @return array
     */
    public function getData();

    /**
     * @api
     * @param array $data
     * @return null
     */
    public function setData($data);
}
