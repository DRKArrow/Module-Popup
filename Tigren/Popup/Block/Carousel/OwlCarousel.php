<?php
namespace Tigren\Popup\Block\Carousel;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Url\Helper\Data;

class OwlCarousel extends \Magento\Catalog\Block\Product\ListProduct {
    protected $_productCollectionFactory;

    protected $_bestSellersCollection;

    protected $_catalogProductTypeConfigurable;

    protected $__catalogProductVisibility;
    public function __construct(Context $context, PostHelper $postDataHelper, Resolver $layerResolver, CategoryRepositoryInterface $categoryRepository, Data $urlHelper,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
                                \Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory $bestSellersCollection,
                                \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
                                \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
                                array $data = [])
    {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_bestSellersCollection = $bestSellersCollection;
        $this->_catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->_catalogProductVisibility = $catalogProductVisibility;
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

    public function getLoadedNewProductsCollection()
    {
        return $this->_getNewProducts();
    }

    protected function _getBestSellersIds()
    {
        $collection = $this->_bestSellersCollection->create();
        $ids = [];
        foreach($collection as $item)
        {
            $parentId = $this->_catalogProductTypeConfigurable->getParentIdsByChild($item->getProductId());
            if(isset($parentId[0]))
                $ids[] = $parentId[0];
            else
                $ids[] =  $item->getProductId();
        }
        return $ids;
    }

    protected function _getBestSellersProducts()
    {
//        $ids = $this->_getBestSellersIds();
//        $collection = $this->_productCollectionFactory->create();
//        $collection->addIdFilter($ids)
//                    ->addAttributeToSelect('*')
//                    ->setPageSize(12);
        $date = new \Zend_Date();
        $today = $date->setDay(1)->getDate()->get('Y-MM-dd');
        $fromDay = $date->subMonth(1)->getDate()->get('Y-MM-dd');
        $collection = $this->_productCollectionFactory->create()->addAttributeToSelect('*')->setPageSize(12);
        $collection->getSelect()->joinRight(
            array('aggregation' => $collection->getResource()->getTable('sales_bestsellers_aggregated_monthly')),
            "e.entity_id = aggregation.product_id AND aggregation.period BETWEEN '{$fromDay}' AND '{$today}'",
            array('SUM(aggregation.qty_ordered) AS sold_quantity')
        )
        ->group('e.entity_id')
        ->order(array('sold_quantity DESC', 'e.created_at'));
        return $collection;
    }

    public function getLoadedBestSellersProductCollection()
    {
        return $this->_getBestSellersProducts();
    }
}