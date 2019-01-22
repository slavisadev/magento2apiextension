<?php

namespace CerseiLabs\APIextension\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use CerseiLabs\APIextension\Api\ReindexRepositoryInterface;

class ReindexRepository implements ReindexRepositoryInterface
{

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var \CerseiLabs\APIextension\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Indexer\IndexerInterfaceFactory
     */
    protected $indexerFactory;

    protected $process;

    public function __construct(
        \Magento\Indexer\Model\Processor $process,
        \CerseiLabs\APIextension\Helper\Data $helper,
        \Magento\Framework\Indexer\IndexerInterfaceFactory $indexerFactory
    )
    {
        $this->om = ObjectManager::getInstance();
        $this->helper = $helper;
        $this->indexerFactory = $indexerFactory;
        $this->process = $process;
    }

    /**
     * @param string $indexerId
     * @return \Magento\Framework\Indexer\IndexerInterface
     */
    public function getIndexer($indexerId)
    {
        return $this->indexerFactory->create()->load($indexerId);
    }

    /**
     * @api
     *
     * @return \CerseiLabs\APIextension\Api\Data\ResponseInterface
     */
    public function execute()
    {
        $this->helper->log('execute reindex');
        $this->process->reindexAll();
    }
}
