<?php

class Ewave_Temando_Block_Adminhtml_Shipment_Edit_Tab_Shipments 
    extends Mage_Adminhtml_Block_Widget_Grid 
	implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    /**
     * Set grid params
     *
     */
    public function __construct() {

	parent::__construct();
	$this->setId('related_shipments_grid');
	$this->setUseAjax(true);
    }

    
    protected function _getShipment() {
	return Mage::registry('temando_shipment_data');
    }

    
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('sales/order_shipment_grid_collection')
            ->addFieldToSelect('entity_id')
            ->addFieldToSelect('created_at')
            ->addFieldToSelect('increment_id')
            ->addFieldToSelect('total_qty')
            ->addFieldToSelect('shipping_name')
            ->setOrderFilter($this->getOrder())
        ;
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header' => Mage::helper('sales')->__('Shipment #'),
            'index' => 'increment_id',
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Date Shipped'),
            'index' => 'created_at',
            'type' => 'datetime',
        ));

        $this->addColumn('total_qty', array(
            'header' => Mage::helper('sales')->__('Total Qty'),
            'index' => 'total_qty',
            'type'  => 'number',
        ));
	
	$this->addColumn('action', array(
	    'header'	=> Mage::helper('temando')->__('Action'),
	    'width'	=> '140px',
	    'type'	=> 'action',
	    'getter'	=> 'getId',
	    'actions'	=> array(
		array(
		    'caption'	=> Mage::helper('temando')->__('View Consignment'),
		    'url'	=> array(
			'base'	=> '*/*/consignment',
			'params'    => array('id' => $this->_getShipment()->getId()),
		    ),
		    'field' => 'shipment_id'
		),
	    ),
	    'filter'	=> false,
	    'sortable'	=> false,
	));

        return parent::_prepareColumns();
    }

    /**
     * Retrieve order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->_getShipment()->getOrder();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl(
            'adminhtml/sales_order_shipment/view',
            array(
                'shipment_id'=> $row->getId(),
                'order_id'  => $this->getOrder()->getId()
             ));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/shipments', array('_current' => true));
    }
    
    public function getTabLabel()
    {
        return $this->__('Shipments');
    }

    public function getTabTitle()
    {
        return $this->__('Shipments');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

}
