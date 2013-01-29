<?php

class Ewave_Temando_Model_System_Config_Source_Pixelpercent extends Ewave_Temando_Model_System_Config_Source
{
    
    const PIXELS  = 'px';
    const PERCENT  = '%';
    
    protected function _setupOptions()
    {
        $this->_options = array(
            self::PIXELS  => 'Pixels',
            self::PERCENT  => 'Percent',
        );
    }
    
}
