/* export Repeater */

//  @todo (Pablo - 2019-07-29) - Support sorting of elements

import Mustach from 'mustache';

/* globals $, jQuery */
class Repeater {

    /**
     * Construct Repeater
     * @return {Repeater}
     */
    constructor() {

        this.repeaters = [];
        $('.js-admin-repeater')
            .each((index, element) => {

                let $element = $(element);
                let instance = new RepeaterInstance(
                    $element,
                    $element.data('data')
                );

                $element.data('js-admin-repeater-instance', instance);

                this
                    .repeaters
                    .push(instance);

            });

        return this;
    }
}

// --------------------------------------------------------------------------

class RepeaterInstance {

    /**
     * Construct RepeaterInstance
     * @param $element
     * @returns {RepeaterInstance}
     */
    constructor($element, data) {

        this.trigger('constructing');

        this.index = 0;
        this.$element = $element
        this.$target = $('.js-admin-repeater__target', this.$element);
        this.$add = $('.js-admin-repeater__add', this.$element);
        this.template = $('.js-admin-repeater__template', this.$element).html();
        $('.js-admin-repeater__template', this.$element).remove();

        this.bindEvents();
        this.load(data || []);

        this.trigger('constructed');

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Bind events to the repeater
     */
    bindEvents() {

        this.trigger('binding');

        this.$add
            .on('click', (e) => {
                this.add();
                return false;
            });

        this.$target
            .on('click', '.js-admin-repeater__remove', (e) => {
                this.remove($(e.currentTarget));
                return false;
            });

        this.$element
            .on('js-admin-repeater:load', (e, data) => {
                this.load(data);
            })
            .on('js-admin-repeater:clear', (e) => {
                this.reset();
            });

        this.trigger('bound');
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a new item to the repeaer
     * @param data Any data to sue when rendering the item
     */
    add(data) {

        data = data || {};
        data.index = this.index;

        this.trigger('adding', data);

        let $item = $('<li>').addClass('js-admin-repeater__target__item');
        let template = this.template;

        template = Mustach.render(template, data);

        $item.html(template);
        this.$target.append($item);
        this.index++;

        this.trigger('added');
    }

    // --------------------------------------------------------------------------

    /**
     * Removes an item from the repeater
     * @param $btn The button which was clicked
     */
    remove($btn) {

        let $item = $btn
            .closest('.js-admin-repeater__target__item');

        this.trigger('removing', $item);
        $item.remove();
        this.trigger('removed', $item);
    }

    // --------------------------------------------------------------------------

    /**
     * Load an array of data
     * @param data
     */
    load(data) {
        this.trigger('loading', data);
        for (let key in data) {
            if (data.hasOwnProperty(key)) {
                this.add(data[key]);
            }
        }
        this.trigger('loaded', data);
    }

    // --------------------------------------------------------------------------

    /**
     * Removes all items from the repeater and reset the idnex
     */
    reset() {
        this.trigger('resetting');
        $('.js-admin-repeater__target__item', this.$target)
            .remove();
        this.index = 0;
        this.trigger('reset');
    }

    // --------------------------------------------------------------------------

    /**
     * Triggers an event
     * @param event
     * @param data
     */
    trigger(event, data) {

        let eventData = {
            'instance': this,
            'data': data
        };

        console.log('js-admin-repeater:' + event, eventData);
    }
}

// --------------------------------------------------------------------------

export default Repeater;
