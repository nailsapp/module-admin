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
    const CONFIGURABLE = true;

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return 'Welcome Phrase';
    }

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return 'A lovely welcome message';
    }

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    public function getImage(): ?string
    {
        return null;
    }

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    public function getBody(): string
    {
        return sprintf(
            implode(PHP_EOL, [
                '<p>' . $this->getPhrase() . '</p>',
            ]),
            Inflector::possessive(Config::get('APP_NAME'))
        );
    }

    // --------------------------------------------------------------------------

    public function getConfig(): string
    {
        return implode(PHP_EOL, [
            '<fieldset>',
            '<legend>Details</legend>',
            form_field_dropdown([
                'key'     => 'phrase',
                'label'   => 'Phrase',
                'options' => $this->getPhrases(),
                'class'   => 'select2',
                'default' => $this->aConfig['phrase'] ?? null,
            ]),
            '</fieldset>',
        ]);
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
        $aPhrases = $this->getPhrases();

        return !empty($this->aConfig['phrase'])
            ? $aPhrases[$this->aConfig['phrase']] ?? 'Config defined, but invalid'
            : random_element($aPhrases);
    }
}
