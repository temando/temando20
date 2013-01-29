<?php

class Ewave_Temando_Model_System_Config_Source_Shipment_Status extends Ewave_Temando_Model_System_Config_Source
{
    
    const PENDING =     '0';
    const PART_BOOKED = '1';
    const BOOKED =      '2';
    const CANCELLED =   '3';
    
    protected function _setupOptions()
    {
        $this->_options = array(
            self::PENDING     => Mage::helper('temando')->__('Pending'),
	    self::PART_BOOKED => Mage::helper('temando')->__('Partially Booked'),
            self::BOOKED      => Mage::helper('temando')->__('Booked'),
            self::CANCELLED   => Mage::helper('temando')->__('Cancelled'),
        );
    }
    
}
