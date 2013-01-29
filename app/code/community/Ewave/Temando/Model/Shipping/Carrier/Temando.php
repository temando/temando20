<?php

class Ewave_Temando_Model_Shipping_Carrier_Temando extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{
    
    const ERR_INVALID_COUNTRY = 'To and From addresses must be within Australia';
    const ERR_INVALID_DEST    = 'Please enter a delivery address to view available shipping methods';
    const ERR_NO_METHODS      = 'No shipping methods available';
    
    protected $_code = 'temando';

    protected static $_errors_map = array(
        "The 'destinationCountry', 'destinationCode' and 'destinationSuburb' elements (within the 'Anywhere' type) do not contain valid values.  These values must match with the predefined settings in the Temando system."
                => "Invalid suburb / postcode combination."
    );
    
    /**
     * @var Mage_Shipping_Model_Rate_Request
     */
    protected $_rate_request;
    
    /**
     * @var Ewave_Temando_Helper_Data
     */
    protected $_helper;
    
    protected $_pricing_method;
    protected $_username;
    protected $_password;
    protected $_sandbox;
    
    protected $_origin;
    protected $_origins;
    
    public function isTrackingAvailable()
    {
        return true;
    }
    
    
    public function __construct()
    {
        parent::__construct();
        $this->_helper = Mage::helper('temando');
        
        $this->_pricing_method = $this->getConfigData('pricing/method');
        $this->_username = $this->getConfigData('general/username');
        $this->_password = $this->getConfigData('general/password');
        $this->_sandbox = $this->getConfigData('general/sandbox');
    }
    
    /**
     * Checks if the to and from addresses are within Australia.
     *
     * @return boolean
     */
    protected function _isInAustralia()
    {
        $origCountry = $this->getConfigData('origin/country');
        return ($origCountry == "AU" && $this->_rate_request->getDestCountryId() == "AU");
    }
    
    /**
     * Creates the flat rate method, with the price set in the config. An
     * optional parameter allows the price to be overridden.
     *
     * @return Mage_Shipping_Model_Rate_Result_Method
     */
    protected function _getFlatRateMethod($price = false, $free = false, $origin_id = null)
    {
        if (true === $this->_rate_request->getFreeShipping()) {
            $price = 0;
            $free = true;
        }

        if ($price === false) {
            $cost = $this->getConfigData('pricing/shipping_fee');
        } else {
            $cost = $price;
        }


        $title = $this->getConfigData('options/shown_name');
        if ($this->getConfigData('options/show_name_time')) {
            $title = $free ? 'Free Shipping' : 'Flat Rate';
        }

        $method = Mage::getModel('shipping/rate_result_method')
            ->setCarrier($this->_code)
            ->setCarrierTitle($this->getConfigData('carriers/temando/title'))
            ->setMethodTitle($free ? 'Free Shipping' : $title)
            ->setMethod($free ? '10000' : '10001')
            ->setPrice($price)
            ->setCost($cost)
	    ->setOriginId($origin_id);
            
        return $method;
    }

    protected function _getErrorMethod($errorText, $originId = null)
    {
        $error = Mage::getModel('shipping/rate_result_error');
        $error->setCarrier('temando');
        $error->setCarrierTitle($this->getConfigData('carriers/temando/title'));
	$error->setOriginId($originId);
        if (isset(self::$_errors_map[$errorText])) {
            $errorText = self::$_errors_map[$errorText];
        }

        $error->setErrorMessage($errorText);

        return $error;
    }
    
    /**
     * Creates a rate method based on a Temando API quote.
     *
     * @param Mage_Shipping_Model_Rate_Result_Method the quote from the
     * Temando API.
     *
     * @return Mage_Shipping_Model_Rate_Result_Method
     */
    protected function _getRateMethodFromQuote($quote, $method_id)
    {
        $carrier = $quote->getCarrier();
        $title = $this->getConfigData('options/shown_name');
        if (Mage::getSingleton('admin/session')->isLoggedIn() || $this->getConfigData('options/show_name_time')) {
            $title = $quote->getDescription($this->getConfigData('options/show_carrier_names'));
        }

        $method = Mage::getModel('shipping/rate_result_method')
            ->setCarrier($this->_code)
            ->setCarrierTitle($this->getConfigData('carriers/temando/title'))
            ->setMethodTitle($title)
            ->setMethod($method_id)
            ->setPrice($price)
            ->setCost($quote->getTotalPrice())
	    ->setOriginId($quote->getWarehouseId());
        
        return $method;
    }
    
