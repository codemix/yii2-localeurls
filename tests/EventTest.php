<?php

use yii\helpers\Url;
use \codemix\localeurls\LanguageChangedEvent;

class EventTest extends TestCase
{
    protected $eventExpected = true;
    protected $eventFired = false;
    protected $expectedLanguage;
    protected $expectedOldLanguage;

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->eventExpected = true;
        $this->eventFired = false;
        $this->expectedLanguage = null;
        $this->expectedOldLanguage = null;
    }

    public function testFiresIfNoLanguagePersisted()
    {
        $this->mockUrlManager([
            'languages' => ['fr', 'en', 'de'],
            'on languageChanged' => [$this, 'languageChangedHandler'],
        ]);
        $this->expectedLanguage = 'fr';

        $this->assertFalse($this->eventFired);
        $this->mockRequest('/fr/site/page');
        $this->assertTrue($this->eventFired);
    }

    public function testFiresOnCookieLanguageChange()
    {
        $_COOKIE['_language'] = 'de';
        $this->mockUrlManager([
            'languages' => ['fr', 'en', 'de'],
            'on languageChanged' => [$this, 'languageChangedHandler'],
        ]);
        $this->expectedLanguage = 'fr';
        $this->expectedOldLanguage = 'de';

        $this->assertFalse($this->eventFired);
        $this->mockRequest('/fr/site/page');
        $this->assertTrue($this->eventFired);
    }

    public function testFiresOnSessionLanguageChange()
    {
        @session_start();
        $_SESSION['_language'] = 'de';
        $this->mockUrlManager([
            'languages' => ['fr', 'en', 'de'],
            'on languageChanged' => [$this, 'languageChangedHandler'],
        ]);
        $this->expectedLanguage = 'fr';
        $this->expectedOldLanguage = 'de';

        $this->assertFalse($this->eventFired);
        $this->mockRequest('/fr/site/page');
        $this->assertTrue($this->eventFired);
    }

    public function testFiresNotIfNoCookieLanguageChange()
    {
        $_COOKIE['_language'] = 'fr';
        $this->mockUrlManager([
            'languages' => ['fr', 'en', 'de'],
            'on languageChanged' => [$this, 'languageChangedHandler'],
        ]);

        $this->assertFalse($this->eventFired);
        $this->mockRequest('/fr/site/page');
        $this->assertFalse($this->eventFired);
    }

    public function testFiresNotIfNoSessionLanguageChange()
    {
        @session_start();
        $_SESSION['_language'] = 'fr';
        $this->mockUrlManager([
            'languages' => ['fr', 'en', 'de'],
            'on languageChanged' => [$this, 'languageChangedHandler'],
        ]);

        $this->assertFalse($this->eventFired);
        $this->mockRequest('/fr/site/page');
        $this->assertFalse($this->eventFired);
    }

    public function testFiresNotIfPersistenceDisabled()
    {
        $this->mockUrlManager([
            'languages' => ['fr', 'en', 'de'],
            'on languageChanged' => [$this, 'languageChangedHandler'],
            'enableLanguagePersistence' => false,
        ]);
        $this->expectedLanguage = 'fr';

        $this->assertFalse($this->eventFired);
        $this->mockRequest('/fr/site/page');
        $this->assertFalse($this->eventFired);
    }

    /**
     * Event handler
     */
    public function languageChangedHandler($event)
    {
        $this->assertInstanceOf('\codemix\localeurls\LanguageChangedEvent', $event);
        $this->assertTrue($this->eventExpected);
        $this->assertEquals($this->expectedLanguage, $event->language);
        $this->assertEquals($this->expectedOldLanguage, $event->oldLanguage);
        $this->eventFired = true;
    }
}
