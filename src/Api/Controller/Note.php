<?php

namespace Nails\Admin\Api\Controller;

use Nails\Admin\Constants;
use Nails\Admin\Traits\Api\RestrictToAdmin;
use Nails\Api;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Service\Input;
use Nails\Factory;

/**
 * Class Note
 *
 * @package Nails\Admin\Api\Controller
 */
class Note extends Api\Controller\CrudController
{
    use RestrictToAdmin;

    // --------------------------------------------------------------------------

    const CONFIG_MODEL_NAME     = 'Note';
    const CONFIG_MODEL_PROVIDER = Constants::MODULE_SLUG;
    const CONFIG_LOOKUP_DATA    = ['expand' => ['created_by']];

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
        [$sModel, $iItemId] = $this->getModelClassAndId();
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
        [$sModel, $iItemId] = $this->getModelClassAndId();
        $aData['where'] = [
            ['model', $sModel],
            ['item_id', $iItemId],
        ];

        /** @var Api\Factory\ApiResponse $oApiResponse */
        $oApiResponse = Factory::factory('ApiResponse', Api\Constants::MODULE_SLUG);
        $oApiResponse
            ->setData($this->oModel->countAll($aData));

        return $oApiResponse;
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
    protected function validateUserInput($aData, $oItem = null): array
    {
        $aData = parent::validateUserInput($aData, $oItem);

        [$sModel] = $this->getModelClassAndId();
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
    protected function getModelClassAndId(): array
    {
        /** @var Input $oInput */
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
    protected function formatObject($oObj): \stdClass
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