    /**
     * Creates a string describing the applicable elements of a rate request.
     *
     * This is used to determine if the quotes fetched last time should be
     * refreshed, or if they can remain valid.
     *
     * @param Mage_Shipping_Model_Rate_Request $rate_request
     *
     * @return boolean
     */
    protected function _createRequestString(Mage_Shipping_Model_Rate_Request $rate_request, $pickup, $deliveryOptions)
    {
        $request_string = Mage::getModel('checkout/session')
            ->getQuote()->getId() . '|';
        foreach ($rate_request->getAllItems() as $item) {
            $request_string .= $item->getProductId() . 'x' . $item->getQty();
        }
	
	if(!is_array($deliveryOptions)) {$deliveryOptions = array();}
        
        $request_string .= '|' . $rate_request->getDestCity();
        $request_string .= '|' . $rate_request->getDestCountryId();
        $request_string .= '|' . $rate_request->getDestPostcode();
        $request_string .= '|' . $rate_request->getDestStreet();
	$request_string .= '|' . $pickup;
	$request_string .= '|' . implode(',', $deliveryOptions);
        return $request_string;
    }
    
    public function collectRates(Mage_Shipping_Model_Rate_Request $rate_request)
    {
	$isProductPage = (("etemando" == Mage::app()->getRequest()->getModuleName()) && ("pcs" == Mage::app()->getRequest()->getControllerName()));
	$isCartPage = (("checkout" == Mage::app()->getRequest()->getModuleName()) && ("cart" == Mage::app()->getRequest()->getControllerName()));
	
	$isPickup = Mage::app()->getRequest()->getParam('shipping_is_pickup', false);
	$pickupLoc = Mage::app()->getRequest()->getParam('pickup-location', '');
	
	if(("checkout" == Mage::app()->getRequest()->getModuleName() && "saveShipping" == Mage::app()->getRequest()->getActionName()) ||
	    "onestepcheckout" == Mage::app()->getRequest()->getModuleName() && Mage::app()->getRequest()->getPost('delivery_option_click')) 
	{
	    $delivery_options = Mage::app()->getRequest()->getParam('delivery_options');
	    if(!$delivery_options || !is_array($delivery_options)) { $delivery_options = array(); }
	    Mage::getSingleton('checkout/session')->setData('selected_delivery_options', $delivery_options);
	} else {
	    $delivery_options = Mage::getSingleton('checkout/session')->getData('selected_delivery_options');
	    if(!$delivery_options) {
		$delivery_options = array();
	    }
	}
	
        $this->_rate_request = $rate_request;
        $result = Mage::getModel('shipping/rate_result');
	/* @var $result Mage_Shipping_Model_Rate_Result */
	
	if (!$this->_isInAustralia()) { return $result->setError(self::ERR_INVALID_COUNTRY); }
	
	//OneStepCheckout inserts '-' in city/pcode if no default configured
        if (!$rate_request->getDestCountryId() || !$rate_request->getDestPostcode() || !$rate_request->getDestCity() ||
		$rate_request->getDestPostcode() == '-' || $rate_request->getDestCity() == '-') { 
	    return $this->_getErrorMethod(self::ERR_INVALID_DEST);
        }
	
	if(Mage::helper('temando')->isVersion2()) {
	    $unavailable = array();
	    $this->_origins = Mage::helper('temando/v2')->getDynamicOrigins(
		$rate_request->getDestPostcode(),
		Mage::app()->getStore()->getId(),
		$rate_request->getAllItems(),
		$unavailable
	    );
	    
	    if(!$this->_origins) {
		Mage::getSingleton('checkout/session')->unsetData('origins');
		throw new Exception('Unable to fulfil this order due to unavailable products or warehouse misconfiguration');
	    }
	    //save origins info for checkout template
	    Mage::getSingleton('checkout/session')->setOrigins($this->_origins);
	}
	
        // Check all items are with free ship
        $has_paid = false;
        foreach ($rate_request->getAllItems() as $item) {
            if ($item->getProduct()->isVirtual() || $item->getParentItem()) { continue; }
            if ($item->getFreeShipping()) { continue; }

            $has_paid = true;
        }

	//free shipping set or all items have free shipping
        if (!$has_paid || $this->getConfigData('free_shipping_enable') && 
		$this->isEligibleForFreeShipping($rate_request->getAllItems(), $this->getConfigData('free_shipping_subtotal')))
	{
            $this->_pricing_method = 'free';
        }

	//prepare extras
        $insurance = Mage::getModel('temando/option_insurance')->setSetting(Mage::getStoreConfig('temando/insurance/status'));
        $carbon = Mage::getModel('temando/option_carbonoffset')->setSetting(Mage::getStoreConfig('temando/carbon/status'));
	if($isPickup) {
	    $pickup = Mage::getModel('temando/option_pickup')->setForcedValue(Ewave_Temando_Model_Option_Boolean::YES);
	}
        
        if ($isProductPage || $isCartPage) 
	{
            if (!in_array($insurance->getForcedValue(), array(Ewave_Temando_Model_Option_Boolean::YES, Ewave_Temando_Model_Option_Boolean::NO))) {
                $insurance->setForcedValue(Ewave_Temando_Model_Option_Boolean::NO);
            }

            if (!in_array($carbon->getForcedValue(), array(Ewave_Temando_Model_Option_Boolean::YES, Ewave_Temando_Model_Option_Boolean::NO))) {
                $carbon->setForcedValue(Ewave_Temando_Model_Option_Boolean::NO);
            }
        }
        /* @var Ewave_Temando_Model_Options $options */
        $options = Mage::getModel('temando/options')->addItem($insurance)->addItem($carbon);
	if(isset($pickup)) {$options->addItem($pickup);}
		
	//get magento quote id
        $magento_quote_id = Mage::getSingleton('checkout/session')->getQuoteId();
        if (!$magento_quote_id && Mage::getSingleton('admin/session')->isLoggedIn() && Mage::getSingleton('adminhtml/session_quote')->getQuote()) {
            $magento_quote_id = Mage::getSingleton('adminhtml/session_quote')->getQuote()->getId();
        }
        if ($isProductPage){ $magento_quote_id = 100000000 + mt_rand(0, 100000); }

	//save current extras and delivery options
        if (is_null(Mage::registry('temando_current_options'))) {
            Mage::register('temando_current_options', $options);
        }
	
	//validate request data
	if(!$this->getConfigData('checkout/allow_pobox') && Mage::helper('temando')->isStreetWithPO($rate_request->getDestStreet())) {
	    foreach($this->_origins as $id => $products) {
		return $this->_getErrorMethod($this->getConfigData('checkout/allow_pobox_message'), $id);
	    }
	}
		
	//static methods - return immediately
        switch ($this->_pricing_method) {
            case 'flat': return $result->append($this->_getFlatRateMethod());
            case 'free': return $result->append($this->_getFlatRateMethod('0.00', true));
        }
	
	//GET SHIPPING QUOTES
	$quotes = array();
	
	//check if request same as previous
	$last_request = Mage::getSingleton('checkout/session')->getData('temando_request_string');
	if ($last_request == $this->_createRequestString($rate_request, $pickupLoc, $delivery_options)) {
            // load existing quotes from DB instead
            $saved_quotes = Mage::getModel('temando/quote')->getCollection()
                ->addFieldToFilter('magento_quote_id', Mage::getSingleton('checkout/session')->getData('temando_quote_id'));
	    foreach($saved_quotes->getItems() as $saved_quote) {
		$quotes[$saved_quote->getWarehouseId()][] = $saved_quote;
	    }
        } else {
	    try {		
		//get quotes for each origin location
		$first = true;
		foreach($this->_origins as $origin_id => $productIds) {

		    $originItems = $this->splitRequestItems($rate_request->getAllItems(), $productIds);
		    if($this->isFreeShipping($originItems)) {
			continue;
		    } 

		    $request = Mage::getModel('temando/api_v2_request');
		    $this->_origin = Mage::getModel('temando/warehouse')->load($origin_id);
		    $desc = $this->_origin->getName();

		    $request
			->setUsername($this->getConfigData('general/username'))
			->setPassword($this->getConfigData('general/password'))
			->setSandbox($this->getConfigData('general/sandbox'))
			->setMagentoQuoteId($magento_quote_id)
			->setOrigin(
			    $this->_origin->getCountry(),
			    $this->_origin->getPostcode(),
			    $this->_origin->getCity(),
			    $this->_origin->getType() ? $this->_origin->getType() : $this->_origin->getLocationType(),
			    $desc)
			->setDestination(
			    $rate_request->getDestCountryId(),
			    $rate_request->getDestPostcode(),
			    $rate_request->getDestCity(),
			    $rate_request->getDestStreet(),
			    $isPickup,
			    $pickupLoc)
			->setItems($this->splitRequestItems($rate_request->getAllItems(), $productIds))
			->setReady()
			->setKeepQuotes($first ? false : true)
			->setDeliveryOptions(Mage::getSingleton('checkout/session')->getData('selected_delivery_options'))
			->setAllowedCarriers($this->getAllowedMethods());

		    $shipQuotes = $request->getQuotes($origin_id);
		    if($shipQuotes) {
			/*@var $shipQuotes Ewave_Temando_Model_Mysql4_Quote_Collection */
			$quotes[$origin_id] = $shipQuotes->getItems();
		    }
		    $first = false;
		}

	    } catch (Exception $ex) {
		switch(Mage::helper('temando')->getConfigData('pricing/error_process')) {
		    case Ewave_Temando_Model_System_Config_Source_Errorprocess::VIEW:
			return $this->_getErrorMethod($ex->getMessage());
			return $result->append($this->_getErrorMethod($ex->getMessage()));
			break;
		    case Ewave_Temando_Model_System_Config_Source_Errorprocess::CUST:
			return $this->_getErrorMethod(Mage::helper('temando')->getConfigData('pricing/error_message'));
			return $result->append($this->_getErrorMethod(Mage::helper('temando')->getConfigData('pricing/error_message')));
			break;
		    case Ewave_Temando_Model_System_Config_Source_Errorprocess::FLAT:
			return $result->append($this->_getFlatRateMethod());
			break;
		}
	    }
	}
        
	// save quotes for use in the observer
        Mage::getSingleton('checkout/session')->setData('temando_quote_id', $magento_quote_id);
	
	// get shipping methods
	$data = Mage::app()->getRequest()->getParams();
	if($isProductPage && isset($data['product_id'])) {
	    $product = Mage::getModel('catalog/product')->load($data['product_id']);

	    if($product->getId()) {
		$subtotal = $product->getPrice() * $data['qty'];
	    }
	    $items = $data['qty'];
	} else {
	    $subtotal = $rate_request->getOrderSubtotal() ? $rate_request->getOrderSubtotal() : $rate_request->getPackageValue(); 
	    $items = $rate_request->getOrderTotalQty() ? $rate_request->getOrderTotalQty() : $rate_request->getPackageQty();
	}

	$engine = Mage::getModel('temando/hybrid');
	$engine->setIsPickup($isPickup);
	$engine->loadRules($rate_request->getPackageWeight(), $subtotal, $items, $rate_request->getDestPostcode());		

	$methods = $err = array();
	foreach($quotes as $origin_id => $qts) {
	    $shipMethods = $engine->getShippingMethods($err, $options, $qts, $origin_id);
	    if(is_array($shipMethods) && !empty($shipMethods)) {
		$methods = array_merge($methods, $shipMethods);
	    }
	}
	if(!empty($err)) {
	    switch(Mage::helper('temando')->getConfigData('pricing/error_process')) {
		case Ewave_Temando_Model_System_Config_Source_Errorprocess::VIEW:
		    return $this->_getErrorMethod($err);
		    return $result->append($this->_getErrorMethod($err));
		    break;
		case Ewave_Temando_Model_System_Config_Source_Errorprocess::CUST:
		    return $this->_getErrorMethod(Mage::helper('temando')->getConfigData('pricing/error_message'));
		    return $result->append($this->_getErrorMethod(Mage::helper('temando')->getConfigData('pricing/error_message')));
		    break;
		case Ewave_Temando_Model_System_Config_Source_Errorprocess::FLAT:
		    return $result->append($this->_getFlatRateMethod());
		    break;
	    }
	} else {
	    foreach($methods as $method) {
		$result->append($method);
	    }
	}

	
	//add free shipping method if all shipment items are free (per origin)
	if(count($this->_origins) > 1) {
	    foreach($this->_origins as $id => $pids)
	    {
		$originItems = $this->splitRequestItems($rate_request->getAllItems(), $pids);
		if($this->isFreeShipping($originItems)) {
		    $result->append($this->_getFlatRateMethod(0, true, $id));
		    continue;
		} 
	    }
	}

        Mage::getSingleton('checkout/session')->setData('temando_request_string', $this->_createRequestString($this->_rate_request, $pickupLoc, $delivery_options));
        return $result;
    }

