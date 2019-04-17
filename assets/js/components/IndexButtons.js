/* export IndexButtons */

/* globals $, jQuery */
class IndexButtons {

    /**
     * Construct IndexButtons
     * @return {IndexButtons}
     */
    constructor () {
        $('td.actions')
            .each((index, element) => {
                let $buttons = $('> a', element);
                let offsets = [];
                $buttons.each((index, element) => {
                    offsets.push(element.offsetTop);
                })

                if (Math.max(...offsets) !== Math.min(...offsets)) {
                    $buttons.addClass('btn-block');
                }
            });
        return this;
    }
}

export default IndexButtons;
