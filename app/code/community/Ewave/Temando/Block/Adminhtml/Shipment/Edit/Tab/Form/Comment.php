<?php

class Ewave_Temando_Block_Adminhtml_Shipment_Edit_Tab_Form_Comment extends Ewave_Temando_Block_Adminhtml_Shipment_Edit_Tab_Abstract
{
    /**
     * Returns formatted shipping instructions for this shipment
     * 
     * @return string 
     */
    public function getShippingInstructions()
    {
	$instructions = $this->getShipment()->getShippingInstructions();
	return $instructions ? htmlspecialchars($instructions) : '<i>no instructions</i>';
    }
    
    /**
     * Returns formatted customer comment for this shipment
     * 
     * @return string 
     */
    public function getCustomerComment()
    {
	$comment = $this->getShipment()->getCustomerComment();
	return $comment ? htmlspecialchars($comment) : '<i>no comment</i>';
    }
}