<?php

namespace Nails\Admin\Factory\Email\DataExport;

use Nails\Email\Factory\Email;

class Success extends Email
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
        return [];
    }
}
