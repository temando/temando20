<?php

/* @var $this Mage_Eav_Model_Entity_Setup */

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('temando_shipment')}
  ADD `customer_comment` varchar(5000) NULL,
  ADD `shipping_instructions` varchar(30) NULL
;"); 

$installer->endSetup();