<?php

namespace CerseiLabs\APIextension\Model\ResourceModel\Webhook;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @package CerseiLabs\APIextension\Model\ResourceModel\Webhook
 */
class Collection extends AbstractCollection
{
    public function _construct()
    {
        /**
         * Define model & resource model
         */
        $this->_init(
            'CerseiLabs\APIextension\Model\Webhook',
            'CerseiLabs\APIextension\Model\ResourceModel\Webhook'
        );
    }
}
