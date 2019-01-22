<?php

namespace CerseiLabs\APIextension\Api;

use Magento\Framework\Api\ExtensibleDataInterface;

interface ProductRepositoryInterface extends ExtensibleDataInterface
{
    /**
     * Retrieves category paths
     *
     * @api
     *
     * @param string $ids
     * @return array
     */
    public function getCategoryPaths($ids);

    /**
     * Assign category paths to product batch
     * Create new categories if non-existent
     *
     * @api
     * @return boolean
     */
    public function setCategoryPaths();

    /**
     * Get product list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

}
