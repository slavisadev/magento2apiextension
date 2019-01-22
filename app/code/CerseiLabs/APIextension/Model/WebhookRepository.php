<?php
namespace CerseiLabs\APIextension\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use CerseiLabs\APIextension\Api\Data\WebhookInterface;
use CerseiLabs\APIextension\Api\Data\WebhookInterfaceFactory;
use CerseiLabs\APIextension\Api\Data\WebhookSearchResultInterfaceFactory;
use CerseiLabs\APIextension\Api\WebhookRepositoryInterface;
use CerseiLabs\APIextension\Helper\Data as CerseiLabsHelper;
use CerseiLabs\APIextension\Model\ResourceModel\Webhook\Collection;
use CerseiLabs\APIextension\Model\ResourceModel\Webhook\Collection as WebhookCollectionFactory;

/**
 * Class CerseiLabsWebhook
 *
 * @package CerseiLabs\APIextension\Model\Api
 */
class WebhookRepository implements WebhookRepositoryInterface
{
    /**
     * @var Webhook
     */
    private $webhookFactory;

    /**
     * @var \CerseiLabs\APIextension\Model\ResourceModel\Webhook
     */
    protected $webhookResource;

    /**
     * @var WebhookCollectionFactory
     */
    private $webhookCollectionFactory;

    /**
     * @var WebhookSearchResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var CerseiLabsHelper
     */
    private $helper;

    /**
     * WebhookRepository constructor.
     *
     * @param Webhook $webhookFactory
     * @param WebhookCollectionFactory $webhookCollectionFactory
     * @param WebhookSearchResultInterfaceFactory $webhookSearchResultInterfaceFactory
     * @param ResourceModel\Webhook $webhookResource
     */
    public function __construct(
        Webhook $webhookFactory,
        WebhookCollectionFactory $webhookCollectionFactory,
        WebhookSearchResultInterfaceFactory $webhookSearchResultInterfaceFactory,
        \CerseiLabs\APIextension\Model\ResourceModel\Webhook $webhookResource,
        CerseiLabsHelper $helper
    )
    {
        $this->webhookFactory = $webhookFactory;
        $this->webhookCollectionFactory = $webhookCollectionFactory;
        $this->searchResultFactory = $webhookSearchResultInterfaceFactory;
        $this->webhookResource = $webhookResource;
        $this->om = ObjectManager::getInstance();
        $this->helper = $helper;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->om->create('CerseiLabs\APIextension\Model\ResourceModel\Webhook\Collection');

        $this->addFiltersToCollection($searchCriteria, $collection);
        $this->addSortOrdersToCollection($searchCriteria, $collection);
        $this->addPagingToCollection($searchCriteria, $collection);

        $collection->load();

        return $this->buildSearchResult($searchCriteria, $collection);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param WebhookCollectionFactory $collection
     */
    private function addFiltersToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $fields[] = $filter->getField();
                $conditions[] = [$filter->getConditionType() => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param WebhookCollectionFactory $collection
     */
    private function addSortOrdersToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $direction = $sortOrder->getDirection() == SortOrder::SORT_ASC ? 'asc' : 'desc';
            $collection->addOrder($sortOrder->getField(), $direction);
        }
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param WebhookCollectionFactory $collection
     */
    private function addPagingToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param WebhookCollectionFactory $collection
     *
     * @return mixed
     */
    private function buildSearchResult(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $searchResults = $this->searchResultFactory->create();

        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @param int $id
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $webhook = $this->om->create('CerseiLabs\APIextension\Model\Webhook');
        $webhook->getResource()->load($webhook, $id);

        if (!$webhook->getId()) {
            throw new NoSuchEntityException(__('Unable to find webhook with ID "%1"', $id));
        }

        return array(
            $webhook->getData()
        );
    }

    /**
     * @param int $id
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getModelById($id)
    {
        $webhook = $this->om->create('CerseiLabs\APIextension\Model\Webhook');
        $webhook->getResource()->load($webhook, $id);

        if (!$webhook->getId()) {
            throw new NoSuchEntityException(__('Unable to find webhook with ID "%1"', $id));
        }

        return $webhook;
    }

    /**
     * Save webhook entity [POST] and [PUT] methods covered
     *
     * @param \CerseiLabs\APIextension\Api\Data\WebhookInterface $webhook
     *
     * @return array | string
     */
    public function save(WebhookInterface $webhook)
    {
        /** var $model CerseiLabs\APIextension\Model\Webhook */
        $model = $this->om->create('CerseiLabs\APIextension\Model\Webhook');

        $data = array(
            'event_code' => $webhook->getEventCode(),
            'active' => $webhook->getActive(),
            'callback_url' => $webhook->getCallbackUrl(),
            'description' => $webhook->getDescription(),
            'token' => $webhook->getToken(),
        );

        $model->setData($data);

        try {
            $model->save();
            $webhook->setId($model->getId());

            $webhook->getResource()->load($webhook, $model->getId());

            return array(
                $webhook->getData()
            );

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param int $id
     */
    public function delete($id)
    {
        $webhook = $this->getModelById($id);
        $this->webhookResource->delete($webhook);
    }

}
