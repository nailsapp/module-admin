/* export Searcher */

/* globals $, jQuery */
class Searcher {

    /**
     * Construct Searcher
     * @return {Searcher}
     */
    constructor(adminController) {

        $(document)
            .on('admin:js-searcher', (e, selector, options, domElement) => {
                Searcher.log('Initiating new searchers');
                this.init(selector, options, domElement);
            });

        this.adminController = adminController;
        this.adminController
            .onRefreshUi((e, domElement) => {
                $(document)
                    .trigger(
                        'admin:js-searcher',
                        [
                            '.js-searcher:not(.processed)',
                            {},
                            domElement
                        ]
                    );
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Inits Searcher
     * @returns {Modalize}
     */
    init(selector, options, domElement) {
        options = options || {};
        $(selector, domElement)
            .each((index, element) => {
                $(element)
                    .add('processed')
                    .data(
                        'searcher',
                        new SearcherInstance(
                            element,
                            options
                        )
                    );
            });
        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Write a log to the console
     * @param  {String} message The message to log
     * @param  {mixed}  payload Any additional data to display in the console
     * @return {void}
     */
    static log(message, payload) {
        if (typeof (console.log) === 'function') {
            if (payload !== undefined) {
                console.log('Searcher:', message, payload);
            } else {
                console.log('Searcher:', message);
            }
        }
    };

    // --------------------------------------------------------------------------

    /**
     * Write a warning to the console
     * @param  {String} message The message to warn
     * @param  {mixed}  payload Any additional data to display in the console
     * @return {void}
     */
    static warn(message, payload) {
        if (typeof (console.warn) === 'function') {
            if (payload !== undefined) {
                console.warn('Searcher:', message, payload);
            } else {
                console.warn('Searcher:', message);
            }
        }
    };
}

class SearcherInstance {

    /**
     * Construct SearcherInstance
     *
     * @param {DOMElement} element
     * @param {Object} options
     */
    constructor(element, options) {

        this.$input = $(element);

        //  Do not double init
        if (this.$input.data('searcher') instanceof SearcherInstance) {
            return;
        }

        this.api = this.coalesce(this.$input.data('api'), options.api);
        this.isMultiple = this.coalesce(this.$input.data('multiple'), options.isMultiple, false);
        this.isClearable = this.coalesce(this.$input.data('clearable'), options.isClearable, true);
        this.placeholder = this.coalesce(this.$input.data('placeholder'), options.placeholder, 'Search for an item');
        this.minLength = this.coalesce(this.$input.data('min-length'), options.minLength, 2);
        this.getParam = this.coalesce(this.$input.data('get-param'), options.getParam, 'search');
        this.formatter = this.coalesce(options.formatter, null);
        this.propId = this.coalesce(this.$input.data('prop-id'), options.propId, 'id');
        this.propLabel = this.coalesce(this.$input.data('prop-label'), options.propLabel, 'label');

        if (this.api) {

            this.$input
                .removeClass('js-searcher')
                .select2({
                    placeholder: this.placeholder,
                    minimumInputLength: this.minLength,
                    multiple: this.isMultiple,
                    allowClear: this.isClearable,
                    ajax: {
                        url: window.SITE_URL + 'api/' + this.api,
                        dataType: 'json',
                        quietMillis: 250,
                        data: (term) => {
                            let out = {};
                            out[this.getParam] = term
                            return out;
                        },
                        results: (response) => {
                            return {
                                'results': this.formatResults(response.data)
                            };
                        },
                        cache: true
                    },
                    initSelection: (element, callback) => {
                        let id = $(element).val();
                        if (id !== '' && this.isMultiple) {
                            $.ajax({
                                url: window.SITE_URL + 'api/' + this.api + '?ids=' + id,
                                dataType: 'json'
                            })
                                .done((response) => {
                                    callback(this.formatResults(response.data));
                                });

                        } else if (id !== '') {
                            $.ajax({
                                url: window.SITE_URL + 'api/' + this.api + '/' + id,
                                dataType: 'json'
                            }).done((response) => {
                                callback(this.formatResult(response.data));
                            });
                        }
                    }
                });

        } else {
            console.warn('Element is configured as a Searcher but no api has been defined', this.$input);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Formates an array of items
     * @param {Array} results An array of items to format
     * @return {Array}
     */
    formatResults(results) {
        let out = [];
        for (let i = 0; i < results.length; i++) {
            out.push(this.formatResult(results[i]));
        }
        return out;
    }

    // --------------------------------------------------------------------------

    /**
     * Formats a single item
     * @param {Object} item The single object to format
     * @return {{id: Number, text: String}}
     */
    formatResult(item) {
        if (typeof this.formatter === 'function') {
            return this.formatter.call(this, item)
        } else {
            return {
                'id': item[this.propId],
                'text': item[this.propLabel]
            };
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Implements null coalesce operator type functionality
     * hat-tip: https://stackoverflow.com/a/22265471/789224
     * @return {null|any}
     */
    coalesce() {
        var len = arguments.length;
        for (let i = 0; i < len; i++) {
            if (arguments[i] !== null && arguments[i] !== undefined) {
                return arguments[i];
            }
        }
        return null;
    }
}

export default Searcher;
