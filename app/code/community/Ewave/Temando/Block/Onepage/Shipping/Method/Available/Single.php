<?php

class Ewave_Temando_Block_Onepage_Shipping_Method_Available_Single extends Ewave_Temando_Block_Onepage_Shipping_Method_Available
{
    
    
    public function getCode()
    {
        return Mage::getModel('temando/shipping_carrier_temando')->getCode();
    }
    
    public function getTemandoShippingRates()
    {
        $groups = parent::getShippingRates();
        return $groups[$this->getCode()];
    }
    
    /**
     * Gets all the permutations for a specific quote, given the options
     * available.
     *
     * @param Ewave_Temando_Model_Quote $quote
     */
    public function getPermutations($quote)
    {
        return $this->getOptions()->applyAll($quote);
    }
    
    public function getQuotes()
    {
        $quote_collection = Mage::getModel('temando/quote')->getCollection();
        /* @var $quote_collection Ewave_Temando_Model_Mysql4_Quote_Collection */
        $quote_collection->addFieldToFilter('magento_quote_id', $this->getQuote()->getId());
        
        $quotes = array();

        $rates = $this->getTemandoShippingRates();
        foreach ($quote_collection as $quote) {
            $found = false;
            foreach ($rates as $rate) {
                $_t = explode("_", $rate->getMethod());
                if ($_t[0] == $quote->getId()) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                continue;
            }

            $carrier_name = strtolower(trim($quote->getCarrier()->getCompanyName()));
            
            if (!array_key_exists($carrier_name, $quotes)) {
                $quotes[$carrier_name] = array();
            }
            
            $quotes[$carrier_name][] = $quote;
        }
        
        ksort($quotes);
        
        return $quotes;
    }
    
    public function getSole()
    {
        $groups = $this->getShippingRates();
        return count($groups) == 1 && count($groups[0]) == 1;
    }
    
    public function getRateFromPermutation($quote, $permutation_id)
    {
	($quote->getRuleId()) ? $ruleId = '_' . $quote->getRuleId() : $ruleId = '';
        foreach ($this->getTemandoShippingRates() as $rate) {
            if ($rate->getMethod() === $quote->getId().$ruleId. '_' . $permutation_id) {
                return $rate;
            }
        }
        return null;
    }
    
    /**
     * @return Ewave_Temando_Model_Options
     */
    public function getOptions()
    {
        return Mage::registry('temando_current_options');
    }
    
    public function getDynamicRuleTitle($permutation) {
	$title = '';
	if($permutation->getRuleId()) {
	    $rule = Mage::getModel('temando/rule')->load($permutation->getRuleId());
	    if($rule->isDynamic()) {
		$title = $permutation->getDynamicDescriptionFromRule(
			    $rule->getActionDynamicShowCarrierName(),
			    $rule->getActionDynamicShowCarrierTime(),
			    $rule->getActionDynamicLabel()
			 );
	    }
	} else {
	    if(Mage::helper('temando')->getConfigData('options/show_name_time'))
	    {
		$title = $permutation->getDescription(false);
	    }
	    else
	    {
		$title = Mage::helper('temando')->getConfigData('options/shown_name');
	    }
	}
	
	return $title;
    }
    
    public function preparePickupData()
    {
	//save pickup in session for use in observer
	$isPickup = Mage::app()->getRequest()->getParam('shipping_is_pickup', false);
	$pickupLoc = Mage::app()->getRequest()->getParam('pickup-location', '');
	
	Mage::getSingleton('checkout/session')->setData('temando_is_pickup', $isPickup);
	Mage::getSingleton('checkout/session')->setData('temando_pickup_location', $pickupLoc);
	
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
    
}
