<?php

namespace CerseiLabs\APIextension\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use CerseiLabs\APIextension\Helper\Data as CerseiLabsHelper;
use CerseiLabs\APIextension\Model\ResourceModel\Webhook\Collection as WebhooksCollection;

class WebhookEventsCustomer implements ObserverInterface
{
    /**
     * @var WebhooksCollection
     */
    protected $_collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var string
     */
    protected $eventCode;

    /**
     * @var CerseiLabsHelper
     */
    protected $helper;

    /**
     * WebhookEventsCategory constructor.
     * @param WebhooksCollection $collectionFactory
     * @param CerseiLabsHelper $helper
     * @param StoreManagerInterface $_storeManager
     */
    public function __construct(
        WebhooksCollection $collectionFactory,
        CerseiLabsHelper $helper,
        StoreManagerInterface $_storeManager)
    {
        $this->_collectionFactory = $collectionFactory;
        $this->eventCode = 'customer_save_after_data_object';
        $this->helper = $helper;
        $this->_storeManager = $_storeManager;
    }

    public function execute(Observer $observer)
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $subEntities = array();
        $this->helper->runWebHook($baseUrl, $this->_collectionFactory, $this->eventCode, 'customer', $observer, $subEntities);
    }
}
