<?php

class Ewave_Temando_Block_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    protected $_origins = null;
    
    
    public function isDynamicPricing()
    {
        $method = Mage::helper('temando')->getConfigData('pricing/method');
        
        $rates = $this->getShippingRates();
        
        if (!array_key_exists('temando', $rates)) {
            return false;
        }
        
        if (count($rates['temando']) === 1) {
            return false;
        }
        
        return $method === 'hybrid'
        ;
    }
    
    public function getOrigins()
    {
	if(!$this->_origins) {
	    $this->_origins = Mage::getSingleton('checkout/session')->getOrigins();
	}
	return $this->_origins;
    }
    
    public function isSplitShipment()
    {
	$origins = $this->getOrigins();
	return count($origins) > 1;
    }
    
}
