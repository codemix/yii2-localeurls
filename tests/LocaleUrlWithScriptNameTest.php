<?php
class LocaleUrlWithScriptNameTest extends TestCase
{
    protected $showScriptName = true;

    public function testUsesDefaultLanguageIfNoLanguageSpecified()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en-us', 'en', 'de'],
            ],
            'request' => [
                'url' => '/index.php/site/page',
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
                'url' => '/index.php/en-US/site/page',
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
                'url' => '/index.php/deutsch/site/page',
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
     * @expectedExceptionMessage /index.php/es-bo/site/page
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
     * @expectedExceptionMessage /index.php/site/page
     */
    public function testRedirectsIfDefaultLanguage()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en-us', 'en', 'de'],
            ],
            'request' => [
                'url' => '/index.php/en/site/page',
            ]
        ]);
    }

    /**
     * @expectedException \yii\base\Exception
     * @expectedExceptionMessage /index.php/en/site/page
     */
    public function testRedirectsToDefaultSuffixIfNoLanguageSpecified()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en-us', 'en', 'de'],
                'enableDefaultSuffix' => true,
            ],
            'request' => [
                'url' => '/index.php/site/page',
            ]
        ]);
    }

    /**
     * @expectedException \yii\base\Exception
     * @expectedExceptionMessage /index.php/de/site/page
     */
    public function testRedirectsOnAcceptedLanguage()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en-us', 'en', 'de'],
            ],
            'request' => [
                'url' => '/index.php/site/page',
                'acceptableLanguages' => ['de'],
            ]
        ]);
    }

    /**
     * @expectedException \yii\base\Exception
     * @expectedExceptionMessage /index.php/at/site/page
     */
    public function testRedirectsOnAcceptedLanguageWithCountry()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en-us', 'en', 'de', 'at'=>'de-AT'],
            ],
            'request' => [
                'url' => '/index.php/site/page',
                'acceptableLanguages' => ['de-at', 'de', 'en'],
            ]
        ]);
    }

    /**
     * @expectedException \yii\base\Exception
     * @expectedExceptionMessage /index.php/at/site/page
     */
    public function testRedirectsOnAcceptedLanguageWithCountryInLowercase()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en-us', 'en', 'de', 'at' => 'de-AT'],
            ],
            'request' => [
                'url' => '/index.php/site/page',
                'acceptableLanguages' => ['de-at'],
            ]
        ]);
    }

    /**
     * @expectedException \yii\base\Exception
     * @expectedExceptionMessage /index.php/de/site/page
     */
    public function testRedirectsToFallbackAcceptedLanguage()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en-us', 'en', 'de'],
            ],
            'request' => [
                'url' => '/index.php/site/page',
                'acceptableLanguages' => ['de-de'],
            ]
        ]);
    }
}
