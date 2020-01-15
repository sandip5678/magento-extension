WalmartListingProductEditChannelDataHandler = Class.create();
WalmartListingProductEditChannelDataHandler.prototype = Object.extend(new CommonHandler(), {

    gridHandler: null,

    editIdentifierPopup: null,
    editSkuPopup: null,

    frameObj: null,

    // ---------------------------------------

    initialize: function(gridHandler)
    {
        this.gridHandler = gridHandler;

        Validation.add('M2ePro-validate-walmart-sku', M2ePro.translator.translate('SKU contains the special characters that are not allowed by Walmart.'), function(value, el) {

            if (!el.up('tr').visible()) {
                return true;
            }

            return !value.match(/[.\s-]+/g);
        });
    },

    //########################################

    showIdentifiersPopup: function (productId)
    {
        if (window.top !== window) {
            window.top.ListingGridHandlerObj.editChannelDataHandler.frameObj = window;
            window.top.ListingGridHandlerObj.editChannelDataHandler.showIdentifiersPopup(productId);

            return;
        }

        var self = this;
        new Ajax.Request(M2ePro.url.get('adminhtml_walmart_listing/getEditIdentifiersPopup'), {
            method: 'get',
            onSuccess: function(transport) {

                var responseData = transport.responseText;

                self.editIdentifierPopup = self.showPopup(responseData, 'edit_identifiers_popup', {
                    title: M2ePro.translator.translate('Edit Product ID'),
                    top: 200,
                    width: 450,
                    height: 400
                });
                self.editIdentifierPopup.productId = productId;
            }
        });
    },

    editIdentifier: function()
    {
        var self = this,
            identifier = $('identifier'),
            identifierName = identifier.selectedOptions[0].textContent;

        if (!self.validateForm()) {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_walmart_listing/editIdentifier'), {
            method: 'post',
            parameters: {
                product_id: self.editIdentifierPopup.productId,
                type:       identifier.value,
                value:      $('new-identifier-value').value
            },
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                var response = transport.responseText.evalJSON();

                self.getAppropriateMagentoMessageObj().clearAll();
                if (response.message) {
                    self.getAppropriateMagentoMessageObj().addError(response.message);
                }

                if (!response.result) {
                    return;
                }

                self.cancelEditIdentifier();

                self.getAppropriateMagentoMessageObj().addSuccess(
                    M2ePro.translator.translate("Updating "+identifierName+" has successfully submitted to be processed.")
                );
                self.getAppropriateGridObj().reload();
            }
        });
    },

    cancelEditIdentifier: function()
    {
        var self = this;

        if (typeof self.editIdentifierPopup == null) {
            return;
        }

        self.editIdentifierPopup.close();
        self.editIdentifierPopup = null;
    },

    // ---------------------------------------

    showEditSkuPopup: function (productId)
    {
        if (window.top !== window) {
            window.top.ListingGridHandlerObj.editChannelDataHandler.frameObj = window;
            window.top.ListingGridHandlerObj.editChannelDataHandler.showEditSkuPopup(productId);

            return;
        }

        var self = this;
        new Ajax.Request(M2ePro.url.get('adminhtml_walmart_listing/getEditSkuPopup'), {
            method: 'get',
            onSuccess: function(transport) {

                var responseData = transport.responseText;

                self.editSkuPopup = self.showPopup(responseData, 'edit_sku_popup', {
                    title: M2ePro.translator.translate('Edit SKU'),
                    top: 200,
                    width: 450,
                    height: 400
                });
                self.editSkuPopup.productId = productId;
            }
        });
    },

    editSku: function ()
    {
        var self = this;

        if (!self.validateForm()) {
            return;
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_walmart_listing/editSku'), {
            method: 'post',
            parameters: {
                product_id: self.editSkuPopup.productId,
                value:      $('new-sku-value').value
            },
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                var response = transport.responseText.evalJSON();

                self.getAppropriateMagentoMessageObj().clearAll();
                if (response.message) {
                    self.getAppropriateMagentoMessageObj().addError(response.message);
                }

                if (!response.result) {
                    return;
                }

                self.cancelEditSku();

                self.getAppropriateMagentoMessageObj().addSuccess(
                    M2ePro.translator.translate('Updating SKU has successfully submitted to be processed.')
                );

                self.getAppropriateGridObj().reload();
            }
        });
    },

    cancelEditSku: function()
    {
        var self = this;

        if (typeof self.editSkuPopup == null) {
            return;
        }

        self.editSkuPopup.close();
        self.editSkuPopup = null;
    },

    // ---------------------------------------

    showPopup: function(html, id, options)
    {
        var self = this,
            multiSetting = Window.keepMultiModalWindow;

        Window.keepMultiModalWindow = true;

        var popup = Dialog.info(null, Object.assign({
            id: id,
            destroyOnClose: true,
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: '',
            top: 200,
            width: 400,
            height: 400,
            zIndex: 200,
            hideEffect: Element.hide,
            showEffect: Element.show
        }, options));

        $(id).down('.magento_message').insert(html);

        self.autoHeightFix(400);

        Window.keepMultiModalWindow = multiSetting;
        return popup;
    },

    validateForm: function()
    {
        var validationResult = [];

        if ($('popup-edit-form')) {
            validationResult = Form.getElements('popup-edit-form').collect(Validation.validate);
        }

        if (validationResult.indexOf(false) != -1) {
            return false;
        }

        return true;
    },

    // ---------------------------------------

    getAppropriateGridObj: function()
    {
        return this.frameObj ? this.frameObj.ListingGridHandlerObj.editChannelDataHandler.gridHandler.getGridObj()
                             : this.gridHandler.getGridObj();
    },

    getAppropriateMagentoMessageObj: function()
    {
        return this.frameObj ? this.frameObj.MagentoMessageObj : MagentoMessageObj;
    }

    // ---------------------------------------
});