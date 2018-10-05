<?php
namespace Tigren\Popup\Block\Carousel;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Url\Helper\Data;

class OwlCarousel extends \Magento\Catalog\Block\Product\ListProduct {
    protected $_productCollectionFactory;

    protected $_resourceConnection;

    protected $_collection;
    public function __construct(Context $context, PostHelper $postDataHelper, Resolver $layerResolver, CategoryRepositoryInterface $categoryRepository, Data $urlHelper,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
                                \Magento\Framework\App\ResourceConnection $resourceConnection,
                                \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
                                array $data = [])
    {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_resourceConnection = $resourceConnection;
        $this->_collection = $collection;
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }

    protected function _getNewProducts()
    {
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
        $collection->setPageSize(12);
        return $collection;
    }

    protected function _getBestSellersProducts()
    {
        $collection = $this->_collection;
        $date = new \Zend_Date();
        $today = $date->get('Y-MM-dd');
        $fromDay = $date->subMonth(1)->getDate()->get('Y-MM-dd');
        $connection = $this->_resourceConnection->getConnection();
        $select = $connection->select();
        $columns = [
            'product_name' => 'si.name',
            'product_id' => 'si.product_id',
            'qty_ordered' => new \Zend_Db_Expr('count(product_id)')
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
        )->group('product_id')->limit(12)->order('qty_ordered desc');

        $items = $connection->fetchAll($select);

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
}