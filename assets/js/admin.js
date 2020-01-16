'use strict';
let _ADMIN;

import '../sass/admin.scss';
import Alerts from './components/Alerts.js';
import CopyToClipboard from './components/CopyToClipboard.js';
import DisabledElements from './components/DisabledElements.js';
import DynamicTable from './components/DynamicTable.js';
import IndexButtons from './components/IndexButtons.js';
import Modalize from './components/Modalize.js';
import Notes from './components/Notes.js';
import Repeater from './components/Repeater.js';
import Searcher from './components/Searcher.js';
import Sortable from './components/Sortable.js';
import Stripes from './components/Stripes.js';
import Tabs from './components/Tabs.js';
import Toggles from './components/Toggles.js';
import Wysiwyg from './components/Wysiwyg.js';

_ADMIN = function () {
    return {
        /**
         * All the registered plugins
         */
        'instances': {},

        /**
         * Registers a new plugin
         * @param {String} slug The name of the plugin
         * @param plugin
         */
        'registerPlugin': function (vendor, slug, plugin) {
            if (typeof this.instances[vendor] === 'undefined') {
                this.instances[vendor] = {};
            }
            this.instances[vendor][slug] = plugin;
        },

        /**
         * Triggers an event
         * @param eventName
         */
        'trigger': function (eventName) {
            document
                .dispatchEvent(
                    new Event(eventName)
                );
        },

        /**
         * Allows plugins to listen for events
         * @param {String} event The event to listen for
         * @param {function} callback The callback to execute
         */
        'on': function (event, callback) {
            document
                .addEventListener(event, callback);
        },

        /**
         * Triggers a UI refresh
         */
        'refreshUi': function () {
            this.trigger('admin:refresh-ui');
        },

        /**
         * Allows plugins to register callbacks for UI refreshing
         * @param {function} callback The callback to execute
         */
        'onRefreshUi': function (callback) {
            this.on('admin:refresh-ui', callback);
        }
    };
};

window.NAILS.ADMIN = new _ADMIN();

window.NAILS.ADMIN.registerPlugin(
    'nails/module-admin',
    'Alerts',
    new Alerts(window.NAILS.ADMIN)
);
window.NAILS.ADMIN.registerPlugin(
    'nails/module-admin',
    'CopyToClipboard',
    new CopyToClipboard(window.NAILS.ADMIN)
);
window.NAILS.ADMIN.registerPlugin(
    'nails/module-admin',
    'DisabledElements',
    new DisabledElements()
);
window.NAILS.ADMIN.registerPlugin(
    'nails/module-admin',
    'DynamicTable',
    new DynamicTable(window.NAILS.ADMIN)
);
window.NAILS.ADMIN.registerPlugin(
    'nails/module-admin',
    'IndexButtons',
    new IndexButtons(window.NAILS.ADMIN)
);
window.NAILS.ADMIN.registerPlugin(
    'nails/module-admin',
    'Modalize',
    new Modalize(window.NAILS.ADMIN)
);
window.NAILS.ADMIN.registerPlugin(
    'nails/module-admin',
    'Notes',
    new Notes(window.NAILS.ADMIN)
);
window.NAILS.ADMIN.registerPlugin(
    'nails/module-admin',
    'Repeater',
    new Repeater(window.NAILS.ADMIN)
);
window.NAILS.ADMIN.registerPlugin(
    'nails/module-admin',
    'Searcher',
    new Searcher(window.NAILS.ADMIN)
);
window.NAILS.ADMIN.registerPlugin(
    'nails/module-admin',
    'Sortable',
    new Sortable(window.NAILS.ADMIN)
);
window.NAILS.ADMIN.registerPlugin(
    'nails/module-admin',
    'Stripes',
    new Stripes(window.NAILS.ADMIN)
);
window.NAILS.ADMIN.registerPlugin(
    'nails/module-admin',
    'Tabs',
    new Tabs(window.NAILS.ADMIN)
);
window.NAILS.ADMIN.registerPlugin(
    'nails/module-admin',
    'Toggles',
    new Toggles(window.NAILS.ADMIN)
);
window.NAILS.ADMIN.registerPlugin(
    'nails/module-admin',
    'Wysiwyg',
    new Wysiwyg(window.NAILS.ADMIN)
);
