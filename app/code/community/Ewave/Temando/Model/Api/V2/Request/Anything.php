<?php

class Ewave_Temando_Model_Api_V2_Request_Anything extends Mage_Core_Model_Abstract
{
    
    /**
     * @var Mage_Catalog_Model_Product
     */
    protected $_product = null;
    
    /**
     * @var Mage_Sales_Model_Order_Item
     */
    protected $_item = null;
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('temando/api_v2_request_anything');
    }
    
    public function setItem($item)
    {
        if ($item instanceof Mage_Sales_Model_Quote_Item || $item instanceof Mage_Sales_Model_Order_Item || $item instanceof Mage_Sales_Model_Quote_Address_Item || $item instanceof Ewave_Temando_Model_Box) {
            $this->_item = $item;
            if ($item instanceof Mage_Sales_Model_Quote_Item || $item instanceof Mage_Sales_Model_Quote_Address_Item || $item instanceof Mage_Sales_Model_Order_Item) {
                $this->_product = Mage::getModel('catalog/product')->load($item->getProductId());
            }
        }
        return $this;
    }
    
    /**
     * Gets the order item for this Anything object.
     *
     * @return Mage_Sales_Model_Order_Item
     */
    public function getItem()
    {
        if ($this->_item) {
            return $this->_item;
        }
        return false;
    }
    
    /**
     * Gets the catalog product for this Anything object.
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if ($this->_product) {
            return $this->_product;
        }
        return false;
    }
    
    public function toRequestArray()
    {
        if (!$this->validate()) {
            return false;
        }
        
	$anything = array();
        
        if ($this->_item instanceof Ewave_Temando_Model_Box) {
            $any_thing = array(
                'class'         => 'General Goods',
                'subclass'      => 'Household Goods',
                'packaging'     => Mage::getModel('temando/system_config_source_shipment_packaging')
                                       ->getOptionLabel($this->_item->getPackaging()),
                'quantity'      => (int)($this->_item->getQty()),
                'distanceMeasurementType'
                                => $this->_item->getMeasureUnit(),
                'weightMeasurementType'
                                => $this->_item->getWeightUnit(),
                'weight'        => $this->_item->getWeight(),
                'length'        => $this->_item->getLength(),
                'width'         => $this->_item->getWidth(),
                'height'  	=> $this->_item->getHeight(),
                'qualifierFreightGeneralFragile'
                                => $this->_item->getFragile() == '1' ? 'Y' : 'N',
                'description'   => $this->_item->getComment()
            );

            if ($this->_item->getWeight() < 1) {
                $any_thing['weight'] = Mage::helper('temando/v2')->getGramsWeight($any_thing);
                $any_thing['weightMeasurementType'] = Ewave_Temando_Model_System_Config_Source_Unit_Weight::GRAMS;
            }
	    
	    $anything[] = $any_thing;
        } else {
            //Mage::helper('temando/v2')->applyTemandoParamsToProductByItem($this->_item, $this->_product);
	    $productPackages = Mage::helper('temando/v2')->getProductPackages($this->_item, $this->_product);
	    
	    switch(Mage::helper('temando')->getConfigData('defaults/consolidation')) {
		
		case Ewave_Temando_Model_System_Config_Source_Packaging_Consolidation::TEMANDO:
		    foreach($productPackages as $package) {
			
			$quantity = (int)($this->_item->getQty() ? $this->_item->getQty() : $this->_item->getQtyOrdered());
			for($i=1; $i<=$quantity; $i++) {
			    
			    $any_thing = array(
				'class'			    => 'General Goods',
				'subclass'		    => 'Household Goods',
				'packaging'		    => Mage::getModel('temando/system_config_source_shipment_packaging')->getOptionLabel($package['packaging']),
				'mode'			    => 'Less than load',
				'packagingOptimisation'	    => 'Y',//$this->is101010($package) ? 'Y' : 'N',
				'distanceMeasurementType'   => Mage::helper('temando/v2')->getConfigData('units/measure'),
				'weightMeasurementType'	    => Mage::helper('temando/v2')->getConfigData('units/weight'),
				'qualifierFreightGeneralFragile' => $package['fragile'] == '1' ? 'Y' : 'N',
				'weight'		    => $package['weight'],
				'length'		    => $package['length'],
				'width'			    => $package['width'],
				'height'		    => $package['height'],
				'quantity'		    => '1',
				'articles'		    => array(
				    'article'   => array('description' => $package['description'], 'sku' => $this->_item->getSku())
				)
			    );
			    $anything[] = $any_thing;
			}
		    }
		    break;
		
		default:
		    foreach($productPackages as $package) {
			$any_thing = array(
			    'class'	=> 'General Goods',
			    'subclass'	=> 'Household Goods',
			    'packaging'	=> Mage::getModel('temando/system_config_source_shipment_packaging')->getOptionLabel($package['packaging']),
			    'quantity'	=> (int)($this->_item->getQty() ? $this->_item->getQty() : $this->_item->getQtyOrdered()),
			    'distanceMeasurementType' => Mage::helper('temando/v2')->getConfigData('units/measure'),
			    'weightMeasurementType' => Mage::helper('temando/v2')->getConfigData('units/weight'),
			    'weight'    => $package['weight'],
			    'length'    => $package['length'],
			    'width'     => $package['width'],
			    'height'  	=> $package['height'],
			    'qualifierFreightGeneralFragile' => $package['fragile'] == '1' ? 'Y' : 'N',
			    'description'   => $package['description']
			);
			if ($any_thing['packaging'] == 'Pallet') {
			    $any_thing['palletType'] = 'Plain';
			    $any_thing['palletNature'] = 'Not Required';
			}

			$anything[] = $any_thing;
		    }
		    break;
	    } 
        }

        // return only after checking empty data of product attributes
        return $anything;
    }
    
    public function validate()
    {
        return $this->_item instanceof Mage_Sales_Model_Quote_Item ||
            $this->_item instanceof Mage_Sales_Model_Order_Item ||
	    $this->_item instanceof Mage_Sales_Model_Quote_Address_Item ||
            $this->_item instanceof Ewave_Temando_Model_Box;
    }
    
    /**
     * Return true if package dimensions are 10x10x10
     * 
     * @param array $package The package to check
     * @return boolean 
     */
    public function is101010($package) {
	if($package['width'] == 10 && $package['length'] == 10 && $package['height'] == 10) {
	    return true;
	}
	return false;
    }

}
