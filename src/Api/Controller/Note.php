<?php

namespace Nails\Admin\Api\Controller;

use Nails\Admin\Constants;
use Nails\Api;
use Nails\Common\Exception\FactoryException;
use Nails\Factory;

class Note extends Api\Controller\CrudController
{
    const REQUIRE_AUTH          = true;
    const CONFIG_MODEL_NAME     = 'Note';
    const CONFIG_MODEL_PROVIDER = Constants::MODULE_SLUG;
    const CONFIG_LOOKUP_DATA    = ['expand' => ['created_by']];

    // --------------------------------------------------------------------------

    /**
     * Determines whether the user is authenticated or not
     *
     * @param string $sHttpMethod The HTTP Method protocol being used
     * @param string $sMethod     The controller method being executed
     *
     * @return bool
     */
    public static function isAuthenticated($sHttpMethod = '', $sMethod = '')
    {
        return parent::isAuthenticated($sHttpMethod, $sMethod) && isAdmin();
    }

    // --------------------------------------------------------------------------

    /**
     * Handles GET requests
     *
     * @param string $sMethod The method being called
     * @param array  $aData   Any data to apply to the requests
     *
     * @return Api\Factory\ApiResponse
     * @throws Api\Exception\ApiException
     * @throws FactoryException
     */
    public function getRemap($sMethod, array $aData = [])
    {
        list($sModel, $iItemId) = $this->getModelClassAndId();
        $aData['where'] = [
            ['model', $sModel],
            ['item_id', $iItemId],
        ];

        return parent::getRemap($sMethod, $aData);
    }

    // --------------------------------------------------------------------------

    /**
     * Counts the number of items for a specific model/item combination
     *
     * @param array $aData Any data to apply to the requests
     *
     * @return Api\Factory\ApiResponse
     * @throws Api\Exception\ApiException
     * @throws FactoryException
     */
    public function getCount(array $aData = [])
    {
        list($sModel, $iItemId) = $this->getModelClassAndId();
        $aData['where'] = [
            ['model', $sModel],
            ['item_id', $iItemId],
        ];

        return Factory::factory('ApiResponse', Api\Constants::MODULE_SLUG)
            ->setData($this->oModel->countAll($aData));
    }

    // --------------------------------------------------------------------------

    /**
     * Validates user input; adds the model to the response
     *
     * @param array     $aData The user data to validate
     * @param \stdClass $oItem The current object (when editing)
     *
     * @return array
     * @throws Api\Exception\ApiException
     * @throws FactoryException
     */
    protected function validateUserInput($aData, $oItem = null)
    {
        $aData = parent::validateUserInput($aData, $oItem);

        list($sModel) = $this->getModelClassAndId();
        $aData['model'] = $sModel;

        return $aData;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an arry of the model's class name and the item's ID
     *
     * @return array
     * @throws Api\Exception\ApiException
     * @throws FactoryException
     */
    protected function getModelClassAndId()
    {
        $oInput         = Factory::service('Input');
        $sModelName     = $oInput->get('model_name') ?: $oInput->post('model_name');
        $sModelProvider = $oInput->get('model_provider') ?: $oInput->post('model_provider');
        $iItemId        = (int) $oInput->get('item_id');

        try {
            $oModel = Factory::model($sModelName, $sModelProvider);
            $sModel = get_class($oModel);
        } catch (\Exception $e) {
            throw new Api\Exception\ApiException(
                '"' . $sModelProvider . ':' . $sModelName . '" is not a valid model'
            );
        }

        return [$sModel, $iItemId];
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
            'id'      => $oObj->id,
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
