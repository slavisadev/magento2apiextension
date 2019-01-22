<?php

namespace CerseiLabs\APIextension\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use CerseiLabs\APIextension\Helper\Data as CerseiLabsHelper;
use CerseiLabs\APIextension\Model\ResourceModel\Webhook\Collection as WebhooksCollection;

class WebhookEventsOrderCancel implements ObserverInterface
{
    protected $_collectionFactory;

    protected $eventCode;

    protected $helper;

    public function __construct(WebhooksCollection $collectionFactory, CerseiLabsHelper $helper)
    {
        $this->_collectionFactory = $collectionFactory;
        $this->eventCode = 'order_cancel_after';
        $this->helper = $helper;
    }

    public function execute(Observer $observer)
    {
        $this->helper->runWebHook($this->_collectionFactory, $this->eventCode, self::class);
    }
}
