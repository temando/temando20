<?php


class Ewave_Temando_Model_Mysql4_Warehouse_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('temando/warehouse');
    }
    
    /**
     * Returns origin with highest priority which serves given postcode
     * 
     * @param string $postcode
     * @return Ewave_Temando_Model_Warehouse|null 
     */
    public function getOriginByPostcode($postcode, $storeId = null)
    {
	$this->setOrder('priority', 'ASC')->load();
	
	$validOrigin = null;
	foreach($this->_items as $warehouse) {
	    
	    $store_ids = explode(',', $warehouse->getStoreIds());
	    if($storeId && !in_array($storeId, $store_ids))
		continue;
	    
	    if($warehouse->servesArea($postcode)) {
		$validOrigin = $warehouse;
		break;
	    }
	}
	
	return $validOrigin;
	
    }
    
    /**
     * Returns valid origins which can serve given request
     * 
     * @param string $postcode Destination Postal Code
     * @param array $productIds List of product IDs on order
     * @param int $storeId Current Store ID
     * @param array $unavailable Array of product IDs which cannot be allocated
     * @return mixed Array of valid origins or false if products cannot be allocated 
     * @todo improve warehouse selection - rewrite function to recursive; check if there is a warehouse which can serve the rest of the request after each loop
     */
    public function getDynamicOrigins($postcode, $storeId = null, $productIds = array(), &$unavailable = array())
    {
	$this->setOrder('priority', 'ASC')->load();
	$return = array();
	
	//get all warehouses serving given area, enabled for given store
	$validOrigins = array();
	foreach($this->_items as $warehouse) {	    
	    $storeIds = explode(',', $warehouse->getStoreIds());
	    if($storeId && !in_array($storeId, $storeIds))
		continue;
	    
	    if($warehouse->servesArea($postcode)) {
		$validOrigins[] = $warehouse;	
	    }
	}
	
	$available = array();
	foreach($validOrigins as $origin) {
	    /* @var $origin Ewave_Temando_Model_Warehouse */
	    if($origin->hasProducts($productIds)){
		//whs has stock of all products - return immediately
		return array($origin->getId() => $productIds); 
	    }
	    $available[$origin->getId()] = count($origin->getAvailableProducts($productIds));
	}
	
	arsort($available, SORT_NUMERIC);
	
	$found = array();
	foreach($available as $origin_id => $count) {
	    $origin = Mage::getModel('temando/warehouse')->load($origin_id);
	    $stockedProducts = $origin->getAvailableProducts($productIds);
	    foreach($stockedProducts as $productId)
	    {
		if(!in_array($productId, $found))
		{
		    $return[$origin_id][] = $productId;
		    $found[] = $productId;
		}
	    }
	}

	$unavailable = array_diff($productIds, $found);
	
	//return false if not all products allocated
	if(!empty($unavailable)){
	    return false;
	}
	
	return $return;
    }
    
    /**
     * Returns warehouse ids which this user is allowed to view
     * 
     * @param string|int $userId
     * @return array 
     */
    public function getAllowedWarehouseIds($userId)
    {
	$this->load();
	
	$allowed = array();
	foreach($this->_items as $warehouse) {
	    /* @var $warehouse Ewave_Temando_Model_Warehouse */
	    if($warehouse->isAllowedUser($userId))
		$allowed[] = $warehouse->getId();
	}

	return $allowed;
    }
    
}

