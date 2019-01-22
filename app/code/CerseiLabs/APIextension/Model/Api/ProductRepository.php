<?php

namespace CerseiLabs\APIextension\Model\Api;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Webapi\Rest\Request;
use CerseiLabs\APIextension\Api\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var \CerseiLabs\APIextension\Helper\Data
     */
    protected $helper;

    /**
     * @var Request
     */
    protected $incomingRequest;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $metadataService;
    /**
     * @var \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    protected $stockItemRepository;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\Item
     */
    protected $stockItem;

    /**
     * ProductRepository constructor.
     *
     * @param ProductFactory $productFactory
     * @param CategoryFactory $categoryFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \CerseiLabs\APIextension\Helper\Data $helper
     * @param Request $incomingRequest
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataServiceInterface
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository
     */
    public function __construct(
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \CerseiLabs\APIextension\Helper\Data $helper,
        Request $incomingRequest,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataServiceInterface,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Magento\CatalogInventory\Model\Stock\Item $stockItem
    )
    {
        $this->om = ObjectManager::getInstance();
        $this->helper = $helper;
        $this->productFactory = $productFactory;
        $this->categoryFactory = $categoryFactory;
        $this->storeManager = $storeManager;
        $this->incomingRequest = $incomingRequest;
        $this->collectionFactory = $collectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->metadataService = $metadataServiceInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockItem = $stockItem;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryPaths($ids)
    {
        $this->helper->log('entered custom endpoint ' . $ids);

        if (strpos($ids, ',') !== false) {
            $productIds = explode(',', $ids);
        } else {
            $productIds = array($ids);
        }

        $categoriesWithPaths = array('results' => []);
        $counter = 0;
        foreach ($productIds as $productId) {
            $product = $this->productFactory->create()->load($productId);

            $categories = $product->getCategoryIds();
            if (count($categories)) {
                $paths = array();
                foreach ($categories as $categoryId) {
                    $_category = $this->categoryFactory->create()->load($categoryId);
                    $paths[] = $this->convertPathIdsToNames($_category->getPath());
                }
                $counter++;
                $categoriesWithPaths['results'][$productId] = $paths;
            }
        }

        $this->helper->log(json_encode($categoriesWithPaths));

        return $categoriesWithPaths;
    }

    /**
     * @param $path
     * @return string
     */
    public function convertPathIdsToNames($path)
    {
        $categoryIds = explode('/', $path);
        $namesPath = '';

        if (count($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                $_category = $this->categoryFactory->create()->load($categoryId);
                $namesPath .= $_category->getName() . '/';
            }
        }
        return substr($namesPath, 0, -1);
    }

    /**
     * @param $item
     * @param $categoryIds
     */
    public function assignCategories($item, $categoryIds)
    {
        $product = $this->productFactory->create()->load($item['id']);
        $product->setCategoryIds($categoryIds);
        $product->save();
    }

    /**
     * {@inheritdoc}
     */
    public function setCategoryPaths()
    {
        $data = $this->incomingRequest->getRequestData();

        $data = $data['data'];

        foreach ($data as $item) {
            $categoryIds = $this->getCategoryIds($item);
            $this->assignCategories($item, $categoryIds);

            $this->helper->log(111);
        }

        return true;
    }

    /**
     * @param $item
     *
     * @return array
     */
    public function getCategoryIds($item)
    {
        $paths = $item['paths'];
        $skip = (bool)$item['skip'];

        $extractedCategories = array();

        foreach ($paths as $path) {
            $ids = $this->getCategoryIdsByPath($path, $skip);
            $id = array_pop($ids);
            $extractedCategories[] = $id;
        }

        return $extractedCategories;
    }

    /**
     * @param $path
     * @param $skip
     *
     * @return array
     */
    public function getCategoryIdsByPath($path, $skip)
    {
        $names = explode('/', $path);

        $categoryIds = array();

        $previous = null;
        foreach ($names as $name) {
            $id = $this->getCategoryIdByName($name);

            if (!is_null($id)) {
                $categoryIds[] = $this->getCategoryIdByName($name);
            } else {
                if ($skip) {
                    continue;
                }
                $parentId = $this->getParentId($previous);
                $categoryIds[] = $this->createNewCategory($parentId, $name);
            }
            $previous = $name;
        }

        return $categoryIds;
    }

    /**
     * @param $name
     *
     * @return bool | int
     */
    public function getCategoryIdByName($name)
    {
        $collection = $this->categoryFactory->create()->getCollection()->addAttributeToFilter('name', $name)->setPageSize(1);

        return $collection->getFirstItem()->getId();
    }

    /**
     * @param $parentId
     * @param $name
     *
     * @return int
     */
    public function createNewCategory($parentId, $name)
    {
        return $this->createCategory($parentId, $name)->getId();
    }

    /**
     * @param $previous
     * @return mixed
     */
    public function getParentId($previous)
    {
        return $this->getCategoryIdByName($previous);
    }

    /**
     * @param $parentId
     * @param $name
     *
     * @return \Exception
     */
    public function createCategory($parentId, $name)
    {
        $_categoryDefaults = array(
            'is_anchor' => 1,
            'include_in_menu' => 1,
            'is_active' => 1,
            'display_mode' => 'PRODUCTS',
            'name' => $name
        );

        $category = $this->categoryFactory->create();
        $category->setStoreId($this->getStoreId());

        //set data for category
        foreach ($_categoryDefaults as $key => $value) {
            $category->setData($key, $value);
        }

        //skip if path is passed on
        if (!isset($attributes['path'])) {
            // always set parent Category Id
            if (!$parentId)
                $parentId = $this->storeManager->getStore()->getRootCategoryId();

            $parentCategory = $this->categoryFactory->create()->load($parentId);
            $category->setPath($parentCategory->getPath());
        }
        try {
            $newCat = $category->save();
        } catch (\Exception $e) {
            return $e;
        }

        return $newCat;
    }

    /**
     * Get id of the store that we should Limit categories to
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @param $stockItemId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($stockItemId)
    {
        return $this->stockItemRepository->get($stockItemId);
    }

    /**
     * @param int $productId
     *
     * @return \Magento\CatalogInventory\Model\Stock\Item
     */
    public function getStockItemByProductId($productId)
    {
        return $this->stockItem->load($productId, 'product_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection);

        foreach ($this->metadataService->getList($this->searchCriteriaBuilder->create())->getItems() as $metadata) {
            $collection->addAttributeToSelect($metadata->getAttributeCode());
        }
        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');

        //Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        /** @var SortOrder $sortOrder */
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $field = $sortOrder->getField();
            $collection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
            );
        }

        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->load();

        foreach ($collection->getItems() as $item) {
            $item->load('media_gallery');
        }

        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param Collection $collection
     *
     * @return void
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        Collection $collection
    )
    {
        $fields = [];
        $categoryFilter = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $conditionType = $filter->getConditionType() ? $filter->getConditionType() : 'eq';

            if ($filter->getField() == 'category_id') {
                $categoryFilter[$conditionType][] = $filter->getValue();
                continue;
            }
            $fields[] = ['attribute' => $filter->getField(), $conditionType => $filter->getValue()];
        }

        if ($categoryFilter) {
            $collection->addCategoriesFilter($categoryFilter);
        }

        if ($fields) {
            $collection->addFieldToFilter($fields);
        }
    }
}
