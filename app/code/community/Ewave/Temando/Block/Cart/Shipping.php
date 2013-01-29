<?php

class Ewave_Temando_Block_Cart_Shipping extends Mage_Checkout_Block_Cart_Shipping
{


    public function getCityActive()
    {
        return (bool)Mage::getStoreConfig('carriers/temando/active') || parent::getCityActive();
    }

    /**
     * Check if one of carriers require state/province
     *
     * @return bool
     */
    public function isStateProvinceRequired()
    {
        return (bool)Mage::getStoreConfig('carriers/temando/active') || parent::isStateProvinceRequired();
    }

    /**
     * Check if one of carriers require city
     *
     * @return bool
     */
    public function isCityRequired()
    {
        return (bool)Mage::getStoreConfig('carriers/temando/active') || parent::isCityRequired();
    }

    /**
     * Check if one of carriers require zip code
     *
     * @return bool
     */
    public function isZipCodeRequired()
    {
        return (bool)Mage::getStoreConfig('carriers/temando/active') || parent::isZipCodeRequired();
    }

    public function getEstimatePostcode()
    {
        $return = parent::getEstimatePostcode();
        if (!$return && Mage::helper('temando')->getSessionPostcode()) {
            $return = Mage::helper('temando')->getSessionPostcode();
        }

        return $return;
    }

    public function getEstimateCity()
    {
        $return = parent::getEstimateCity();
        if (!$return && Mage::helper('temando')->getSessionCity()) {
            $return = Mage::helper('temando')->getSessionCity();
        }

        return $return;
    }

    public function getEstimateRegionId()
    {
        $return = parent::getEstimateRegionId();
        if (!$return && Mage::helper('temando')->getSessionRegionId()) {
            $return = Mage::helper('temando')->getSessionRegionId();
        }

        return $return;
    }

    public function getEstimateRegion()
    {
        $return = parent::getEstimateRegion();
        if (!$return && Mage::helper('temando')->getSessionRegion()) {
            $return = Mage::helper('temando')->getSessionRegion();
        }

        return $return;
    }

    /**
     * Translate block sentence
     *
     * @return string
     */
    public function __()
    {
        $args = func_get_args();
        if ((count($args) == 1) && ('City' == $args[0])) {
            $args[0] = 'Suburb';
        }

        $expr = new Mage_Core_Model_Translate_Expr(array_shift($args), $this->getModuleName());
        array_unshift($args, $expr);
        return Mage::app()->getTranslator()->translate($args);
    }
    
    /**
     * Gets list of origins and their products for current request
     * 
     * @return array 
     */
    public function getOrigins()
    {
	return Mage::getSingleton('checkout/session')->getData('origins');
    }
    
    /**
     * Returns shipping rates applicable to given origin
     * (if free shipping method exists, only the free shipping method is returned)
     * 
     * @param int $originId
     * @return array 
     */
    public function getShippingRatesByOrigin($originId)
    {
	$return = $this->getEstimateRates();
	$temando_rates = $return[$this->getCode()];
	
	$origin_temando_rates = array();
	foreach($temando_rates as $rate)
	{
	    if($rate->getOriginId() == $originId) {
		$origin_temando_rates[] = $rate;
		if($rate->getCode() == 'temando_10000') {
		    $origin_temando_rates = array($rate); break;
		}
	    }
	}
	
	$return[$this->getCode()] = $origin_temando_rates;
	return $return;
    }
    
    /**
     * Returns Temando carrier code
     * 
     * @return string 
     */
    public function getCode()
    {
	return Mage::getModel('temando/shipping_carrier_temando')->getCode();
    }
    
    /**
     * Returns Temando origin model
     * 
     * @param int $origin_id
     * @return \Ewave_Temando_Model_Warehouse  
     */
    public function getOrigin($origin_id)
    {
	return Mage::getModel('temando/warehouse')->load($origin_id);
    }
    
        /**
     * Returns selected shipping methods
     * 
     * @return array or null if no shipping methods selected 
     */
    public function getSelectedShippingMethods()
    {
	return Mage::getSingleton('checkout/session')->getData('shipping_methods');
    }
    
    public function isSplitShipment()
    {
	$origins = $this->getOrigins();
	return count($origins) > 1;
    }
    
    /**
     * Returns product description (name + sku)
     * 
     * @param int $product_id
     * @return string 
     */
    public function getProductHtml($product_id)
    {
	$product = Mage::getModel('catalog/product')->load($product_id);
	return $product->getName() . " (" . $product->getSku() . ")";
    }

}