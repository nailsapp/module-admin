<template>
    <div>
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
        <button v-on:click="addItem">
            Add a widget
        </button>
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
            grid: [],
            draggable: true,
            resizable: true,
            colNum: 4,
            index: 0,
            defaultWidth: 4,
            defaultHeight: 1
        }
    },

    methods: {
        addItem() {

            this.getOverview();
            this.grid
                .push({
                    'x': (this.grid.length * this.defaultWidth) % this.colNum,
                    'y': this.grid.length + this.colNum,
                    'w': this.defaultWidth,
                    'h': this.defaultHeight,
                    'i': this.index,
                    'label': 'The title ' + this.index,
                    'body': '<p>what is up, mah dawg!</p>'
                });

            this.index++;
        },

        removeItem: function(val) {
            const index = this.grid.map(item => item.i).indexOf(val);
            this.grid.splice(index, 1);
        },

        async getOverview(id) {
            try {

                this.is_loading = true;

                let res = await services.apiRequest({
                    method: 'get',
                    url: API.foo.fizz(id),
                });

                // do something with res

            } catch (error) {
                throw error;
            } finally {
                this.is_loading = false;
            }
        }
    }
}

</script>

<style lang="scss">

.vue-grid-item {
    border: 1px dashed #ccc;

    > fieldset {
        height: 100%;
    }
}

</style>
