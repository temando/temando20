<?php

class Ewave_Temando_Block_Onepage_Shipping_Delivery_Options extends Mage_Checkout_Block_Onepage_Shipping {
    
    /**
     * Delivery options enabled in system configuration for display in checkout
     * 
     * @var array 
     */
    protected $_enabledOptions = null;
    
    /**
     * Retrieve Temando system configuration value
     * 
     * @param string $field The identifier of config entry
     * @return mixed 
     */
    public function getConfigData($field)
    {
	return Mage::helper('temando')->getConfigData($field);
    }
    
    /**
     * Show additional delivery options to customer?
     * 
     * @return boolean 
     */
    public function showOptions()
    {
	return $this->getConfigData('checkout/delivery_options');
    }
    
    /**
     * Show customer shipment comment?
     * 
     * @return boolean 
     */
    public function showComment()
    {
	return $this->getConfigData('checkout/ship_comment');
    }
    
    /**
     *  Show shipping instruction input text?
     * 
     *  @return boolean
     */
    public function showInstructions()
    {
	return $this->getConfigData('checkout/ship_instructions');
    }
    
    /**
     * Returns saved comment as supplied by customer on checkout
     * 
     * @return string
     */
    public function getComment()
    {
	$comment = Mage::getSingleton('checkout/session')->getData('customer_comment');
	if($comment) {
	    return htmlspecialchars($comment);
	}
	return '';
    }
    
    /**
     * Returns saved shipping instructions as supplied by customer on checkout
     * 
     * @return string 
     */
    public function getInstructions()
    {
	$instructions = Mage::getSingleton('checkout/session')->getData('shipping_instructions');
	if($instructions) {
	    return htmlspecialchars($instructions);
	}
	return '';
    }
    
    /**
     * Returns enabled checkout options
     * 
     * @return array 
     */
    public function getEnabledOptions()
    {
	if(!$this->_enabledOptions) {
	    $this->_enabledOptions = Mage::getModel('temando/checkout_delivery_options')->getEnabledOptions();
	}
	return $this->_enabledOptions;
    }
    
    /**
     * Returns customer selected delivery options
     * 
     * @return array Array of selected options  
     */
    public function getSelectedOptions()
    {
	$selected = Mage::getSingleton('checkout/session')->getData('selected_delivery_options');
	if(!is_array($selected)) {
	    $selected = array();
	}
	return $selected;
    }
    
}


