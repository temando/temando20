<?php

/**
 * Description of PickupController
 *
 * @author martin
 */
class Ewave_Temando_PickupController extends Mage_Core_Controller_Front_Action {
    

    public function getLocationsAction() {
	$pickups = array();
	$data = $this->getRequest()->getPost();
	
	if(!$data['country'] || !$data['pcode'] || !$data['suburb']) {
	    echo 'Please enter destination address.';
	    return;
	}
	
	$origin = Mage::helper('temando/v2')->getDynamicOrigin($data['pcode'], Mage::app()->getStore()->getId());
	if(!$origin->getId()) {
	    echo 'Unable to fetch origin location.';
	    return;
	}
	
	$helper = Mage::helper('temando');
	$request = Mage::getModel('temando/api_v2_request');
	/** @var $request Ewave_Temando_Model_Api_V2_Request **/
	
	$request
	    ->setDestination($data['country'], $data['pcode'], $data['suburb'])
	    ->setOrigin(
		$origin->getCountry(),
		$origin->getPostcode(),
		$origin->getCity(),
		$origin->getType() ? $origin->getType() : $origin->getLocationType(),
		$origin->getName())
	    ->setItems(Mage::getSingleton('checkout/session')->getQuote()->getAllItems())
	    ->setMagentoQuoteId(Mage::getSingleton('checkout/session')->getQuote()->getId());
		
	$api = Mage::getModel('temando/api_v2_client');
	/** @var $api Ewave_Temando_Model_Api_V2_Client **/
	
	try {
	    $api->connect(
		$helper->getConfigData('general/username'), 
		$helper->getConfigData('general/password'), 
		$helper->getConfigData('general/sandbox')
	    );
	    $response = $api->getPickupLocations($request->toRequestArray(true));
	    if(isset($response->pickupLocation) && is_array($response->pickupLocation)) {
		foreach($response->pickupLocation as $loc) 
		{
		    $pickup = Mage::getModel('temando/pickup')->loadFromResponse($loc);
		    if($pickup) {
			$pickups[] = $pickup->toArray();
		    }
		}
	    }
	} catch(Exception $e) {
	    $pickups['error'] = $e->getMessage();
	}
	
	$json = Mage::helper('core')->jsonEncode($pickups);
		
	$this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($json);
    }
    
    /**
     * Saves all available pickup locations to session
     * for later use in observer 
     */
    public function setLocationsAction()
    {
	$json = Mage::helper('core')->jsonEncode(array('success' => 'true'));
	$locations = Mage::helper('core')->jsonDecode($this->getRequest()->getPost('locations'));
	
	try {
	    Mage::getSingleton('checkout/session')->setData('temando_pickup_locations', $locations);
	} catch(Exception $e) {
	    $json = Mage::helper('core')->jsonEncode(array('error' => $e->getMessage()));
	}
	
	$this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($json);
    }
    
}


