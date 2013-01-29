<?php

/* @var $this Mage_Eav_Model_Entity_Setup */

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('temando_quote')}
  ADD `pickup_total_price` decimal(12,4) NOT NULL AFTER `carbon_total_price`
;");

$installer->run("
ALTER TABLE {$this->getTable('temando_shipment')}
  ADD `pickup_location` VARCHAR(255) NULL,
  ADD `available_pickup_locations` TEXT NULL
;");

$installer->endSetup();