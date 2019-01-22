<?php

namespace CerseiLabs\APIextension\Api;

use Magento\Framework\Api\ExtensibleDataInterface;

interface StockItemRepositoryInterface extends ExtensibleDataInterface
{
    const ITEM_ID = 'item_id';
    const QTY = 'qty';

    /**
     * @return int
     */
    public function getItemId();

    /**
     * @param $id
     * @return mixed
     */
    public function setItemId($id);

    /**
     * @return int
     */
    public function getQty();

    /**
     * @param $qty
     * @return mixed
     */
    public function setQty($qty);
}
