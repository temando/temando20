<?php
/**
 * Rewrite of Mage_Checkout_Model_Type_Onepage
 * - support for split shipments; save virtual combined shipping rate if multi origin shipping 
 */

class Ewave_Temando_Model_Checkout_Type_Onepage extends Mage_Checkout_Model_Type_Onepage {
    
    /**
     * Specify quote shipping method
     *
     * @param   string $shippingMethod
     * @return  array
     */
    public function saveShippingMethod($shippingMethod)
    {
        if (empty($shippingMethod)) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid shipping method.'));
        }
	
	$this->saveCustomerComment();
	$this->saveShippingInstructions();
	
	if(is_array($shippingMethod)) {
	    //split shipment
	    $total_origins = Mage::app()->getRequest()->getParam('total_origins', 0);
	    if(count($shippingMethod) < $total_origins) {
		return array('error' => -1, 'message' => Mage::helper('checkout')->__('Please select a shipping method for each origin warehouse.'));
	    }
	    $selectedRates = array();
	    foreach($shippingMethod as $method)
	    {
		$rate = $this->getQuote()->getShippingAddress()->getShippingRateByCode($method);
		if(!$rate) {
		    return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid shipping method.'));
		}
		$selectedRates[] = $rate;
	    }
	    
	    $combinedRate = $this->createCombinedRate($selectedRates);
	    $this->getQuote()->getShippingAddress()->addShippingRate($combinedRate);
	    
	    $this->_checkoutSession->setData('shipping_methods', $shippingMethod);
	    $shippingMethod = $combinedRate->getCode();
	} else {
	    $rate = $this->getQuote()->getShippingAddress()->getShippingRateByCode($shippingMethod);
	    if (!$rate) {
		return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid shipping method.'));
	    }
	    $this->_checkoutSession->unsetData('shipping_methods');
	}
	
        $this->getQuote()->getShippingAddress()->setShippingMethod($shippingMethod);

        $this->getCheckout()
            ->setStepData('shipping_method', 'complete', true)
            ->setStepData('payment', 'allow', true);

        return array();
    }
    
    /**
     * Creates virtual shipping rate - a combination from customer selected shipping methods
     * for each origin warehouse
     * 
     * @param array $rates
     * @return Ewave_Temando_Model_Quote_Address_Rate 
     */
    public function createCombinedRate($rates)
    {
	$carrier = $carrierTitle = $methodTitle = '';
	$code = 'temando_multi:';
	$price = 0;
	
	foreach($rates as $rate) {
	    $code .= $rate->getCode() . '_' . $rate->getMethod() . '|';
	    $carrier .= $rate->getCarrier() . '|';
	    $methodTitle .= $rate->getMethodTitle() . ' + ';
	    $price += $rate->getPrice();
	}
	$combinedRate = Mage::getModel('temando/quote_address_rate');
	/*@var $combinedRate Ewave_Temando_Model_Quote_Address_Rate */
	
	$combinedRate
		->setCode(substr($code, 0, strlen($code)-1))
                ->setCarrier(substr($carrier, 0, strlen($carrier)-1))
                ->setCarrierTitle('Multi Origin Shipping')
                ->setMethod('temando_multi')
                ->setMethodTitle(substr($methodTitle, 0, strlen($methodTitle)-3))
                ->setPrice($price);
	
	$selected = Mage::getModel('shipping/rate_result_method')
		    ->setCode($combinedRate->getCode())
		    ->setCarrier($combinedRate->getCarrier())
		    ->setCarrierTitle($combinedRate->getCarrierTitle())
		    ->setMethod($combinedRate->getMethod())
		    ->setMethodTitle($combinedRate->getMethodTitle())
		    ->setPrice($combinedRate->getPrice());
	
	$this->_checkoutSession->setData('combined_rate', $selected);
	
	return $combinedRate;
    }
    
    /**
     * Save additional customer comment
     */
    public function saveCustomerComment()
    {
	$comment = Mage::app()->getRequest()->getParam('comment', null);
	if($comment && strlen(trim($comment))) {
	    $this->_checkoutSession->setData('customer_comment', $comment);
	} else {
	    $this->_checkoutSession->unsetData('customer_comment');
	}
    }
    
    /**
     * Save shipping instructions 
     */
    public function saveShippingInstructions()
    {
	$instructions = Mage::app()->getRequest()->getParam('instructions', null);
	if($instructions && strlen(trim($instructions))) {
	    $this->_checkoutSession->setData('shipping_instructions', $instructions);
	} else {
	    $this->_checkoutSession->unsetData('shipping_instructions');
	}
    }
}


