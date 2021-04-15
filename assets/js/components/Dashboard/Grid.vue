<template>
    <div class="dashboard-widgets">
        <div
            class="dashboard-widgets__empty"
            v-if="!grid.length"
        >
            <p>
                <i><b class="fa fa-th-large"></b>Your dashboard is empty</i>
                <small>
                    Dashboard widgets show small pieces of data regarding your website.
                    Your dashboard is completely customiseable.
                </small>
            </p>
            <p>
                <button
                    class="btn btn-default"
                    v-on:click="configureWidget"
                    v-bind:disabled="!widgets.length"
                >
                    Add a widget
                </button>
            </p>
        </div>
        <div class="dashboard-widgets__grid">
            <grid-layout
                :layout.sync="grid"
                :col-num="colNum"
                :row-height="100"
                :is-draggable="draggable"
                :is-resizable="resizable"
                :vertical-compact="true"
                :margin="[10, 10]"
                :use-css-transforms="true"
            >

                <grid-item v-for="(item, i) in grid"
                           :x="item.x"
                           :y="item.y"
                           :w="item.w"
                           :h="item.h"
                           :i="item.i"
                           :key="item.i"
                >

                    <fieldset>
                        <legend>
                            {{ item.label }}
                            <span class="btn btn-xs btn-danger pull-right" @click="removeItem(item.i)">&times;</span>
                        </legend>
                        <div v-html="item.body"/>
                    </fieldset>

                </grid-item>
            </grid-layout>
        </div>
        <div
            class="dashboard-widgets__add"
            v-if="grid.length && widgets.length"
            v-on:click="configureWidget"
        >
            Add a widget
        </div>
    </div>
</template>
<script>

import API from '../API'
import services from '../Services'
import VueGridLayout from 'vue-grid-layout';

export default {

    name: 'DashboardGrid',

    components: {
        GridLayout: VueGridLayout.GridLayout,
        GridItem: VueGridLayout.GridItem
    },

    data() {
        return {
            widgets: [],
            grid: [],
            draggable: true,
            resizable: true,
            colNum: 4,
            index: 0,
            defaultWidth: 4,
            defaultHeight: 1
        }
    },

    mounted() {
        this.loadWidgets()
            .then((widgets) => this.widgets = widgets);

        //  @todo (Pablo 2021-04-15) - Fetch the user's preferences
        //  @todo (Pablo 2021-04-15) - Render user's selected widgets
    },

    methods: {

        configureWidget(index) {

            if (index) {
                //  @todo (Pablo 2021-04-15) - configure existing widget, pull from this.grid
            } else {
                //  @todo (Pablo 2021-04-15) - New widget
            }
        },

        /**
         * Adds a widget to the stack
         */
        addItem(slug, label, body) {

            this.grid
                .push({
                    'x': (this.grid.length * this.defaultWidth) % this.colNum,
                    'y': this.grid.length + this.colNum,
                    'w': this.defaultWidth,
                    'h': this.defaultHeight,
                    'i': this.index,
                    'slug': slug,
                    'label': label,
                    'body': body
                });

            this.index++;
        },

        /**
         * Removes a widget from the stack
         * @param val
         */
        removeItem: function(index) {
            this.grid
                .splice(
                    this.grid.map(item => item.i).indexOf(index),
                    1
                );
        },

        /**
         * Loads available widgets
         * @returns {Promise<[]|null|Array|*>}
         */
        async loadWidgets() {

            let res = await services.apiRequest({
                method: 'get',
                url: API.dashboard.widgets.fetch,
            });

            return res.data.data.widgets;
        }
    }
}

</script>
