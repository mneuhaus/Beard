$.widget("custom.cardExpandable", {
    // Default options.
    options: {
    },
    _create: function() {
        var ref = this;

        this.element.on('click', '.card-header', function(e) {
            e.preventDefault();
            ref.toggle();
        });
    },

    toggle: function() {
        this.element.find('.card-body').slideToggle();
    }
});