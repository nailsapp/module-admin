'use strict';
let _ADMIN;

import '../sass/admin.scss';
import DynamicTable from './components/DynamicTable.js';
import IndexButtons from './components/IndexButtons.js';
import Notes from './components/Notes.js';
import Searcher from './components/Searcher.js';
import Sortable from './components/Sortable.js';

_ADMIN = (function() {
    const dynamicTable = new DynamicTable();
    const indexButtons = new IndexButtons();
    const notes = new Notes();
    const searcher = new Searcher();
    const sortable = new Sortable();
})();
