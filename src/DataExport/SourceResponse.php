<?php

namespace Nails\Admin\DataExport;

class SourceResponse
{
    /**
     * The human-friendly label
     * @var string
     */
    protected $sLabel = '';

    // --------------------------------------------------------------------------
    /**
     * The exported filename
     * @var string
     */
    protected $sFilename = '';

    // --------------------------------------------------------------------------

    /**
     * The field names
     * @var array
     */
    protected $aFields = [];

    // --------------------------------------------------------------------------

    /**
     * The rows of data, if not using $oSource
     * @var array
     */
    protected $aData = [];

    // --------------------------------------------------------------------------

    /**
     * A data resource if not using $aData
     * @var \PDOStatement
     */
    protected $oSource;

    // --------------------------------------------------------------------------

    /**
     * A function which formats each row
     * @var callable
     */
    protected $cFormatter;

    // --------------------------------------------------------------------------

    /**
     * Sets the data set's label
     *
     * @param string $sLabel The data set's label
     *
     * @return $this
     */
    public function setLabel($sLabel)
    {
        $this->sLabel = $sLabel;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the data set's label
     * @return string
     */
    public function getLabel()
    {
        return $this->sLabel;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the data set's filename
     *
     * @param string $sFilename The data set's filename
     *
     * @return $this
     */
    public function setFilename($sFilename)
    {
        $this->sFilename = $sFilename;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the data set's filename
     * @return string
     */
    public function getFilename()
    {
        return $this->sFilename;
    }

    // --------------------------------------------------------------------------

    /**
     * The fields/columns for the export
     *
     * @param array $aFields An array of fields/column names
     *
     * @return $this
     */
    public function setFields(array $aFields = [])
    {
        $this->aFields = $aFields;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the field array
     * @return array
     */
    public function getFields()
    {
        return $this->aFields;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the data for the export
     *
     * @param array $aData The data to export
     *
     * @return $this
     */
    public function setData(array $aData)
    {
        $this->aData = $aData;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the data array
     * @return array
     */
    public function getData()
    {
        return $this->aData;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the resource
     *
     * @param \PDOStatement $oSource The data source
     *
     * @return $this
     */
    public function setSource($oSource)
    {
        //  @todo (Pablo - 2018-02-19) - verify is correct type
        $this->oSource = $oSource;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the source
     * @return \PDOStatement
     */
    public function getSource()
    {
        return $this->oSource;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the formatting function to use
     *
     * @param callable $cFormatter The formatting function
     *
     * @return $this
     */
    public function setFormatter($cFormatter)
    {
        $this->cFormatter = $cFormatter;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Resets the pointer in the data set to the beginning
     */
    public function reset()
    {
        if (!empty($this->aData)) {
            reset($this->aData);
        } elseif ($this->oSource instanceof \PDOStatement) {
            //  unsupported
        } elseif ($this->oSource instanceof \CI_DB_mysqli_result) {
            //  @todo (Pablo - 2018-02-19) - Watch compatibility with this function as it's marked as private
            $this->oSource->_data_seek(0);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the next item in the data set, passing it to the formatter, if defined
     * @return \stdClass
     */
    public function getNextItem()
    {
        $oRow = null;

        if (!empty($this->aData)) {
            $oRow = current($this->aData);
            next($this->aData);
        } elseif ($this->oSource instanceof \PDOStatement) {
            $oRow = $this->oSource->fetch(\PDO::FETCH_ASSOC);
        } elseif ($this->oSource instanceof \CI_DB_mysqli_result) {
            //  @todo (Pablo - 2018-02-19) - this should use unbuffered_row() when CI is upgraded
            $oRow = $this->oSource->_fetch_object();
        }

        return is_callable($this->cFormatter) ? call_user_func($this->cFormatter, $oRow) : $oRow;
    }
}
