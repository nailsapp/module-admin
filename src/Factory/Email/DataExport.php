<?php

namespace Nails\Admin\Factory\Email;

use Nails\Email\Factory\Email;

class DataExport extends Email
{
    protected $sType = 'data_export';

    // --------------------------------------------------------------------------

    /**
     * Returns test data to use when sending test emails
     *
     * @return array
     */
    public function getTestData(): array
    {
        return [
            'status' => 'COMPLETE',
        ];
    }
}
