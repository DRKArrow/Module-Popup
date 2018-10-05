<?php
namespace Tigren\Popup\Block\Carousel;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Url\Helper\Data;
use Tigren\Popup\Helper\Carousel;

class OwlCarousel extends \Magento\Catalog\Block\Product\ListProduct {
    protected $_newProductsNumber;

    protected $_bestSellersNumber;

    protected $_productCollectionFactory;

    protected $_resourceConnection;

    protected $_collection;

    protected $_carouselHelper;
    public function __construct(Context $context, PostHelper $postDataHelper, Resolver $layerResolver, CategoryRepositoryInterface $categoryRepository, Data $urlHelper,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
                                \Magento\Framework\App\ResourceConnection $resourceConnection,
                                \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
                                Carousel $carouselHelper,
                                array $data = [])
    {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_resourceConnection = $resourceConnection;
        $this->_collection = $collection;
        $this->_carouselHelper = $carouselHelper;
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }

    protected function _limitNewProducts()
    {
        $this->_newProductsNumber = $this->_carouselHelper->getConfigurationConfig('new_product_conf');
        if($this->_newProductsNumber === null)
            $this->_newProductsNumber = 12;
        return $this->_newProductsNumber;
    }

    protected function _limitBestSellers()
    {
        $this->_bestSellersNumber = $this->_carouselHelper->getConfigurationConfig('best_sellers_conf');
        if($this->_bestSellersNumber === null)
            $this->_bestSellersNumber = 12;
        return $this->_bestSellersNumber;
    }

    protected function _getNewProducts()
    {
        $limit = $this->_limitNewProducts();
        $today = date('Y-m-d');
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter(
            'news_from_date',
            [
                'or' => [
                    0 => ['date' => true, 'to' => $today],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ])->addAttributeToFilter(
            'news_to_date',
            [
                'or' => [
                    0 => ['date' => true, 'from' => $today],
                    1 => ['is' => new \Zend_Db_Expr('null')]
                ]
            ])
            ->addAttributeToFilter(
            [
                ['attribute' => 'news_from_date', 'is' => new \Zend_Db_Expr('not null')],
                ['attribute' => 'news_to_date', 'is' => new \Zend_Db_Expr('not null')]
            ]
        );
        $collection->setPageSize($limit);
        $collection->getSelect()->order('created_at desc');
//        print_r($collection->getSelect()->__toString());die;
        return $collection;
    }

    protected function _getBestSellersProducts()
    {
        $limit = $this->_limitBestSellers();
        $collection = $this->_collection;
        $date = new \Zend_Date();
        $today = $date->addDay(1)->get('Y-MM-dd');
        $fromDay = $date->subMonth(1)->getDate()->get('Y-MM-dd');
        $connection = $this->_resourceConnection->getConnection();
        $select = $connection->select();
        $columns = [
            'product_name' => 'si.name',
            'product_id' => 'si.product_id',
            'sum_qty_ordered' => new \Zend_Db_Expr('sum(qty_ordered)')
        ];

        $select->from(
            ['so' => $collection->getResource()->getTable('sales_order')],
            $columns
        )->joinInner(
            ['si' => $collection->getResource()->getTable('sales_order_item')],
            'so.entity_id = si.order_id',
            []
        )->where(
            "so.created_at between '{$fromDay}' and '{$today}'"
        )->where(
            'so.state != ?',
            \Magento\Sales\Model\Order::STATE_CANCELED
        )->where(
            'parent_item_id is null'
        )->group('product_id')
            ->limit($limit)
            ->order('sum_qty_ordered desc');

        $items = $connection->fetchAll($select);
//        print_r($select->__toString()); die();

        $ids = [];

        foreach($items as $item)
        {
            $ids[] = $item['product_id'];
        }

        $result = $this->_productCollectionFactory->create();
        $result->addIdFilter($ids)
            ->addAttributeToSelect('*');

        return $result;
    }

    public function getLoadedNewProductsCollection()
    {
        return $this->_getNewProducts();
    }

    public function getLoadedBestSellersProductCollection()
    {
        return $this->_getBestSellersProducts();
    }

    public function isModuleEnabled()
    {
        if($this->_carouselHelper->getGeneralConfig('enable') == 1)
            return true;
        else
            return false;
    }
}