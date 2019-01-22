<?php

namespace CerseiLabs\APIextension\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Webhook
 *
 * @package CerseiLabs\APIextension\Model\ResourceModel
 */
class Webhook extends AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        #apiextension_webhook is table of module
        $this->_init('apiextension_webhook', 'id');
    }
}
