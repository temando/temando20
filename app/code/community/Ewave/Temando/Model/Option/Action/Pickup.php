<?php

class Ewave_Temando_Model_Option_Action_Pickup extends Ewave_Temando_Model_Option_Action_Abstract
{
    
    public function apply(&$quote)
    {
        /* @var $quote Ewave_Temando_Model_Quote */
        $price = $quote->getTotalPrice();
        $pickup_price = $quote->getPickupTotalPrice();
        
        $quote->setTotalPrice($price + $pickup_price);
    }
    
}
