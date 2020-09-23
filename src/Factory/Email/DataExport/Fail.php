<?php

namespace Nails\Admin\Factory\Email\DataExport;

use Nails\Email\Factory\Email;

class Fail extends Email
{
    protected $sType = 'data_export_fail';

    // --------------------------------------------------------------------------

    /**
     * Returns test data to use when sending test emails
     *
     * @return array
     */
    public function getTestData(): array
    {
        return [
            'error' => 'The error reason for the failure.',
        ];
    }
}
