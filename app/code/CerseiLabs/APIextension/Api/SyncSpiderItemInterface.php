<?php

namespace CerseiLabs\APIextension\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface CerseiLabsItemInterface
{
    /**
     * Retrieves categories
     *
     * @api
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \CerseiLabs\APIextension\Api\Data\ResponseInterface
     */
    public function getCategories(SearchCriteriaInterface $searchCriteria);

    /**
     * Retrieves order statuses by order ID
     *
     * @api
     * @param int $orderId
     * @return mixed
     */
    public function getOrderStatuses($orderId);

    /**
     * Retrieves all options by all attributes
     *
     * @api
     *
     * @return mixed
     */
    public function getAllOptions();
}
