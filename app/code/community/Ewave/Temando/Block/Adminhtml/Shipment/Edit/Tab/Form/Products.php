<?php

class Ewave_Temando_Block_Adminhtml_Shipment_Edit_Tab_Form_Products extends Ewave_Temando_Block_Adminhtml_Shipment_Edit_Tab_Abstract
{
    
    /**
     * Locations holding stock for individual products
     * @var array 
     */
    protected $_stockLocations = null;
	
    /**
     * Gets a Magento catalog product belonging to a Magento order item.
     *
     * @param Mage_Sales_Model_Order_Item $item the Magento order item
     * @return Mage_Catalog_Model_Product
     */
    public function getProductFromItem(Mage_Sales_Model_Order_Item $item)
    {
        return Mage::getModel('catalog/product')
            ->load($item->getProductId());
    }
    
    /**
     * Returns locations holding stock of a product
     * !important: currently support for single and configurable products
     * 
     * @param int $productId
     * @return array of strings Warehouse Names 
     */
    public function getStockLocations($productId)
    {
	if($this->_stockLocations !== null) {
	    return $this->_stockLocations;
	}
	
	$return = array();
	$warehouses = Mage::getModel('temando/warehouse')->getCollection();
	
	foreach($warehouses->getItems() as $warehouse) {
	    if($warehouse->hasProduct($productId)) {
		$warehouse->setElementId('qty_to_ship_'.$productId);
		$return[] = $warehouse;
	    }
	}
	
	return $return;
    }
    
    /**
     * Returns list of all locations holding available product stock ready for JSON conversion
     * 
     * @param Mage_Sales_Model_Order_Item $item
     * @param Mage_Catalog_Model_Product $product
     * @return array Json encoded warehouse locations 
     */
    public function getStockLocationsJson($productId)
    {
	$stockLocationsJson = array();
	
	$locations = $this->getStockLocations($productId);
	
	foreach($locations as $warehouse) {
	    $warehouse->setElementId('qty_to_ship_'.$productId);
	    $stockLocationsJson[] = $warehouse->toArray();
	}
	return $stockLocationsJson;
    }
    
    /**
     * Returns real product ID for selected configurable or simple product
     * @param Mage_Sales_Model_Order_Item $item
     * @param Mage_Catalog_Model_Product $product
     * @return int Product ID 
     */
    public function getProductId($item, $product) 
    {
	$productId = null;
	if ($product->getTypeId() == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
	    //try getting from selected simple product first
	    $simpleProduct = Mage::helper('temando/v2')->getSelectedSimpleFromConfigurable($product, $item);
	    $productId = $simpleProduct->getEntityId();
	}
	
	if(!$productId) {
	    $productId = $item->getProductId();
	}
	
	return $productId;
    }	    
	    
    
}
