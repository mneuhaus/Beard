$.widget("custom.repeater", {
    // Default options.
    options: {
        autoAdd: true,
        placeholder: '--id--',
        itemAdded: function(){}
    },
    _create: function() {
        this.setOptionsFromAttributes();
        var repeater = this;
        this.itemsContainer = this.find('.repeater-items');
        this.template = this.find('.repeater-template');

        if (this.options.autoAdd === true) {
            $(this.element).on("keyup", ".repeater-unused input", function() {
                if ($(this).val().length > 0) {
                    $(this).parents('.repeater-unused').removeClass('repeater-unused');
                    repeater.addNewUnused();
                }
            });

            repeater.addNewUnused();
        }

        $(this.element).on("click", ".repeater-item-add", function(e) {
            e.preventDefault();
            repeater.addNew();
        });

        $(this.element).on("click", ".repeater-item-remove", function(e) {
            e.preventDefault();
            var item = $(this).parents('.repeater-item').first();
            var referenceData = item.find('.repeater-reference-data');
            referenceData.append('<input type="hidden" name="' + referenceData.attr('data-namespace') + '[_remove]' + '" value=1>');
            repeater.element.append(referenceData);
            item.find('.repeater-item-remove').remove();
            item.slideUp(function(){
                item.remove();
            });
        });

        $('form').submit(function() {
            $('.repeater-unused, .repeater-template').remove();
        });
    },

    setOptionsFromAttributes: function() {
        if (this.element.data('auto-add') !== undefined) {
            this.options.autoAdd = this.element.data('auto-add') ? true : false;
        }
        if (this.element.data('placeholder') !== undefined) {
            this.options.placeholder = this.element.data('placeholder');
        }
    },

    addNewUnused: function() {
        if (this.find('.repeater-unused').length > 0) {
            return;
        }
        var e = this.template.clone();
        e.removeClass('repeater-template');
        e.addClass('repeater-unused');
        e.html(e.html().replace(new RegExp(this.options.placeholder,"g"), Math.random().toString(36).slice(2)));
        this.itemsContainer.append(e);
        e.find('.card-expandable').cardExpandable();
        e.find('.repeater').repeater();
        return e;
    },

    addNew: function() {
        var newItem = this.addNewUnused().removeClass('repeater-unused');
        newItem.find('.card .card-body').first().show();
        newItem.find('.card .card-body input').first().focus();
        this.options.itemAdded();
    },

    find: function(selector) {
        return this.element.find(selector).not(this.element.find('.repeater ' + selector));
    }
});
