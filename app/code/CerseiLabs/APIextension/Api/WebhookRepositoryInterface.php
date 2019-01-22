<?php

namespace CerseiLabs\APIextension\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use CerseiLabs\APIextension\Api\Data\WebhookInterface;

interface WebhookRepositoryInterface
{
    /**
     * @param int $id
     *
     * @api
     *
     * @return \CerseiLabs\APIextension\Api\Data\ResponseInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * @api
     *
     * @param \CerseiLabs\APIextension\Api\Data\WebhookInterface $webhook
     *
     * @return \CerseiLabs\APIextension\Api\Data\WebhookInterface
     */
    public function save(WebhookInterface $webhook);

    /**
     * Delete webhook by identifier test
     *
     * @api
     *
     * @param int $id
     *
     * @return void
     */
    public function delete($id);

    /**
     * Retrieves webhooks
     *
     * @api
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return \CerseiLabs\APIextension\Api\Data\WebhookSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