    public function getAllowedMethods()
    {
        return explode(',', Mage::getStoreConfig('carriers/temando/allowed_methods'));
    }

    public function getTrackingInfo($tracking_number)
    {
        $api = Mage::getModel('temando/api_client');
        $api->connect(
            Mage::helper('temando')->getConfigData('general/username'),
            Mage::helper('temando')->getConfigData('general/password'),
            Mage::helper('temando')->getConfigData('general/sandbox'));

        $_t = explode('Request Id: ', $tracking_number);
        if (isset($_t[1])) {
            $tracking_number = $_t[1];
        }

        $status = $api->getRequest(array('requestId' => $tracking_number));
        
        $result = Mage::getModel('shipping/tracking_result_abstract')
            ->setTracking($tracking_number);
        /* @var $result Mage_Shipping_Model_Tracking_Result_Abstract */
        if ($status && $status->request->quotes && $status->request->quotes->quote) {
            if (isset($status->request->quotes->quote->carrier->companyName)) {
                $result->setCarrierTitle($status->request->quotes->quote->carrier->companyName);
            }

            if (isset($status->request->quotes->quote->trackingStatus)) {
                $result->setStatus($status->request->quotes->quote->trackingStatus);
            } else {
                $result->setStatus($this->_helper->__('Unavailable'));
            }
            
            $text = '';
            if (isset($status->request->quotes->quote->trackingFurtherDetails)) {
                $text .= $status->request->quotes->quote->trackingFurtherDetails;
            }
            if (isset($status->request->quotes->quote->trackingLastChecked)) {
                $text .= 'Last Update: ' . date('Y-m-d h:ia', strtotime($status->request->quotes->quote->trackingLastChecked));
            }
            
            if ($text) {
                $result->setTrackSummary($text);
            }
        } else {
            $result->setErrorMessage($this->_helper->__('An error occurred while fetching the shipment status.'));
        }
        
        return $result;
    }
    
