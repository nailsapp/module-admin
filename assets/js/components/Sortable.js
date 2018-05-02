/* exported _ADMIN_SORTABLE */
var _ADMIN_SORTABLE;
_ADMIN_SORTABLE = function() {

    /**
     * Avoid scope issues in callbacks and anonymous functions by referring to `this` as `base`
     * @type {_ADMIN_SORTABLE}
     */
    var base = this;

    // --------------------------------------------------------------------------

    /**
     * Construct _ADMIN_SORTABLE
     * @return {_ADMIN_SORTABLE} A reference to this class
     */
    base.__construct = function() {
        $('.js-admin-sortable')
            .each(function() {

                var $item = $(this);
                var handle = $item.data('handle') || null;
                var axis = $item.data('axis') || 'y';
                var containment = $item.data('containment') || 'parent';

                $item.sortable({
                    handle: handle,
                    axis: axis,
                    containment: containment,
                    forceHelperSize: true,
                    helper: function(e, tr) {
                        var $originals = tr.children();
                        var $helper = tr.clone();
                        $helper.children().each(function(index) {
                            // Set helper cell sizes to match the original sizes
                            $(this).width($originals.eq(index).outerWidth());
                        });
                        return $helper;
                    },
                    stop: function() {
                        $item.find('.js-order').each(function(index) {
                            $(this).val(index);
                        });
                    }
                });
            });

        return base;
    };

    // --------------------------------------------------------------------------

    return base.__construct();
}();
