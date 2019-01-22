<?php

namespace CerseiLabs\APIextension\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface WebhookSearchResultInterface
 *
 * @package CerseiLabs\APIextension\Api\Data
 */
interface WebhookSearchResultInterface extends SearchResultsInterface
{
    /**
     * @return \CerseiLabs\APIextension\Api\Data\WebhookSearchResultInterface[]
     */
    public function getItems();

    /**
     * @param \CerseiLabs\APIextension\Api\Data\WebhookSearchResultInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
