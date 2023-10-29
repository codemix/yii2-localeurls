<?php

declare(strict_types=1);

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
    public string $language = '';

    /**
     * @var string|null the old language
     */
    public string|null $oldLanguage = null;
}
