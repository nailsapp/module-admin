<?php

namespace Nails\Admin\Api\Controller;

use Nails\Api\Controller\Base;
use Nails\Api\Exception\ApiException;
use Nails\Common\Exception\FactoryException;
use Nails\Factory;

class Notes extends Base
{
    /**
     * Lists notes for a given item
     *
     * @return Nails\Api\Factory\ApiResponse
     */
    public function getIndex()
    {
        list($oItem, $oItemModel) = $this->getItemAndModel();
        $oNotesModel = Factory::model('Notes', 'nailsapp/module-admin');

        return Factory::factory('ApiResponse', 'nailsapp/module-api')
                      ->setData(
                          array_map(
                              [$this, 'formatObject'],
                              $oNotesModel->getAll([
                                  'expand' => ['created_by'],
                                  'where'  => [
                                      ['model', get_class($oItemModel)],
                                      ['item_id', $oItem->id],
                                  ],
                              ])
                          )
                      );
    }

    // --------------------------------------------------------------------------

    /**
     * Counts notes for a given item
     *
     * @return Nails\Api\Factory\ApiResponse
     */
    public function getCount()
    {
        list($oItem, $oItemModel) = $this->getItemAndModel();
        $oNotesModel = Factory::model('Notes', 'nailsapp/module-admin');

        return Factory::factory('ApiResponse', 'nailsapp/module-api')
                      ->setData(
                          $oNotesModel->countAll([
                              'expand' => ['created_by'],
                              'where'  => [
                                  ['model', get_class($oItemModel)],
                                  ['item_id', $oItem->id],
                              ],
                          ])
                      );
    }

    // --------------------------------------------------------------------------

    /**
     * Creates a new note for a given item
     *
     * @return Nails\Api\Factory\ApiResponse
     */
    public function postIndex()
    {
        list($oItem, $oItemModel) = $this->getItemAndModel();

        $oInput   = Factory::service('Input');
        $sMessage = trim(strip_tags($oInput->post('message')));

        if (empty($sMessage)) {
            throw new ApiException('Message cannot be empty');
        }

        $oNotesModel      = Factory::model('Notes', 'nailsapp/module-admin');
        $iChangeLogItemId = $oNotesModel->create([
            'model'   => get_class($oItemModel),
            'item_id' => $oItem->id,
            'message' => $sMessage,
        ]);

        return Factory::factory('ApiResponse', 'nailsapp/module-api')
                      ->setData(
                          $this->formatObject(
                              $oNotesModel->getById($iChangeLogItemId, ['expand' => ['created_by']])
                          )
                      );
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the item and the item model
     *
     * @return array
     */
    protected function getItemAndModel()
    {
        $oInput = Factory::service('Input');

        try {
            $sModelName     = $oInput->get('model_name') ?: $oInput->post('model_name');
            $sModelProvider = $oInput->get('model_provider') ?: $oInput->post('model_provider');
            $oItemModel     = Factory::model($sModelName, $sModelProvider);
        } catch (FactoryException $e) {
            throw new ApiException('Invalid Model', 400);
        }

        $iItemId = (int) $oInput->get('id') ?: $oInput->post('id') ?: null;
        $oItem   = $oItemModel->getById($iItemId);
        if (empty($oItem)) {
            throw new ApiException('Invalid ID', 404);
        }

        return [$oItem, $oItemModel];
    }

    // --------------------------------------------------------------------------

    /**
     * Formats the response object
     *
     * @param \stdClass $oObj The object to format
     *
     * @return \stdClass
     */
    protected function formatObject($oObj)
    {
        return (object) [
            'message' => auto_typography($oObj->message),
            'date'    => toUserDatetime($oObj->created),
            'user'    => (object) [
                'id'         => $oObj->created_by ? (int) $oObj->created_by->id : null,
                'first_name' => $oObj->created_by ? $oObj->created_by->first_name : null,
                'last_name'  => $oObj->created_by ? $oObj->created_by->last_name : null,
            ],
        ];
    }
}
