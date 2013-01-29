<?php

/* @var $this Mage_Eav_Model_Entity_Setup */

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('temando_shipment')}
  ADD `is_split` tinyint(1) NOT NULL DEFAULT 0,
  ADD `combined_rate` longtext NULL
;"); 

$installer->endSetup();