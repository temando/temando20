<?php

class Ewave_Temando_Model_System_Config_Source_Pricing extends Ewave_Temando_Model_System_Config_Source
{
    
    const FREE                         = 'free';
    const FLAT_RATE                    = 'flat';
    const RULE_ENGINE		       = 'hybrid';
    
    protected function _setupOptions()
    {
        $this->_options = array(
            self::FREE                         => 'Free Shipping',
            self::FLAT_RATE                    => 'Fixed Price / Flat Rate',
	    self::RULE_ENGINE		       => 'Rule Engine',
        );
    }
    
}
