<?php


class Ewave_Temando_Block_Onepage_Shipping_Pickup extends Mage_Checkout_Block_Onepage_Shipping {

    public function getConfigData($field)
    {
	return Mage::helper('temando')->getConfigData($field);
    }
    
    public function getMapStyle() {
	$height = $this->getConfigData('pickup/map_height');
	$width = $this->getConfigData('pickup/map_width');
	$unit = $this->getConfigData('pickup/unit');
	
	return "height: $height{$unit};width: $width{$unit}";
    }
}


