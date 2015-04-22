<?php
class LocaleUrlWithoutScriptNameTest extends TestCase
{
    protected $showScriptName = false;

    public function testUsesDefaultLanguageIfNoLanguageSpecified()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en-us', 'en', 'de'],
            ],
            'request' => [
                'url' => '/site/page',
            ]
        ]);
        $this->assertEquals('en', Yii::$app->language);
    }

    public function testUsesLanguageFromUrl()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en-US', 'en', 'de'],
            ],
            'request' => [
                'url' => '/en-US/site/page',
            ]
        ]);
        $this->assertEquals('en-US', Yii::$app->language);
        $this->assertEquals('en-US', Yii::$app->session->get('_language'));
        $cookie = Yii::$app->response->cookies->get('_language');
        $this->assertNotNull($cookie);
        $this->assertEquals('en-US', $cookie->value);
    }

    public function testUsesAliasFormUrl()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en-us', 'en', 'deutsch' => 'de'],
            ],
            'request' => [
                'url' => '/deutsch/site/page',
            ]
        ]);
        $this->assertEquals('de', Yii::$app->language);
        $this->assertEquals('de', Yii::$app->session->get('_language'));
        $cookie = Yii::$app->response->cookies->get('_language');
        $this->assertNotNull($cookie);
        $this->assertEquals('de', $cookie->value);
    }

    public function testAcceptsWildCardCountry()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en-us', 'deutsch' => 'de', 'es-*'],
            ],
            'request' => [
                'url' => '/index.php/es-bo/site/page',
            ]
        ]);
        $this->assertEquals('es-BO', Yii::$app->language);
        $this->assertEquals('es-BO', Yii::$app->session->get('_language'));
        $cookie = Yii::$app->response->cookies->get('_language');
        $this->assertNotNull($cookie);
        $this->assertEquals('es-BO', $cookie->value);
    }

    /**
     * @expectedException \yii\base\Exception
     * @expectedExceptionMessage /es-bo/site/page
     */
    public function testRedirectsIfUpperCaseWildCardCountry()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en-us', 'deutsch' => 'de', 'es-*'],
            ],
            'request' => [
                'url' => '/index.php/es-BO/site/page',
            ]
        ]);
    }

    /**
     * @expectedException \yii\base\Exception
     * @expectedExceptionMessage /site/page
     */
    public function testRedirectsIfDefaultLanguage()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en-us', 'en', 'de'],
            ],
            'request' => [
                'url' => '/en/site/page',
            ]
        ]);
    }

    /**
     * @expectedException \yii\base\Exception
     * @expectedExceptionMessage /en/site/page
     */
    public function testRedirectsToDefaultSuffixIfNoLanguageSpecified()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en-us', 'en', 'de'],
                'enableDefaultSuffix' => true,
            ],
            'request' => [
                'url' => '/site/page',
            ]
        ]);
    }

    /**
     * @expectedException \yii\base\Exception
     * @expectedExceptionMessage /de/site/page
     */
    public function testRedirectsOnAcceptedLanguage()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en-us', 'en', 'de'],
            ],
            'request' => [
                'url' => '/site/page',
                'acceptableLanguages' => ['de'],
            ]
        ]);
    }

    /**
     * @expectedException \yii\base\Exception
     * @expectedExceptionMessage /at/site/page
     */
    public function testRedirectsOnAcceptedLanguageWithCountry()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en-us', 'en', 'at'=>'de-AT'],
            ],
            'request' => [
                'url' => '/site/page',
                'acceptableLanguages' => ['de-AT'],
            ]
        ]);
    }

    /**
     * @expectedException \yii\base\Exception
     * @expectedExceptionMessage /at/site/page
     */
    public function testRedirectsOnAcceptedLanguageWithCountryInLowercase()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en-us', 'en', 'de', 'at' => 'de-AT'],
            ],
            'request' => [
                'url' => '/site/page',
                'acceptableLanguages' => ['de-at'],
            ]
        ]);
    }

    /**
     * @expectedException \yii\base\Exception
     * @expectedExceptionMessage /de/site/page
     */
    public function testRedirectsToFallbackAcceptedLanguage()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en-us', 'en', 'de'],
            ],
            'request' => [
                'url' => '/site/page',
                'acceptableLanguages' => ['de-de'],
            ]
        ]);
    }
}
