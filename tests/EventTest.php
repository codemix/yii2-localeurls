<?php

declare(strict_types=1);

namespace tests;

use codemix\localeurls\LanguageChangedEvent;

final class EventTest extends TestCase
{
    protected $eventExpected = true;
    protected $eventFired = false;
    protected $expectedLanguage;
    protected $expectedOldLanguage;

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->eventExpected = true;
        $this->eventFired = false;
        $this->expectedLanguage = null;
        $this->expectedOldLanguage = null;
    }

    public function testFiresIfNoLanguagePersisted(): void
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

    public function testFiresOnCookieLanguageChange(): void
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

    public function testFiresOnSessionLanguageChange(): void
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

    public function testFiresNotIfNoCookieLanguageChange(): void
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

    public function testFiresNotIfNoSessionLanguageChange(): void
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

    public function testFiresNotIfPersistenceDisabled(): void
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
    public function languageChangedHandler($event): void
    {
        $this->assertInstanceOf(LanguageChangedEvent::class, $event);
        $this->assertTrue($this->eventExpected);
        $this->assertEquals($this->expectedLanguage, $event->language);
        $this->assertEquals($this->expectedOldLanguage, $event->oldLanguage);
        $this->eventFired = true;
    }
}
