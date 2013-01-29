var Shipment = Class.create();

Shipment.prototype = {
    initialize: function(stockLocations, originDropdown){
	this.stockLocations = eval(stockLocations);
	this.qtyInputElements = $$('[id^="qty_to_ship_"]');
	this.originDropdown = $(originDropdown);
	this.originDropdown.observe("change", this.warehouseDropdownChangeListener);	
    },
    warehouseDropdownChangeListener: function() {
	shipment.disableAllQtyInputElements()
	var originId = shipment.originDropdown.value;
	shipment.qtyInputElements.each(function(el, index) {
	    if(shipment.hasStock(originId, el.id)) {
		$(el).disabled = false;
	    }
	});
    },
    hasStock: function(originId, elementId) {
	var ret = false;
	shipment.stockLocations.each(function(loc, index) {
	    if(loc.element_id == elementId && loc.id == originId) {
		ret = true;
	    }
	});
	return ret;
    },
    disableAllQtyInputElements: function() {
	shipment.qtyInputElements.each(function(el, index) {
	    $(el).disabled = true;
	});
    },
    validate: function() {
	return true;
    }
}
