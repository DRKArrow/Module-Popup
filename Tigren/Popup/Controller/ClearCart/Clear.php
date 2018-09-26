<?php
namespace Tigren\Popup\Controller\ClearCart;

use Magento\Framework\App\Action\Context;

class Clear extends \Magento\Framework\App\Action\Action {

    protected $_resultJsonFactory;

    protected $_checkoutSession;

    protected $_model;

    public function __construct(Context $context, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
                                \Magento\Checkout\Model\Session $checkoutSession,
                                \Magento\Quote\Model\Quote $quote)
    {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_model = $quote;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->_resultJsonFactory->create();
        if($this->getRequest()->isAjax()) {
            $quoteId = $this->_checkoutSession->getQuote()->getId();
            $quote = $this->_model->load($quoteId);
            $test = Array
            (
                'quoteId' => $quoteId,
                'quoteData' => $quote->getData()
            );
            $quote->delete();
            return $result->setData($test);
        }
    }
}