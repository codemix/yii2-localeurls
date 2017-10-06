<?php
namespace codemix\localeurls;

use yii\base\Event;

/**
 * This event represents a change of persisted user language via URL.
 */
class LanguageChangedEvent extends Event
{
    /**
     * @var string the new language
     */
    public $language;

    /**
     * @var string|null the old language
     */
    public $oldLanguage;
}
