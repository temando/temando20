<?php

class Ewave_Temando_Block_Adminhtml_Shipment_Edit_Tab_Form
    extends Ewave_Temando_Block_Adminhtml_Shipment_Edit_Tab_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    
    protected $_template = 'ewave/temando/shipment.phtml';

    public function __construct()
    {
        $this->setTemplate($this->_template);
        parent::__construct();
        $this->setTemplate($this->_template);
    }

    public function getTabLabel()
    {
        return $this->__('Create Shipment');
    }

    public function getTabTitle()
    {
        return $this->__('Create Shipment');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
    
}
