<?php

class Ewave_Temando_Model_Option_Pickup extends Ewave_Temando_Model_Option_Boolean
{
    
    protected $_id = 'pickupservice';
    protected $_name = 'Pickup Service';
    protected $_action_type = 'pickup';
    protected $_desc = 'Sample description for pickup service here...';
    
        
    public function _construct() {
	parent::_construct();
	
	$customTitle = Mage::helper('temando')->getConfigData('pickup/title');
	if(strlen(trim($customTitle))) {
	    $this->_name = $customTitle;
	}
	$customDesc = Mage::helper('temando')->getConfigData('pickup/description');
	if(strlen(trim($customDesc))) {
	    $this->_desc = $customDesc;
	}
	return $this;
    }
    
    /**
     * The parent function applies the action depending on the value, here we
     * also update the quote's information to indicate that it includes
     * pickup service.
     *
     * (non-PHPdoc)
     *
     * @see Ewave_Temando_Model_Option_Boolean::_apply()
     */
    protected function _apply($value, &$quote)
    {
        /* @var $quote Ewave_Temando_Model_Quote */
        if (parent::_apply($value, $quote)) {
            $quote->setPickupIncluded(true);
        }
    }
    
}
