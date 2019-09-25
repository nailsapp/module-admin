'use strict';
let _ADMIN;

import '../sass/admin.scss';
import CopyToClipboard from './components/CopyToClipboard.js';
import DisabledElements from './components/DisabledElements.js';
import DynamicTable from './components/DynamicTable.js';
import IndexButtons from './components/IndexButtons.js';
import Notes from './components/Notes.js';
import Repeater from './components/Repeater.js';
import Searcher from './components/Searcher.js';
import Sortable from './components/Sortable.js';

_ADMIN = (function() {
    new CopyToClipboard();
    new DisabledElements();
    new DynamicTable();
    new IndexButtons();
    new Notes();
    new Repeater();
    new Searcher();
    new Sortable();
})();
