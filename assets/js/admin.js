'use strict';
let _ADMIN;

import DynamicTable from './components/DynamicTable.js';
import Sortable from './components/Sortable.js';
import IndexButtons from './components/IndexButtons.js';

_ADMIN = (function() {
    const dynamicTable = new DynamicTable();
    const sortable = new Sortable();
    const indexButtons = new IndexButtons();
})();
