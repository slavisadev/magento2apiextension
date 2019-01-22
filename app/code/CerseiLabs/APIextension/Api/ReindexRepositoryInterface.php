<?php

namespace CerseiLabs\APIextension\Api;

/**
 * Interface CustomersInterface
 *
 * @package CerseiLabs\APIextension\Api
 */
interface ReindexRepositoryInterface
{
    /**
     * @api
     *
     * @return \CerseiLabs\APIextension\Api\Data\ResponseInterface
     */
    public function execute();
}
