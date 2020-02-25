/* export Fancybox */

/* globals $, jQuery */
class Fancybox {

    /**
     * Construct Fancybox
     * @return {Fancybox}
     */
    constructor(adminController) {

        this.init();
        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Inits Fancybox
     * @param {HTMLElement} domElement
     * @returns {Fancybox}
     */
    init(domElement) {

        $(document)
            .on('click', '.fancybox', function(e) {

                e.preventDefault();
                e.stopPropagation();

                //  Prep the URL
                let href = $(this).attr('href');
                let type = 'iframe';

                if (href.substr(0, 1) !== '#') {

                    /**
                     * Ok, so fancybox has a hard time auto detecting things when it's done like this;
                     * this results in it silently failing when trying to open something which should
                     * be opened in an iframe.
                     *
                     * To solve this we're going to explicitly look for certain file extensions and set
                     * the `type` accordingly.
                     */

                    if (/^.+.(jpg|png|gif)(\?.*)?$/.test(href)) {
                        type = null;
                    }

                    //  Parse the URL for a query string
                    href += !/\?/.test(href) ? '?' : '&';
                    href += 'isModal=true';

                } else {
                    type = null;
                }

                //  Interpret width and height
                let height = $(this).data('height');
                let width = $(this).data('width');

                //  Open a new fancybox instance
                $.fancybox.open({
                    'href': href,
                    'height': height,
                    'width': width,
                    'type': type,
                    'helpers': {
                        'overlay': {
                            'locked': false
                        }
                    },
                    'beforeLoad': function() {
                        $('body')
                            .addClass('noScroll modal-open');
                    },
                    'afterClose': function() {
                        $('body')
                            .removeClass('noScroll modal-open');
                    }
                });

                return false;
            });

        return this;
    }
}

export default Fancybox;
