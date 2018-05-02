/* export Sortable */
class Sortable {
    constructor() {
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
                        $helper
                            .children()
                            .each(function(index) {
                                // Set helper cell sizes to match the original sizes
                                $(this).width($originals.eq(index).outerWidth());
                            });
                        return $helper;
                    },
                    stop: function() {
                        $item
                            .find('.js-order')
                            .each(function(index) {
                                $(this).val(index);
                            });
                    }
                });
            });

        return this;
    }
}

export default Sortable;
