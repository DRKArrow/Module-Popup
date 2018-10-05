<?php

namespace Tigren\Popup\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Carousel extends AbstractHelper
{

    const XML_PATH_PRODUCT_CAROUSEL = 'product_carousel/';

    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field, ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    public function getGeneralConfig($code, $storeId = null)
    {

        return $this->getConfigValue(self::XML_PATH_PRODUCT_CAROUSEL .'general/'. $code, $storeId);
    }

    public function getConfigurationConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_PRODUCT_CAROUSEL .'configuration/'. $code, $storeId);
    }
}