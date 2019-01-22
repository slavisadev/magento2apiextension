<?php

namespace CerseiLabs\APIextension\Model;

use Magento\Framework\Model\AbstractModel;
use CerseiLabs\APIextension\Api\Data\WebhookInterface;

/**
 * Class Webhook
 *
 * @package CerseiLabs\APIextension\Model
 */
class Webhook extends AbstractModel implements WebhookInterface
{

    /**
     * Webhook constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_init(ResourceModel\Webhook::class);
    }

    /**
     * @var
     */
    protected $description;

    /**
     * @var
     */
    protected $token;

    /**
     * @var
     */
    protected $active;

    /**
     * @var
     */
    protected $eventCode;

    /**
     * @var
     */
    protected $callbackUrl;

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return string
     */
    public function getEventCode()
    {
        return $this->eventCode;
    }

    /**
     * @param string $eventCode
     */
    public function setEventCode($eventCode)
    {
        $this->eventCode = $eventCode;
    }

    /**
     * @return string
     */
    public function getCallbackUrl()
    {
        return $this->callbackUrl;
    }

    /**
     * @param string $callbackUrl
     */
    public function setCallbackUrl($callbackUrl)
    {
        $this->callbackUrl = $callbackUrl;
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('CerseiLabs\APIextension\Model\ResourceModel\Webhook');
    }
}
