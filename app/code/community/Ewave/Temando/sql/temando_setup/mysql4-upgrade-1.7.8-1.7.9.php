<?php
/**
 * SPLIT SHIPMENTS SUPPORT - MOVE TEMANDO TO MAGE SHIP FLOW AND ALLOW
 * COMBINATION WITH ANY OTHER SHIPPING METHOD 
 */


/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = new Mage_Sales_Model_Entity_Setup('sales_setup');
$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('sales/shipment'), 'is_temando', array(
        'type'    => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
	'comment' => 'Shipped with Temando',
	'default' => '0'
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/shipment'), 'temando_label_document', array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
	'comment' => 'Temando Shipping Label Document'
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/shipment'), 'temando_label_document_type', array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
	'length'    => '32',
	'comment' => 'Temando Shipping Label Document Type'
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/shipment'), 'temando_consignment_document', array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
	'comment' => 'Temando Consignment Document'
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/shipment'), 'temando_consignment_document_type', array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
	'length'  => '32',
	'comment' => 'Temando Consignment Document Type'
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/shipment'), 'temando_shipment_origin', array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
	'comment' => 'Shipment Origin Location',
	'length'  => '255'
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/shipment'), 'temando_shipment_ready_date', array(
        'type'    => Varien_Db_Ddl_Table::TYPE_DATE,
	'comment' => 'Temando Shipment Ready Date'
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/shipment'), 'temando_shipment_ready_time', array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
	'length'    => '2',
	'comment' => 'Temando Shipment Ready Time'
    ));
$installer->endSetup();