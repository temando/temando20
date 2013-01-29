<?php

class Ewave_Temando_Block_Onepage_Shipping_Method_Available_Multi extends Ewave_Temando_Block_Onepage_Shipping_Method_Available
{
    
    protected $_rates = null;
    
    protected $_hasError = false;
    
    public function getCode()
    {
        return Mage::getModel('temando/shipping_carrier_temando')->getCode();
    }
    
    /**
     * Returns all address shipping rates
     * 
     * @return array 
     */
    public function getAllShippingRates()
    {
	if(!$this->_rates) {
	    $this->_rates = parent::getShippingRates();
	}	
	return $this->_rates;
    }
       
    /**
     * Returns Temando shipping rates applicable to given origin
     * (if free shipping method exists, only the free shipping method is returned)
     * 
     * @param int $originId
     * @return array 
     */
    public function getShippingRatesByOrigin($originId)
    {
	$return = $this->getAllShippingRates();
	$temando_rates = array_key_exists($this->getCode(), $return) ? $return[$this->getCode()] : array();
	
	$origin_temando_rates = array();
	foreach($temando_rates as $rate)
	{
	    Mage::log('origin'.$originId.'|'.$rate->getCode(), null, 'rates.log', true);
	    if($rate->getOriginId() == $originId || $this->_hasError) {
		$origin_temando_rates[] = $rate;
		if($rate->getErrorMessage()) {
		    $this->_hasError = true;
		}
		if($rate->getCode() == 'temando_10000') {
		    $origin_temando_rates = array($rate); break;
		}
	    }
	}
	
	$return[$this->getCode()] = $origin_temando_rates;
	return $return;
    }
      
    public function getSole()
    {
        $groups = parent::getShippingRates();
        return count($groups) == 1 && count($groups[0]) == 1;
    }
    
    /**
     * @return Ewave_Temando_Model_Options
     */
    public function getOptions()
    {
        return Mage::registry('temando_current_options');
    }
    
    /**
     * Saves pickup data to session for later use in observer 
     */
    public function preparePickupData()
    {
	//save pickup in session for use in observer
	$isPickup = Mage::app()->getRequest()->getParam('shipping_is_pickup', false);
	$pickupLoc = Mage::app()->getRequest()->getParam('pickup-location', '');
	
	Mage::getSingleton('checkout/session')->setData('temando_is_pickup', $isPickup);
	Mage::getSingleton('checkout/session')->setData('temando_pickup_location', $pickupLoc);
	
    }
    
    /**
     * Returns origin model
     * 
     * @param int $origin_id
     * @return \Ewave_Temando_Model_Warehouse  
     */
    public function getOrigin($origin_id)
    {
	return Mage::getModel('temando/warehouse')->load($origin_id);
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
    
    /**
     * Returns class for a shipping rate element (radio button)
     * (used in conjunction with extras tick boxes)
     * 
     * @param string $code Shipping rate code
     * @return string 
     */
    public function getClassFromRateCode($code)
    {
	$class = '';
	if(preg_match('/^temando_/', $code)) {
	    preg_match_all('/(insurance|carbonoffset)_(Y|N)/', $code, $matches);
	    $class = implode(' ', $matches[0]);
	}
	return $class;
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

    
}
