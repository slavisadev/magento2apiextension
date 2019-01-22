<?php

namespace CerseiLabs\APIextension\Model\Api;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Webapi\Request;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreRepository;
use CerseiLabs\APIextension\Api\Data\ResponseInterfaceFactory;
use CerseiLabs\APIextension\Api\CerseiLabsItemInterface;
use CerseiLabs\APIextension\Helper\Data as CerseiLabsHelper;

class CerseiLabsItem extends AbstractCerseiLabs implements CerseiLabsItemInterface
{
    private $productFactory;

    protected $_categoryCollectionFactory;

    protected $_categoryHelper;

    protected $eavOptionManagement;

    private $storeManager;

    /**
     * @var \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CategorySearchResultInterfaceFactory
     */
    private $searchResultFactory;
    /**
     * @var ObjectManager
     */
    private $om;

    protected $helper;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $metadataService;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var
     */
    protected $storeRepository;

    /**
     * CerseiLabsItem constructor.
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $config
     * @param Request $request
     * @param ProductFactory $productFactory
     * @param AttributeOptionManagementInterface $eavOptionManagement
     * @param CerseiLabsHelper $helper
     * @param ResponseInterfaceFactory $responseFactory
     * @param \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataServiceInterface
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreRepository $storeRepository
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $attrCollection
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $config,
        Request $request,
        ProductFactory $productFactory,
        AttributeOptionManagementInterface $eavOptionManagement,
        CerseiLabsHelper $helper,
        ResponseInterfaceFactory $responseFactory,
        \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataServiceInterface,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreRepository $storeRepository
    )
    {
        parent::__construct($responseFactory);
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->request = $request;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->productFactory = $productFactory;
        $this->eavOptionManagement = $eavOptionManagement;
        $this->om = ObjectManager::getInstance();
        $this->helper = $helper;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->metadataService = $metadataServiceInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Retrieves order statuses for order ID
     *
     * @param int $orderId
     *
     * @return array
     */
    public function getOrderStatuses($orderId)
    {
        /** @var \Magento\Sales\Model\Order $orderObject */
        $orderObjectModel = $this->om->create('Magento\Sales\Model\Order');
        $orderObject = $orderObjectModel->load($orderId);

        return $this->generateCerseiLabsResponse([$orderObject->getStatus()]);
    }

