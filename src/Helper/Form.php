<?php

/**
 * Form helper
 *
 * @package     Nails
 * @subpackage  nails/module-admin
 * @category    Helper
 * @author      Nails Dev Team
 */

namespace Nails\Admin\Helper;

use Nails\Common\Exception\NailsException;
use Nails\Common\Helper\ArrayHelper;
use Nails\Factory;

class Form
{
    /**
     * Generates a dynamic table
     *
     * @param array $aConfig The config array
     *
     * @return string
     * @throws NailsException
     *
     * @todo (Pablo - 2020-01-15) - Move this to mails/module-admin
     */
    public static function dynamicTable(array $aConfig): string
    {
        $sKey      = ArrayHelper::getFromArray('key', $aConfig, []);
        $aColumns  = ArrayHelper::getFromArray('columns', $aConfig, []);
        $bSortable = (bool) ArrayHelper::getFromArray('sortable', $aConfig, false);
        $sDefault  = ArrayHelper::getFromArray('default', $aConfig, '');

        if (!is_string($sDefault)) {
            $sDefault = json_encode($sDefault);
        }

        if (empty($aColumns)) {
            throw new NailsException('Columns must be provided when using the dynamic table form field');
        }

        $aHeaderCells = array_map(function ($sColumn) {
            return '<th>' . $sColumn . '</th>';
        }, array_keys($aColumns));

        $aBodyCells = array_map(function ($sColumn) {
            return '<td>' . $sColumn . '</td>';
        }, array_values($aColumns));

        if ($bSortable) {
            array_unshift($aHeaderCells, '<th style="width: 33px;"></th>');
            array_unshift(
                $aBodyCells,
                '<td>
                    <b class="fa fa-bars handle"></b>
                    <input type="hidden" name="' . $sKey . '[{{index}}][id]" value="{{id}}">
                    <input type="hidden" name="' . $sKey . '[{{index}}][order]" value="{{order}}" class="js-admin-sortable__order">
                </td>'
            );
            $sSortableClass = 'js-admin-sortable';
        } else {
            array_unshift($aHeaderCells, '<th style="display: none;"></th>');
            array_unshift(
                $aBodyCells,
                '<td style="display: none;">
                    <input type="hidden" name="' . $sKey . '[{{index}}][id]" value="{{id}}">
                </td>'
            );
            $sSortableClass = '';
        }

        array_push($aHeaderCells, '<th style="width: 33px;"></th>');
        array_push($aBodyCells, '<td><a href="#" class="btn btn-xs btn-danger js-admin-dynamic-table__remove">&times;</a></td>');

        $sHeaderCells = implode('', $aHeaderCells);
        $sBodyCells   = implode('', $aBodyCells);
        $sColSpan     = count($aHeaderCells);
        $sData        = htmlspecialchars($sDefault);

        return <<<EOT
            <table class="js-admin-dynamic-table" data-data="$sData">
                <thead>
                    <tr>
                        $sHeaderCells
                    </tr>
                </thead>
                <tbody class="js-admin-dynamic-table__template $sSortableClass" data-handle=".handle">
                    $sBodyCells
                </tbody>
                <tbody>
                    <tr>
                        <td colspan="$sColSpan">
                            <a href="#" class="btn btn-xs btn-primary js-admin-dynamic-table__add">
                                &plus; Add Row
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
EOT;
    }

    // --------------------------------------------------------------------------

    public static function form_field_dynamic_table(array $aField): string
    {
        $aField['html'] = static::dynamicTable($aField);
        return \Nails\Common\Helper\Form\Field::html($aField);
    }
}