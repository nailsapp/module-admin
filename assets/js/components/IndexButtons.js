/* export IndexButtons */

/* globals $, jQuery */
class IndexButtons {

    /**
     * Construct IndexButtons
     * @return {IndexButtons}
     */
    constructor(adminController) {

        adminController
            .onRefreshUi(() => {
                this.init();
            });

        return this;
    }

    /**
     * Inits the index buttoins
     * @returns {IndexButtons}
     */
    init() {
        $('td.actions')
            .each((index, element) => {
                let $buttons = $('> .btn, > .btn-group', element);
                let offsets = [];
                $buttons.each((index, element) => {
                    offsets.push(element.offsetTop);
                })

                if (Math.max(...offsets) !== Math.min(...offsets)) {
                    $buttons.addClass('btn-block');
                    $buttons.filter('.btn-group').find('> .btn').addClass('btn-block');
                } else {
                    $buttons.removeClass('btn-block');
                    $buttons.filter('.btn-group').find('> .btn').removeClass('btn-block');
                }
            });
        return this;
    }
}

export default IndexButtons;
