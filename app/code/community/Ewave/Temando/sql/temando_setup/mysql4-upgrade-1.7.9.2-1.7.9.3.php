<?php
/**
 * SPLIT SHIPMENTS SUPPORT - ADD WAREHOUSE ID TO SHIPPING RATE 
 */


/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = new Mage_Sales_Model_Entity_Setup('sales_setup');
$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('sales/quote_address_shipping_rate'), 'origin_id', array(
        'type'    => Varien_Db_Ddl_Table::TYPE_INTEGER,
	'comment' => 'Temando Origin ID',
	'default' => null
    ));

$installer->endSetup();