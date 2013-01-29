<?php

/**
 * @method string getDescription()
 * @method string getType()
 * @method string getCompanyName()
 * @method string getStreet()
 * @method string getSuburb()
 * @method string getState()
 * @method string getCode()
 * @method string getCountry()
 * @method string getLoadingFacilities()
 * @method string getForklift()
 * @method string getDock()
 * @method string getLimitedAccess()
 * @method string getPostalBox()
 * @method string getAvailable()
 * 
 * @method Ewave_Temando_Model_Pickup setDescription()
 * @method Ewave_Temando_Model_Pickup setType()
 * @method Ewave_Temando_Model_Pickup setCompanyName()
 * @method Ewave_Temando_Model_Pickup setStreet()
 * @method Ewave_Temando_Model_Pickup setSuburb()
 * @method Ewave_Temando_Model_Pickup setState()
 * @method Ewave_Temando_Model_Pickup setCode()
 * @method Ewave_Temando_Model_Pickup setCountry()
 * @method Ewave_Temando_Model_Pickup setLoadingFacilities()
 * @method Ewave_Temando_Model_Pickup setForklift()
 * @method Ewave_Temando_Model_Pickup setDock()
 * @method Ewave_Temando_Model_Pickup setLimitedAccess()
 * @method Ewave_Temando_Model_Pickup setPostalBox()
 * @method Ewave_Temando_Model_Pickup setAvailable()
 */
class Ewave_Temando_Model_Pickup extends Mage_Core_Model_Abstract {
    
    public function _construct() {
	parent::_construct();
    }
    
    /**
     * Loads Pickup Location object data from API response
     * 
     * @param stdClass $location
     * @return null|\Ewave_Temando_Model_Pickup 
     */
    public function loadFromResponse($location)
    {
	if(!$location instanceof stdClass) {
	    return null;
	}
	
	$this
	    ->setDescription($location->description)
	    ->setType($location->type)
	    ->setCompanyName($location->companyName)
	    ->setStreet($location->street)
	    ->setSuburb($location->suburb)
	    ->setState($location->state)
	    ->setCode($location->code)
	    ->setCountry($location->country)
	    ->setLoadingFacilities($location->loadingFacilities)
	    ->setForklift($location->forklift)
	    ->setDock($location->dock)
	    ->setLimitedAccess($location->limitedAccess)
	    ->setPostalBox($location->postalBox)
	    ->setAvailable($location->available)
	    ->setTitle($this->getPickupTitleForSelectBox())
	    ->setPosition()
	    ;
	
	return $this;
    }
    
    /**
     * Returns pickup location title - name & address
     * @return string 
     */
    public function getPickupTitleForSelectBox()
    {
	return $this->getCompanyName().', '.$this->getStreet().', '.$this->getSuburb().' '.$this->getState().', '.$this->getCountry();
    }
    
    
}

