EbayListingOtherGridHandler = Class.create(ListingOtherGridHandler, {

    // ---------------------------------------

    tryToMove: function(listingId)
    {
        this.movingHandler.submit(listingId, this.onSuccess)
    },

    onSuccess: function(listingId)
    {
        setLocation(M2ePro.url.get('adminhtml_ebay_listing_categorySettings/index', {
                listing_id: listingId,
            })
        );
    },

    // ---------------------------------------

    getComponent: function()
    {
        return 'ebay';
    },

    // ---------------------------------------

    getSelectedItemsParts: function()
    {
        var selectedProductsArray = this.getSelectedProductsArray();

        if (this.getSelectedProductsString() == '' || selectedProductsArray.length == 0) {
            return [];
        }

        var maxProductsInPart = this.getMaxProductsInPart();

        var result = [];
        for (var i=0;i<selectedProductsArray.length;i++) {
            if (result.length == 0 || result[result.length-1].length == maxProductsInPart) {
                result[result.length] = [];
            }
            result[result.length-1][result[result.length-1].length] = selectedProductsArray[i];
        }

        return result;
    },

    // ---------------------------------------

    getMaxProductsInPart: function()
    {
        return 10;
    }

    // ---------------------------------------
});