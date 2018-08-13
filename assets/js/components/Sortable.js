/* export Sortable */
/* globals $, jQuery */
class Sortable {

    /**
     * Construct Sortable
     * @return {Sortable}
     */
    constructor() {
        $('.js-admin-sortable')
            .each(function() {

                let $item = $(this);
                let handle = $item.data('handle') || null;
                let axis = $item.data('axis') || 'y';
                let containment = $item.data('containment') || 'parent';

                $item
                    .sortable({
                        handle: handle,
                        axis: axis,
                        containment: containment,
                        forceHelperSize: true,
                        helper: function(e, tr) {
                            let $originals = tr.children();
                            let $helper = tr.clone();
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
                                .find('.js-admin-sortable__order')
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
