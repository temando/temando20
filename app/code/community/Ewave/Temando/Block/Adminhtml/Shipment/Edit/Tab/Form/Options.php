<?php

class Ewave_Temando_Block_Adminhtml_Shipment_Edit_Tab_Form_Options extends Ewave_Temando_Block_Adminhtml_Shipment_Edit_Tab_Abstract
{
    protected $_selectedOptions;
    
    /**
     * Gets the description of the Temando quote selected by the customer.
     *
     * @return Ewave_Temando_Model_Quote
     */
    public function getCustomerSelectedDeliveryOptions()
    {
	if(!$this->_selectedOptions) {
	    $options = $this->getShipment()->getCustomerSelectedDeliveryOptions();
	    if(strlen($options)) {
		$selected_options = explode(',', $options);
	    } else {
		$selected_options = array();
	    }
	    foreach(Mage::getModel('temando/checkout_delivery_options')->getAllOptions() as $key => $desc) {
		$this->_selectedOptions[$desc] = in_array($key, $selected_options) ? 'Yes' : 'No';
	    }
	    
	}
	return $this->_selectedOptions;
	    
    }
    
}
