<?php

namespace Nails\Admin\DataExport;

class SourceResponse
{
    /**
     * The human-friendly label
     *
     * @var string
     */
    protected $sLabel = '';

    // --------------------------------------------------------------------------
    /**
     * The exported file name
     *
     * @var string
     */
    protected $sFileName = '';

    // --------------------------------------------------------------------------

    /**
     * The field names
     *
     * @var array
     */
    protected $aFields = [];

    // --------------------------------------------------------------------------

    /**
     * The rows of data, if not using $oSource
     *
     * @var array
     */
    protected $aData = [];

    // --------------------------------------------------------------------------

    /**
     * A data resource if not using $aData
     *
     * @var \PDOStatement|\CI_DB_mysqli_result
     */
    protected $oSource;

    // --------------------------------------------------------------------------

    /**
     * A function which formats each row
     *
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
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->sLabel;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the data set's file name
     *
     * @param string $sFileName The data set's file name
     *
     * @return $this
     */
    public function setFileName($sFileName)
    {
        $this->sFileName = $sFileName;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the data set's file name
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->sFileName;
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
     *
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
     *
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
     * @param \PDOStatement|\CI_DB_mysqli_result $oSource The data source
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
     *
     * @return \PDOStatement|\CI_DB_mysqli_result
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
            $this->oSource->data_seek(0);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the next item in the data set, passing it to the formatter, if defined
     *
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
            $oRow = $this->oSource->unbuffered_row();
        }

        return is_callable($this->cFormatter) ? call_user_func($this->cFormatter, $oRow) : $oRow;
    }
}
