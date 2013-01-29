<?php

/**
 * Description of View
 *
 * @author martin
 */
class Ewave_Temando_Block_Adminhtml_Sales_Order_Shipment_Info extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ewave/temando/sales/order/shipment/info.phtml');
    } 
    
    /**
     * Retrieve shipment model instance
     *
     * @return Mage_Sales_Model_Order_Shipment
     */
    public function getShipment()
    {
        return Mage::registry('current_shipment');
    }
}


