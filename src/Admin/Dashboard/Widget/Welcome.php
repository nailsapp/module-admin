<?php

namespace Nails\Admin\Admin\Dashboard\Widget;

use Nails\Admin\Interfaces\Dashboard\Widget;
use Nails\Common\Helper\Inflector;
use Nails\Config;

/**
 * Class Welcome
 *
 * @package Nails\Admin\Admin\Dashboard\Widget
 */
class Welcome extends Base
{
    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->getPhrase();
    }

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    public function getBody(): string
    {
        return sprintf(
            implode(PHP_EOL, [
                '<p>Welcome to %s Administration pages. From here you can control aspects of the site.</p>',
                '<p>Get started by choosing an option from the left.</p>',
            ]),
            Inflector::possessive(Config::get('APP_NAME'))
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the phrases to use
     *
     * @return string[]
     */
    protected function getPhrases(): array
    {
        $aPhrases = [
            'Be awesome.',
            'You look nice!',
            'What are we doing today?',
        ];

        if (activeUser('first_name')) {
            $aPhrases[] = 'Today is gonna be a good day, ' . activeUser('first_name') . '.';
            $aPhrases[] = 'Hey, ' . activeUser('first_name') . '!';

        } else {
            $aPhrases[] = 'Today is gonna be a good day.';
            $aPhrases[] = 'Hey!';
        }

        return $aPhrases;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns a random phrase
     *
     * @return string
     */
    protected function getPhrase(): string
    {
        return random_element($this->getPhrases());
    }
}
