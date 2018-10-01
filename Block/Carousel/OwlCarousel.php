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
    public function __construct(Context $context, PostHelper $postDataHelper, Resolver $layerResolver, CategoryRepositoryInterface $categoryRepository, Data $urlHelper,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
                                \Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory $bestSellersCollection,
                                \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
                                array $data = [])
    {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_bestSellersCollection = $bestSellersCollection;
        $this->_catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }

    public function getNewProducts()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('type_id', array('eq', array('virtual', 'grouped', 'configurable')));
        $collection->addAttributeToSort('entity_id', 'desc');
        $collection->setPageSize(12);
        return $collection;
    }

    private function getBestSellersIds()
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

    public function getBestSellersProducts()
    {
        $ids = $this->getBestSellersIds();
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('entity_id', ['in' => $ids]);
        $collection->setPageSize(12);
        return $collection;
    }
}