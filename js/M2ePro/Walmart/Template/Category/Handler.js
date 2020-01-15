WalmartTemplateCategoryHandler = Class.create(WalmartTemplateEditHandler, {

    // ---------------------------------------

    initialize: function()
    {
        var self = this;

        self.specificHandler = null;

        // ---------------------------------------
        self.categoryInfo = {};

        self.categoryPathHiddenInput = $('category_path');
        self.categoryNodeIdHiddenInput = $('browsenode_id');

        self.categoryProductDataNickHiddenInput = $('product_data_nick');
        // ---------------------------------------

        self.productDataNicksInfo = {};
        self.variationThemes      = [];

        // ---------------------------------------

        self.initValidation();
    },

    initValidation: function()
    {
        var self = this;

        self.setValidationCheckRepetitionValue('M2ePro-category-template-title',
                                                M2ePro.translator.translate('The specified Title is already used for another Policy. Policy Title must be unique.'),
                                                'Walmart_Template_Category', 'title', 'id',
                                                M2ePro.formData.id);

        Validation.add('M2ePro-validate-category', M2ePro.translator.translate('You should select Category and Product Type first'), function(value) {

            return $('category_path').value != '';
        });
    },

    // ---------------------------------------

    setSpecificHandler: function(object)
    {
        var self = this;
        self.specificHandler = object;
    },

    // ---------------------------------------

    checkMarketplaceSelection: function()
    {
        return $('marketplace_id').value != '';
    },

    //########################################

    duplicate_click: function($super, $headId)
    {
        this.setValidationCheckRepetitionValue('M2ePro-category-template-title',
                                                M2ePro.translator.translate('The specified Title is already used for another Policy. Policy Title must be unique.'),
                                                'Walmart_Template_Category', 'title', '','');

        if (M2ePro.customData.category_locked) {

            M2ePro.customData.category_locked = false;
            this.hideCategoryWarning('category_locked_warning_message');
            $('edit_category_link').show();

            $('product_data_nick_select').removeAttribute('disabled');
        }

        if (M2ePro.customData.marketplace_locked) {

            M2ePro.customData.marketplace_locked = false;
            $('marketplace_locked_warning_message').remove();

            if (!M2ePro.customData.marketplace_force_set) {
                $('marketplace_hidden_input').remove();
                $('marketplace_id').removeAttribute('disabled');
            }
        }

        if (M2ePro.customData.new_asin_switcher_locked) {

            M2ePro.customData.new_asin_switcher_locked = false;
            $('new_asin_locked_warning_message').remove();

            if (!M2ePro.customData.new_asin_switcher_force_set) {
                $('new_asin_accepted_hidden_input').remove();
                $('new_asin_accepted').removeAttribute('disabled');
            }
        }

        $super($headId, M2ePro.translator.translate('Add Category Policy'));
    },

    // ---------------------------------------

    save_click: function($super, url, confirmText, templateNick)
    {
        var self = WalmartTemplateCategoryHandlerObj;

        self.specificHandler.prepareSpecificsDataToPost();
        $super(url, confirmText, templateNick);
    },

    save_and_edit_click: function($super, url, tabsId, confirmText, templateNick)
    {
        var self = WalmartTemplateCategoryHandlerObj;

        self.specificHandler.prepareSpecificsDataToPost();
        $super(url, tabsId, confirmText, templateNick);
    },

    //########################################

    onChangeMarketplace: function()
    {
        var self = WalmartTemplateCategoryHandlerObj;
        self.resetCategory();
    },

    onClickEditCategory: function()
    {
        var self = WalmartTemplateCategoryHandlerObj;

        if (!self.checkMarketplaceSelection()) {
            return alert(M2ePro.translator.translate('You should select Marketplace first.'));
        }

        WalmartTemplateCategoryCategoriesChooserHandlerObj.showEditCategoryPopUp();
    },

    // ---------------------------------------

    setCategory: function(categoryInfo, notSetProductTypeForceIfOnlyOne)
    {
        var self = this;
        notSetProductTypeForceIfOnlyOne = notSetProductTypeForceIfOnlyOne || false;

        this.categoryInfo = categoryInfo;

        this.categoryPathHiddenInput.value   = this.getInterfaceCategoryPath(categoryInfo);
        this.categoryNodeIdHiddenInput.value = categoryInfo.browsenode_id;

        this.updateCategoryPathSpan(this.getInterfaceCategoryPath(categoryInfo, true));

        self.setProductDataNick(this.categoryInfo.product_data_nicks.shift());

        if (self.categoryInfo.product_data_nicks.length == 1 && !notSetProductTypeForceIfOnlyOne) {
            self.setProductDataNick(self.categoryInfo.product_data_nicks[0]);
        }

        this.hideCategoryWarning('category_is_not_accessible_message');

        $$('.m2epro-category-depended-block').each(function(el){
            el.show();
        });
    },

    setProductDataNick: function(productDataNick)
    {
        var self = this;

        this.categoryProductDataNickHiddenInput.value = productDataNick;

        this.updateWarningMessagesVisibility();

        this.specificHandler.reset();
        this.specificHandler.run(this.categoryInfo, productDataNick);
    },

    // ---------------------------------------

    resetCategory: function()
    {
        this.categoryInfo = {};

        this.categoryPathHiddenInput.value   = '';
        this.categoryNodeIdHiddenInput.value = '';

        this.resetCategoryPathSpan();
        this.resetProductDataNick();

        this.hideCategoryWarning('category_variation_warning_message');

        $$('.m2epro-category-depended-block').each(function(el){
            el.hide();
        });
    },

    resetProductDataNick: function()
    {
        this.categoryProductDataNickHiddenInput.value = '';
        this.specificHandler.reset();
    },

    // ---------------------------------------

    prepareEditMode: function()
    {
        var self = WalmartTemplateCategoryHandlerObj;

        if (M2ePro.formData.product_data_nick == '' ||
            M2ePro.formData.browsenode_id == '' ||
            M2ePro.formData.category_path == '') {

            return;
        }

        var callback = function(transport) {

            if (!transport.responseText) {

                self.resetCategory();
                self.showCategoryWarning('category_is_not_accessible_message');

            } else {

                var categoryInfo = transport.responseText.evalJSON();

                self.setCategory(categoryInfo, true);
                self.setProductDataNick(M2ePro.formData.product_data_nick);

                if (M2ePro.customData.category_locked) {

                    self.showCategoryWarning('category_locked_warning_message');
                    $('edit_category_link').hide();

                    $('product_data_nick_select').setAttribute('disabled', 'disabled');
                }
            }
        };

        WalmartTemplateCategoryCategoriesChooserHandlerObj.getCategoryInfoFromDictionaryBrowseNodeId(
            M2ePro.formData.browsenode_id,
            M2ePro.formData.category_path,
            callback
        );
    },

    // ---------------------------------------

    showCategoryWarning: function(item)
    {
        var me = $(item);

        var atLeastOneWarningShown = $$('#category_warning_messages span.category-warning-item').any(function(obj) {
            return $(obj).id != me.id && $(obj).visible();
        });

        if (atLeastOneWarningShown && me.previous('span.additional-br')) {
            me.previous('span.additional-br').show();
        }

        $(item).show();
        $('category_warning_messages').show();
    },

    hideCategoryWarning: function(item)
    {
        var me = $(item);
        $(item).hide();

        var atLeastOneWarningShown = $$('#category_warning_messages .category-warning-item').any(function(obj) {
            return $(obj).visible();
        });

        if (me.previous('span.additional-br')) {
            me.previous('span.additional-br').hide();
        }

        !atLeastOneWarningShown && $('category_warning_messages').hide();
    },

    // ---------------------------------------

    updateCategoryPathSpan: function(path)
    {
        $('category_path_span').update(path);
    },

    resetCategoryPathSpan: function()
    {
        var span = $('category_path_span');
        span.innerHTML = '<span style="color: grey; font-style: italic">' + M2ePro.translator.translate('Not Selected') + '</span>';
    },

    updateWarningMessagesVisibility: function()
    {
        var self = WalmartTemplateCategoryHandlerObj;

        new Ajax.Request(M2ePro.url.get('adminhtml_walmart_template_category/getVariationThemes'), {
            method: 'get',
            asynchronous: true,
            parameters: {
                marketplace_id:     $('marketplace_id').value,
                product_data_nick: self.categoryProductDataNickHiddenInput.value
            },
            onSuccess: function(transport) {

                self.variationThemes = transport.responseText.evalJSON();

                self.variationThemes.length == 0 ? self.showCategoryWarning('category_variation_warning_message')
                                                 : self.hideCategoryWarning('category_variation_warning_message');
            }
        });
    },

    //########################################

    getInterfaceCategoryPath: function(categoryInfo, withBrowseNodeId)
    {
        withBrowseNodeId = withBrowseNodeId || false;

        var path = categoryInfo.path != null ? categoryInfo.path.replace(/>/g,' > ') + ' > ' + categoryInfo.title
                                             : categoryInfo.title;

        return !withBrowseNodeId ? path : path + ' ('+categoryInfo.browsenode_id+')';
    }

    // ---------------------------------------
});