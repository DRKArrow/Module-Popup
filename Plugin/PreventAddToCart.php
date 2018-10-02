<?php
namespace Tigren\Popup\Plugin;

use Magento\Framework\Exception\LocalizedException;

class PreventAddToCart
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    protected $_checkoutSession;

    protected $_productRepository;

    protected $_helperData;

    /**
     * Plugin constructor.
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Tigren\Popup\Helper\Data $helperData
    ) {
        $this->quote = $checkoutSession->getQuote();
        $this->_checkoutSession = $checkoutSession;
        $this->_productRepository = $productRepository;
        $this->_helperData = $helperData;
    }

    /**
     * beforeAddProduct
     *
     * @param      $subject
     * @param      $productInfo
     * @param null $requestInfo
     *
     * @return array
     * @throws LocalizedException
     */
    public function beforeAddProduct($subject, $productInfo, $requestInfo = null)
    {
        if($this->_helperData->getGeneralConfig('enable') == 1)
        {
            $items = $this->_checkoutSession->getQuote()->getAllVisibleItems();
            $i = 0;
            foreach($items as $item)
            {
                $itemId = $item->getProduct()->getId();
                $product = $this->_productRepository->getById($itemId);
                if($product->getCustomAttribute('is_multiple_cart')) {
                    if($product->getCustomAttribute('is_multiple_cart')->getValue() == 0) {
                        $i++;
                    }
                }
            }
            if ($i > 0) {
                die();
            }
        }

        return [$productInfo, $requestInfo];
    }
}