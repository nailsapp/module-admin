<?php

namespace Nails\Admin\Factory\Model\Field;

use Nails\Admin\Helper\Form;
use Nails\Common\Factory\Model\Field;

class DynamicTable extends Field
{
    /** @var string */
    public $type = Form::FIELD_DYNAMIC_TABLE;

    /** @var array */
    public $columns = [];

    /** @var bool */
    public $sortable = false;

    // --------------------------------------------------------------------------

    /**
     * Sets the columns
     *
     * @param array $aColumns
     *
     * @return $this
     */
    public function setColumns(array $aColumns): self
    {
        $this->columns = $aColumns;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the sortable
     *
     * @param bool $bSortable
     *
     * @return $this
     */
    public function setSortable(bool $bSortable): self
    {
        $this->sortable = $bSortable;
        return $this;
    }
}
