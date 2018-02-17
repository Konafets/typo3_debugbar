if (typeof(PhpDebugBar) === 'undefined') {
    // namespace
    var PhpDebugBar = {};
    PhpDebugBar.$ = jQuery;
}

(function($) {

    var csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-');

    /**
     * Widget for the displaying sql queries
     *
     * Options:
     *  - data
     */
    var TYPO3GenericWidget = PhpDebugBar.Widgets.TYPO3GenericWidget = PhpDebugBar.Widget.extend({

        className: csscls('typo3-debugbar-generic'),

        render: function() {
            this.bindAttr('data', function(data) {
                this.$el.append(data);
            });
        }
    });

})(PhpDebugBar.$);
