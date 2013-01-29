<?php

/* @var $this Mage_Eav_Model_Entity_Setup */

$installer = $this;

$installer->startSetup();

//change index of 'booked' shipment status
$installer->run("
ALTER TABLE {$this->getTable('temando_quote')}
  ADD `warehouse_id` int(11) NULL
;"); 


$installer->endSetup();