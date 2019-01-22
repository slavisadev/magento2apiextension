<?php

namespace CerseiLabs\APIextension\Api\Data;

/**
 * Interface WebhookInterface
 * DTO interface [Data Transfer Object]
 *
 * @package CerseiLabs\APIextension\Api\Data
 */
interface WebhookInterface
{
    /**
     * @api
     * @param int $id
     * @return void
     */
    public function setId($id);

    /**
     * @api
     * @return int
     */
    public function getId();

    /**
     * @api
     * @param $data
     * @return void
     */
    public function setData($data);

    /**
     * @api
     * @param string $eventCode
     * @return void
     */
    public function setEventCode($eventCode);

    /**
     * @api
     * @return string
     */
    public function getEventCode();

    /**
     * @api
     * @param string $description
     * @return void
     */
    public function setDescription($description);

    /**
     * @api
     * @return string
     */
    public function getDescription();

    /**
     * @api
     * @param string $token
     * @return void
     */
    public function setToken($token);

    /**
     * @api
     * @return string
     */
    public function getToken();

    /**
     * @api
     * @param string $callbackUrl
     * @return void
     */
    public function setCallbackUrl($callbackUrl);

    /**
     * @api
     * @return string
     */
    public function getCallbackUrl();

    /**
     * @api
     * @param bool $active
     * @return void
     */
    public function setActive($active);

    /**
     * @api
     * @return bool
     */
    public function getActive();
}
