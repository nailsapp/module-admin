/* export Confirm */

/* globals $, jQuery */
class Confirm {

    /**
     * Construct Confirm
     * @return {Confirm}
     */
    constructor(adminController) {

        adminController
            .onRefreshUi((domElement) => {
                this.init(domElement);
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Inits Confirm
     * @param domElement {HTMLElement} The domElement to focus on
     * @returns {Confirm}
     */
    init(domElement) {

        $('a.confirm', domElement)
            .on('click', (e) => {

                let $link = $(e.currentTarget);
                let body = $link.data('body') || 'Please confirm you\'d like to continue with this action.';
                let title = $link.data('title') || 'Are you sure?';

                body.replace(/\\n/g, '\n');

                if (body.length) {

                    $('<div>')
                        .html(body)
                        .dialog({
                            'title': title,
                            'resizable': false,
                            'draggable': false,
                            'modal': true,
                            'dialogClass': 'no-close',
                            'buttons': {
                                'OK': function() {
                                    window.location.href = $link.attr('href');
                                },
                                'Cancel': function() {
                                    $(this).dialog('close');
                                }
                            }
                        });

                    return false;

                } else {
                    //  No message, just let the event bubble as normal.
                    return true;
                }
            });

        return this;
    }
}

export default Confirm;
