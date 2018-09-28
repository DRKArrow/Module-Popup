<?php
namespace Tigren\Popup\Block\Carousel;

use Magento\Catalog\Block\Product\Context;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Config\Element;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Url\Helper\Data;

class OwlCarousel extends \Magento\Catalog\Block\Product\ListProduct {
    protected $_productCollectionFactory;

    protected $_bestSellersCollection;

    public function __construct(Context $context, PostHelper $postDataHelper, Resolver $layerResolver, CategoryRepositoryInterface $categoryRepository, Data $urlHelper,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
                                \Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory $bestSellersCollection,
                                array $data = [])
    {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_bestSellersCollection = $bestSellersCollection;
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