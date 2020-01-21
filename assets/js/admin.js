'use strict';
let _ADMIN;

import '../sass/admin.scss';
import Alerts from './components/Alerts.js';
import Confirm from './components/Confirm.js';
import CopyToClipboard from './components/CopyToClipboard.js';
import DisabledElements from './components/DisabledElements.js';
import DynamicTable from './components/DynamicTable.js';
import IndexButtons from './components/IndexButtons.js';
import InputHelper from './components/InputHelper.js';
import Modalize from './components/Modalize.js';
import Notes from './components/Notes.js';
import Repeater from './components/Repeater.js';
import Revealer from './components/Revealer.js';
import Searcher from './components/Searcher.js';
import Sortable from './components/Sortable.js';
import Stripes from './components/Stripes.js';
import Tabs from './components/Tabs.js';
import Toggles from './components/Toggles.js';
import Wysiwyg from './components/Wysiwyg.js';

_ADMIN = function() {
    return {

        /**
         * The admin module namespace
         */
        'namespace': 'nails/module-admin',

        /**
         * All the registered plugins
         */
        'instances': {},

        /**
         * Registers a new plugin
         * @param {String} slug The name of the plugin
         * @param plugin
         */
        'registerPlugin': function(vendor, slug, plugin) {
            if (typeof this.instances[vendor] === 'undefined') {
                this.instances[vendor] = {};
            }
            this.instances[vendor][slug] = plugin;
            return this;
        },

        /**
         * Triggers an event
         * @param eventName
         */
        'trigger': function(eventName, detail) {
            document
                .dispatchEvent(
                    new CustomEvent(eventName, {detail: detail})
                );
            return this;
        },

        /**
         * Allows plugins to listen for events
         * @param {String} event The event to listen for
         * @param {function} callback The callback to execute
         */
        'on': function(event, callback) {
            document
                .addEventListener(event, callback);
            return this;
        },

        /**
         * Triggers a UI refresh
         * @param domElement The domElement to focus the refresh on
         */
        'refreshUi': function(domElement) {
            this.log('Refreshing UI', domElement || document);
            this.trigger('admin:refresh-ui', {domElement: domElement || document});
            return this;
        },

        /**
         * Allows plugins to register callbacks for UI refreshing
         * @param {function} callback The callback to execute
         */
        'onRefreshUi': function(callback) {
            this.on('admin:refresh-ui', function(e) {
                callback(e, e.detail ? e.detail.domElement : null);
            });
            return this;
        },

        /**
         * Triggers a UI destroy
         * @param domElement The domElement to focus the destroy on
         */
        'destroyUi': function(domElement) {
            this.log('Destroying UI', domElement || document);
            this.trigger('admin:destroy-ui', {domElement: domElement || document});
            return this;
        },

        /**
         * Allows plugins to register callbacks for UI destruction
         * @param {function} callback The callback to execute
         */
        'onDestroyUi': function(callback) {
            this.on('admin:destroy-ui', function(e) {
                callback(e, e.detail ? e.detail.domElement : null);
            });
            return this;
        },

        // --------------------------------------------------------------------------

        /**
         * Write a log to the console
         * @return {void}
         */
        'log': function() {
            if (typeof (console.log) === 'function') {
                console.log("\x1b[33m[Admin]\x1b[0m", ...arguments);
            }
        },

        // --------------------------------------------------------------------------

        /**
         * Write a warning to the console
         * @return {void}
         */
        'warn': function(message, payload) {
            if (typeof (console.warn) === 'function') {
                console.warn("\x1b[33m[Admin]\x1b[0m", ...arguments);
            }
        }
    };
};

window.NAILS.ADMIN = new _ADMIN();

window
    .NAILS
    .ADMIN
    .registerPlugin(window.NAILS.ADMIN.namespace, 'Alerts', new Alerts(window.NAILS.ADMIN))
    .registerPlugin(window.NAILS.ADMIN.namespace, 'Confirm', new Confirm(window.NAILS.ADMIN))
    .registerPlugin(window.NAILS.ADMIN.namespace, 'CopyToClipboard', new CopyToClipboard(window.NAILS.ADMIN))
    .registerPlugin(window.NAILS.ADMIN.namespace, 'DisabledElements', new DisabledElements())
    .registerPlugin(window.NAILS.ADMIN.namespace, 'DynamicTable', new DynamicTable(window.NAILS.ADMIN))
    .registerPlugin(window.NAILS.ADMIN.namespace, 'IndexButtons', new IndexButtons(window.NAILS.ADMIN))
    .registerPlugin(window.NAILS.ADMIN.namespace, 'InputHelper', new InputHelper(window.NAILS.ADMIN))
    .registerPlugin(window.NAILS.ADMIN.namespace, 'Modalize', new Modalize(window.NAILS.ADMIN))
    .registerPlugin(window.NAILS.ADMIN.namespace, 'Notes', new Notes(window.NAILS.ADMIN))
    .registerPlugin(window.NAILS.ADMIN.namespace, 'Repeater', new Repeater(window.NAILS.ADMIN))
    .registerPlugin(window.NAILS.ADMIN.namespace, 'Revealer', new Revealer(window.NAILS.ADMIN))
    .registerPlugin(window.NAILS.ADMIN.namespace, 'Searcher', new Searcher(window.NAILS.ADMIN))
    .registerPlugin(window.NAILS.ADMIN.namespace, 'Sortable', new Sortable(window.NAILS.ADMIN))
    .registerPlugin(window.NAILS.ADMIN.namespace, 'Stripes', new Stripes(window.NAILS.ADMIN))
    .registerPlugin(window.NAILS.ADMIN.namespace, 'Tabs', new Tabs(window.NAILS.ADMIN))
    .registerPlugin(window.NAILS.ADMIN.namespace, 'Toggles', new Toggles(window.NAILS.ADMIN))
    .registerPlugin(window.NAILS.ADMIN.namespace, 'Wysiwyg', new Wysiwyg(window.NAILS.ADMIN));
