/* export IndexButtons */
/* globals $, jQuery */
class IndexButtons {

    /**
     * Construct IndexButtons
     * @return {IndexButtons}
     */
    constructor() {
        $('td.actions')
            .each((index, element) => {
                let $buttons = $('> a', element);
                if ($buttons.length > 3) {
                    $buttons.addClass('btn-block');
                }
            });
        return this;
    }
}

export default IndexButtons;
