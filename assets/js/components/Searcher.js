/* export Searcher */

/* globals $, jQuery */
class Searcher {
    /**
     * Construct Searcher
     * @return {Searcher}
     */
    constructor() {
        $('.js-searcher')
            .each((index, element) => {
                $(element).data('searcher', new SearcherInstance(element));
            });
    }
}

class SearcherInstance {

    /**
     * Construct SearcherInstance
     * @param {DOMElement} element
     */
    constructor(element) {

        this.$input = $(element);
        this.api = this.$input.data('api');
        this.isMultiple = this.$input.data('multiple') || false;
        this.isClearable = this.$input.data('clearable') || true;
        this.placeholder = this.$input.data('placeholder') || 'Search for an item';
        this.minLength = this.$input.data('min-length') || 2;

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
                            return {search: term};
                        },
                        results: (response) => {
                            return {'results': this.formatResults(response.data)};
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
            console.warn('Element is configured as a Searcher but no model or provider has been defined', this.$input);
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
        return {
            'id': item.id,
            'text': item.label
        };
    }
}

export default Searcher;
