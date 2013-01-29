<?php

class Ewave_Temando_Model_Api_V2_Request_Anywhere extends Mage_Core_Model_Abstract
{
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('temando/api_v2_request_anywhere');
    }
    
    public function toRequestArray()
    {
        if (!$this->validate()) {
            return false;
        }
	$delivery_options = is_array($this->getDeliveryOptions()) ? $this->getDeliveryOptions() : array();
	$data = array(
            'itemNature' => 'Domestic',
            'itemMethod' => 'Door to Door',
            'destinationCountry' => $this->getDestinationCountry(),
            'destinationCode' => sprintf("%04d", $this->getDestinationPostcode()),
            'destinationSuburb' => $this->getDestinationCity(),
            'destinationIs' => 'Residence',
	    'destinationResPostalBox' => Mage::helper('temando/v2')->isStreetWithPO($this->getDestinationStreet()) ? 'Y':'N',
	    'destinationResLimitedAccess' => array_key_exists('limited_access', $delivery_options) ? 'Y' : 'N',
	    'destinationResHeavyLift' => array_key_exists('heavy_lift', $delivery_options) ? 'Y' : 'N',
	    'destinationResTailgateLifter' => array_key_exists('tailgate_lifter', $delivery_options) ? 'Y' : 'N',
	    'destinationResUnattended' => array_key_exists('unattended_delivery', $delivery_options) ? 'Y' : 'N',
            'originBusNotifyBefore' => 'Y',
            'originBusLimitedAccess' => 'N',
	    'originDescription' => $this->getOriginName()
        );
	
	if($this->getIsPickup()) {
	    $data['pickupDescription'] = $this->getPickupDescription();
	}

        return $data;
    }
    
    public function validate()
    {
        return
            $this->getOriginCountry() &&
            $this->getOriginPostcode() &&
            $this->getOriginCity() &&
            $this->getOriginType() &&
            $this->getDestinationCountry() &&
            $this->getDestinationPostcode() &&
            $this->getDestinationCity();
    }
    
}
