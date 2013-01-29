<?php
/**
 * @method string getOriginId()
 * @method Ewave_Temando_Model_Quote_Address_Rate setOriginId(int $value)
 */

class Ewave_Temando_Model_Quote_Address_Rate extends Mage_Sales_Model_Quote_Address_Rate {
    
    public function importShippingRate(Mage_Shipping_Model_Rate_Result_Abstract $rate)
    {
        if ($rate instanceof Mage_Shipping_Model_Rate_Result_Error) {
            $this
                ->setCode($rate->getCarrier().'_error')
                ->setCarrier($rate->getCarrier())
                ->setCarrierTitle($rate->getCarrierTitle())
                ->setErrorMessage($rate->getErrorMessage())
		->setOriginId($rate->getOriginId())
            ;
        } elseif ($rate instanceof Mage_Shipping_Model_Rate_Result_Method) {
            $this
                ->setCode($rate->getMethod() == 'temando_multi' ? $rate->getCode() : $rate->getCarrier().'_'.$rate->getMethod())
                ->setCarrier($rate->getCarrier())
                ->setCarrierTitle($rate->getCarrierTitle())
                ->setMethod($rate->getMethod())
                ->setMethodTitle($rate->getMethodTitle())
                ->setMethodDescription($rate->getMethodDescription())
                ->setPrice($rate->getPrice())
		->setOriginId($rate->getOriginId())
            ;
        }
        return $this;
    }
    
    
}