    public function getConfigData($field)
    {
        $parent = parent::getConfigData($field);
        return $parent !== null ? $parent : $this->_helper->getConfigData($field);
    }
    
    private function isEligibleForFreeShipping($items, $minimum)
    {
	if(empty($minimum))
	    return false;
	
	$goods_value = 0;
        
        foreach ($items as $item) {
	    
	    if ($item->getParentItem() || $item->getIsVirtual()) {
                // do not add child products or virtual items
                continue;
            }

            if ($item->getProduct() && $item->getProduct()->isVirtual()) {
                // do not add virtual product
                continue;
            }

            if ($item->getFreeShipping()) {
                continue;
            }
	    
            $value = $item->getValue();
            if (!$value) {
                $value = $item->getRowTotalInclTax();
            }
            if (!$value) {
                $value = $item->getRowTotal();
            }	    
            if (!$value) {
                $qty = $item->getQty();
                if (!$qty) {
                    $qty = $item->getQtyOrdered();
                }
                $value = $item->getPrice() * $qty;
            }
	    
            $goods_value += $value;
        }
	
	if($goods_value >= (float)$minimum)
	    return true;
	
	return false;
    }    
   
    
    public function getCode()
    {
        return $this->_code;
    }

    public function isStateProvinceRequired()
    {
        return true;
    }

    public function isCityRequired()
    {
        return true;
    }

    /**
     * Determine whether zip-code is required for the country of destination
     *
     * @param string|null $countryId
     *
     * @return bool
     */
    public function isZipCodeRequired($countryId = null)
    {
        return true;
    }
    
    /**
     * Splits request items by origin
     * 
     * @param type $allItems
     * @param type $productIds
     * @return type 
     */
    public function splitRequestItems($allItems, $productIds)
    {
	$return = array();
	foreach($allItems as $item) {
	    if($item->getParentItem() || $item->getIsVirtual())
		continue;
	    if ($item->getProduct() && $item->getProduct()->isVirtual()) {
                continue;
            }
	    $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $item->getSku());
	    if(in_array($product->getId(), $productIds)) {
		$return[] = $item;
	    }
	}
	return $return;
    }
    
    public function isFreeShipping($items)
    {
	foreach($items as $item) {
	    if($item->getParentItem() || $item->getIsVirtual())
		continue;
	    if($item->getProduct() && $item->getProduct()->isVirtual()) {
                continue;
            }
	    if(!$item->getFreeShipping()) {
		return false;
	    }
	}
	return true;
    }
	    
    
    
}
