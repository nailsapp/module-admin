'use strict';
let _ADMIN, _ADMIN_PROXY;

import '../sass/admin.scss';
import Alerts from './components/Alerts.js';
import Confirm from './components/Confirm.js';
import CopyToClipboard from './components/CopyToClipboard.js';
import DateTime from './components/DateTime.js';
import DisabledElements from './components/DisabledElements.js';
import DynamicTable from './components/DynamicTable.js';
import Fancybox from './components/Fancybox.js';
import IndexButtons from './components/IndexButtons.js';
import InputHelper from './components/InputHelper.js';
import Modalize from './components/Modalize.js';
import Notes from './components/Notes.js';
import Repeater from './components/Repeater.js';
import Revealer from './components/Revealer.js';
import ScrollToFirstError from './components/ScrollToFirstError.js'
import Searcher from './components/Searcher.js';
import Select from './components/Select.js';
import Sortable from './components/Sortable.js';
import Stripes from './components/Stripes.js';
import Tabs from './components/Tabs.js';
import Tipsy from './components/Tipsy.js';
import Toggles from './components/Toggles.js';
import Wysiwyg from './components/Wysiwyg.js';

_ADMIN_PROXY = function(vendor, slug, instances) {
    return {
        'vendor': vendor,
        'slug': slug,
        'getInstance': function(plugin, vendor) {
            vendor = vendor || window.NAILS.ADMIN.namespace;
            return window.NAILS.ADMIN.instances[vendor][plugin];
        },
        'onRefreshUi': function(callback) {
            window.NAILS.ADMIN.onRefreshUi(callback);
            return this;
        },
        'refreshUi': function(domElement) {
            this.log('UI refresh requested', domElement || document);
            window.NAILS.ADMIN.refreshUi(domElement);
            return this;
        },
        'destroyUi': function(domElement) {
            window.NAILS.ADMIN.destroyUi(domElement);
            return this;
        },
        'onDestroyUi': function(callback) {
            window.NAILS.ADMIN.onDestroyUi(callback);
            return this;
        },
        'log': function() {
            if (typeof (console.log) === 'function') {
                console.log(
                    "\x1b[33m[" +
                    this.vendor +
                    ': ' +
                    this.slug +
                    "]\x1b[0m",
                    ...arguments
                );
            }
        },
        'warn': function() {
            if (typeof (console.warn) === 'function') {
                console.warn(
                    "\x1b[33m[" +
                    this.vendor +
                    ': ' +
                    this.slug +
                    "]\x1b[0m",
                    ...arguments
                );
            }
        },
        'error': function() {
            if (typeof (console.error) === 'function') {
                console.error(
                    "\x1b[33m[" +
                    this.vendor +
                    ': ' +
                    this.slug +
                    "]\x1b[0m",
                    ...arguments
                );
            }
        }
    }
}

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

            if (typeof plugin === 'function') {

                this.instances[vendor][slug] = plugin(
                    new _ADMIN_PROXY(vendor, slug)
                );

            } else {
                this.instances[vendor][slug] = plugin;
            }

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
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Alerts',
        function(controller) {
            return new Alerts(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Confirm',
        function(controller) {
            return new Confirm(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'CopyToClipboard',
        function(controller) {
            return new CopyToClipboard(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'DateTime',
        function(controller) {
            return new DateTime(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'DisabledElements',
        function(controller) {
            return new DisabledElements(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'DynamicTable',
        function(controller) {
            return new DynamicTable(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Fancybox',
        function(controller) {
            return new Fancybox(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'IndexButtons',
        function(controller) {
            return new IndexButtons(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'InputHelper',
        function(controller) {
            return new InputHelper(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Modalize',
        function(controller) {
            return new Modalize(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Notes',
        function(controller) {
            return new Notes(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Repeater',
        function(controller) {
            return new Repeater(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Revealer',
        function(controller) {
            return new Revealer(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'ScrollToFirstError',
        function(controller) {
            return new ScrollToFirstError(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Searcher',
        function(controller) {
            return new Searcher(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Select',
        function(controller) {
            return new Select(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Sortable',
        function(controller) {
            return new Sortable(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Stripes',
        function(controller) {
            return new Stripes(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Tabs',
        function(controller) {
            return new Tabs(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Tipsy',
        function(controller) {
            return new Tipsy(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Toggles',
        function(controller) {
            return new Toggles(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Wysiwyg',
        function(controller) {
            return new Wysiwyg(controller);
        }
    )