    /**
     * Retrieves categories with children
     *
     * @api
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getCategories(SearchCriteriaInterface $searchCriteria)
    {

        if ($this->hasFilters($searchCriteria)) {

            $collection = $this->om->create('Magento\Catalog\Model\ResourceModel\Category\Collection');

            $this->addFiltersToCollection($searchCriteria, $collection);
            $this->addSortOrdersToCollection($searchCriteria, $collection);
            $this->addPagingToCollection($searchCriteria, $collection);

            $categories = $collection->load();
        } else {
            $categoryFactory = $this->om->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
            //categories from current store will be fetched
            $categories = $categoryFactory->create()->addAttributeToSelect('*')->setStore($this->storeManager->getStore());
        }

        $childCategory = [];

        foreach ($categories as $childItem) {

            $childCategory[] = [
                'id' => $childItem->getId(),
                'parent_id' => $childItem->getParentId(),
                'name' => $childItem->getName(),
                'is_active' => $this->convertNullToZero($childItem->getIsEnabled()),
                'is_anchor' => $this->convertNullToZero($childItem->getIsAnchor()),
                'position' => $childItem->getPosition(),
                'level' => $childItem->getLevel(),
                'product_count' => $childItem->getProductCount(),
                'created_at' => $childItem->getCreatedAt(),
                'updated_at' => $childItem->getUpdatedAt(),
                'url_path' => $childItem->getUrlPath(),
                'include_in_menu' => $childItem->getIncludeInMenu(),
                'description' => $childItem->getDescription(),
            ];
        }
        return $this->generateCerseiLabsResponse($childCategory);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return bool
     */
    public function hasFilters(SearchCriteriaInterface $searchCriteria)
    {
        return count($searchCriteria->getFilterGroups()) > 0;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param \Magento\Catalog\Model\ResourceModel\Category\Collection $collection
     */
    private function addFiltersToCollection(SearchCriteriaInterface $searchCriteria, $collection)
    {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $fields[] = $filter->getField();
                $conditions[] = [$filter->getConditionType() => $filter->getValue()];
            }

            if (count($fields) == 1)
                $fields = $fields[0];

            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param $collection
     */
    private function addSortOrdersToCollection(SearchCriteriaInterface $searchCriteria, $collection)
    {
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $direction = $sortOrder->getDirection() == SortOrder::SORT_ASC ? 'asc' : 'desc';
            $collection->addOrder($sortOrder->getField(), $direction);
        }
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param $collection
     */
    private function addPagingToCollection(SearchCriteriaInterface $searchCriteria, $collection)
    {
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param $collection
     *
     * @return mixed
     */
    private function buildSearchResult(SearchCriteriaInterface $searchCriteria, $collection)
    {
        $searchResults = $this->searchResultFactory->create();

        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @param $item
     *
     * @return int
     */
    public function convertNullToZero($item)
    {
        if (is_null($item)) {
            return 0;
        }
        return $item;
    }

    /**
     * Recursively
     *
     * @param $item
     *
     * @return array
     */
    public function createChildrenArrayRecursively($item)
    {
        $childCategory = array();

        /** @var \Magento\Catalog\Api\Data\CategoryTreeInterface $item */
        foreach ($item->getChildrenData() as $childItem) {
            $childCategory[] = [
                'id' => $childItem->getId(),
                'parent_id' => $childItem->getParentId(),
                'name' => $childItem->getName(),
                'is_active' => $childItem->getIsActive(),
                'is_anchor' => $childItem->getIsAnchor(),
                'position' => $childItem->getPosition(),
                'level' => $childItem->getLevel(),
                'product_count' => $childItem->getProductCount(),
                'created_at' => $childItem->getCreatedAt(),
                'updated_at' => $childItem->getUpdatedAt(),
                'url_path' => $childItem->getUrlPath(),
                'include_in_menu' => $childItem->getIncludeInMenu(),
                'description' => $childItem->getDescription(),
                'children_data' => $this->createChildrenArrayRecursively($childItem),
            ];
        }

        return $childCategory;
    }

    /**
     * @param $price
     * @param $vat
     * @param $taxIncluded
     * @return string
     */
    protected function calculateNetPrice($price, $vat, $taxIncluded)
    {
        return number_format($taxIncluded ? $price / (1 + $vat * 0.01) : $price, 2);
    }

    /**
     * @param $price
     * @param $vat
     * @param bool $taxIncluded
     * @return string
     */
    protected function calculatePrice($price, $vat, $taxIncluded = true)
    {
        return number_format($taxIncluded ? $price : $price * (1 + $vat * 0.01), 2);
    }

    /**
     * Retrieves parent product id if product is configurable
     * @param Product $product
     * @return mixed
     */
    protected function getParentProductUrl($product)
    {
        $parents = $this->om->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')
            ->getParentIdsByChild($product->getId());
        if (!empty($parents)) {
            /** @var Product $parent */
            $parent = $this->om->get('Magento\Catalog\Model\Product')->load($parents[0]);

            return $parent->getUrlModel()->getProductUrl($parent);
        }

        return $product->getUrlModel()->getProductUrl($product);
    }

    /**
     * Retrieve all product options grouped by product attributes for all attribute sets
     *
     * @return array
     */
    public function getAllOptions()
    {
        $attributeSets = $this->om->create(\Magento\Catalog\Model\Product\AttributeSet\Options::class);
        $excludedAttributes = $this->getExcludedAttributes();

        $result = array();
        $attrSetData = array();
        foreach ($attributeSets->toOptionArray() as $attributeSet) {

            $attrSetData[$attributeSet['value']] = $attributeSet['label'];

            $attributeSetId = $attributeSet['value'];
            /** @var \Magento\Catalog\Model\Product\Attribute\Management $configHelper */
            $configHelper = $this->om->create(\Magento\Catalog\Model\Product\Attribute\Management::class);
            $attributes = $configHelper->getAttributes($attributeSetId);

            foreach ($attributes as $key => $attributeData) {

                //check if item should be added to array
                if (array_key_exists((int)$attributeData->getAttributeId(), $result) || in_array($attributeData->getName(), $excludedAttributes)) {
                    continue;
                }

                $_options = array();

                //obtain all options
                $options = $this->getItems($attributeData->getName());
                foreach ($options as $optionInstance) {
                    if ($optionInstance->getLabel() && $optionInstance->getValue() && !is_array($optionInstance->getValue())) {
                        $_options[$optionInstance->getValue()] = $optionInstance->getLabel();
                    }
                }

                if (!empty($options)) {
                    $result[(int)$attributeData->getAttributeId()] = array(
                        'ID' => (int)$attributeData->getAttributeId(),
                        'attribute_code' => $attributeData->getName(),
                        'attribute_type' => $attributeData->getFrontendInput(),
                        'options' => $_options,
                        'required' => $attributeData->getIsRequired()
                    );
                }
            }
        }

        $result[] = array(
            'attribute_code' => 'attribute_set_id',
            'attribute_type' => 'select',
            'options' => $attrSetData,
            'required' => 1
        );

        $result[] = array(
            'attribute_code' => 'store_id',
            'attribute_type' => 'select',
            'options' => $this->getStores(),
            'required' => 1
        );

        return $result;
    }

    /**
     * @TODO implement getApplyTo check
     * @return array
     */
    public function getExcludedAttributes()
    {
        return array(
            'price_view',
            'shipment_type'
        );
    }

    /**
     * Get list of store views
     *
     * @return array
     */
    public function getStores()
    {
        $stores = $this->storeRepository->getList();
        $websiteIds = array();
        $storeList = array();

        foreach ($stores as $store) {
            $websiteId = $store["website_id"];
            $storeId = $store["store_id"];
            $storeName = $store["name"];
            $storeList[$storeId] = $storeName;
            array_push($websiteIds, $websiteId);
        }

        return $storeList;
    }

    /**
     * @param $attributeCode
     * @return \Magento\Eav\Api\Data\AttributeOptionInterface[]
     */
    public function getItems($attributeCode)
    {
        return $this->eavOptionManagement->getItems(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode
        );
    }

    /**
     * @param $array
     * @param $keySearch
     *
     * @return bool
     */
    function keyExists($array, $keySearch)
    {
        foreach ($array as $key => $item) {
            if ($key == $keySearch) {
                return true;
            } else {
                if (is_array($item) && $this->keyExists($item, $keySearch)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Retrieves products with stock, media and category subentities
     *
     * @api
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getProducts(SearchCriteriaInterface $searchCriteria)
    {
        $collectionFactory = $this->om->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $collectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection);

        foreach ($this->metadataService->getList($this->searchCriteriaBuilder->create())->getItems() as $metadata) {
            $collection->addAttributeToSelect($metadata->getAttributeCode());
        }
        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');

        $this->collectionProcessor->process($searchCriteria, $collection);

        $collection->load();

        $collection->addCategoryIds();
        $searchResult = $this->searchResultsFactory->create();

        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
        //[$searchResult->getItems()];
    }
}
