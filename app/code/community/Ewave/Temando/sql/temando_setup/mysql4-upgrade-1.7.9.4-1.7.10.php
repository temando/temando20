<?php

/* @var $this Mage_Eav_Model_Entity_Setup */

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('temando_shipment')}
  ADD `customer_selected_delivery_options` varchar(500) NULL after `customer_selected_options`
;"); 

$installer->endSetup();