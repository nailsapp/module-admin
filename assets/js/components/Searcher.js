/* export Searcher */

/* globals $, jQuery */
class Searcher {

    /**
     * Construct Searcher
     * @return {Searcher}
     */
    constructor() {
        $(document)
            .on('admin:js-searcher', (e, selector, options) => {
                options = options || {};
                $(selector)
                    .each((index, element) => {
                        $(element)
                            .data(
                                'searcher',
                                new SearcherInstance(
                                    element,
                                    options
                                )
                            );
                    });
            });

        $(document)
            .trigger('admin:js-searcher', ['.js-searcher'])
    }
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

        this.api = this.$input.data('api') || options.api;
        this.isMultiple = this.$input.data('multiple') || options.isMultiple || false;
        this.isClearable = this.$input.data('clearable') || options.isClearable || true;
        this.placeholder = this.$input.data('placeholder') || options.placeholder || 'Search for an item';
        this.minLength = this.$input.data('min-length') || options.minLength || 2;
        this.getParam = this.$input.data('get-param') || options.getParam || 'search';
        this.formatter = options.formatter || null;

        if (this.api) {

            this.$input
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
                'id': item.id,
                'text': item.label
            };
        }
    }
}

export default Searcher;
