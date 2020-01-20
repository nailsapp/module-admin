/* export CopyToClipboard */

import ClipboardJS from 'clipboard';

/* globals $, jQuery */
class CopyToClipboard {

    /**
     * Construct CopyToClipboard
     * @return {CopyToClipboard}
     */
    constructor(adminController) {
        $(document)
            .on('admin:js-copy-to-clipboard', (e, selector) => {
                CopyToClipboard.log('Initiating new copy buttons');
                $(selector)
                    .addClass('js-copy-to-clipboard--initiated')
                    .each((index, element) => {
                        if (!$(element).data('clipboardjs')) {
                            $(element)
                                .data(
                                    'clipboardjs',
                                    new CopyToClipboardInstance(
                                        element
                                    )
                                );
                        }
                    });
            });

        adminController
            .onRefreshUi(() => {
                $(document)
                    .trigger(
                        'admin:js-copy-to-clipboard',
                        ['.js-copy-to-clipboard']
                    );
            });
    }

    // --------------------------------------------------------------------------

    /**
     * Write a log to the console
     * @return {void}
     */
    static log() {
        if (typeof (console.log) === 'function') {
            console.log('"\x1b[33m[CopyToClipboard]\x1b[0m"', ...arguments);
        }
    };

    // --------------------------------------------------------------------------

    /**
     * Write a warning to the console
     * @return {void}
     */
    static warn() {
        if (typeof (console.warn) === 'function') {
            console.warn('"\x1b[33m[CopyToClipboard]\x1b[0m"', ...arguments);
        }
    };
}

class CopyToClipboardInstance {

    /**
     * Construct CopyToClipboardInstance
     * @param {DOMElement} element
     */
    constructor(element) {

        this.$body = $('body');
        this.$el = $(element);
        this.$icon = $('<div>')
            .text('Copied')
            .css({
                position: 'absolute',
                left: 0,
                top: 0,
                background: '#000000',
                color: '#ffffff',
                zIndex: 1000000,
                padding: '0.25rem 0.5rem',
                borderRadius: '3px',
                border: '1px solid #ffffff',
                fontSize: '0.8rem'
            });

        this.clipboardJs = new ClipboardJS(element);
        this.clipboardJs
            .on('success', (e) => {
                CopyToClipboard.log('Item copied!');
                this.showIcon();
                e.clearSelection();
            })
            .on('error', (e) => {
                CopyToClipboard.warn('Error', e);
                e.clearSelection();
            });
    }

    // --------------------------------------------------------------------------

    /**
     * Shows the "copied" icon
     */
    showIcon() {

        this.$icon.remove();
        this.$body.append(this.$icon);
        let offsetTop = this.calcOffsetTop();
        this.$icon
            .css({
                'opacity': 0,
                'left': this.calcOffsetLeft(),
                'top': this.calcOffsetTop()
            })
            .animate({
                'opacity': 1,
            }, 100)
            .delay(250)
            .animate({
                'opacity': 0,
            }, 250);


        setTimeout(() => {
            this.$icon.remove();
        }, 600);
    }

    // --------------------------------------------------------------------------

    /**
     * Calculates the left offset
     * @returns {number}
     */
    calcOffsetLeft() {
        return this.$el.offset().left + (this.$el.outerWidth() / 2) - (this.$icon.outerWidth() / 2);
    }

    // --------------------------------------------------------------------------

    /**
     * Calculates the top offset
     * @returns {number}
     */
    calcOffsetTop() {
        return this.$el.offset().top - this.$icon.outerHeight() - 5;
    }
}

export default CopyToClipboard;
