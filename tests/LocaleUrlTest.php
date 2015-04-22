<?php
class LocaleUrlTest extends TestCase
{
    protected $redirects = [
        'testRedirectsIfUpperCaseWildCardCountry' => '/es-bo/site/page',
        'testRedirectsIfDefaultLanguage' => '/site/page',
        'testRedirectsToDefaultSuffixIfNoLanguageSpecified' => '/en/site/page',
        'testRedirectsOnAcceptedLanguage' => '/de/site/page',
        'testRedirectsOnAcceptedLanguageWithCountry' => '/at/site/page',
        'testRedirectsOnAcceptedLanguageWithCountryInLowercase' => '/at/site/page',
        'testRedirectsToFallbackAcceptedLanguage' => '/de/site/page',
    ];

    public function testUsesDefaultLanguageIfNoLanguageSpecified()
    {
        $this->mockRequest('/');
        $this->mockLocaleUrl( [
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->assertEquals('en', Yii::$app->language);
    }

    public function testUsesLanguageFromUrl()
    {
        $this->mockRequest('/en-US/site/page');
        $this->mockLocaleUrl([
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->assertEquals('en-US', Yii::$app->language);
        $this->assertEquals('en-US', Yii::$app->session->get('_language'));
        $cookie = Yii::$app->response->cookies->get('_language');
        $this->assertNotNull($cookie);
        $this->assertEquals('en-US', $cookie->value);
    }

    public function testUsesAliasFormUrl()
    {
        $this->mockRequest('/deutsch/site/page');
        $this->mockLocaleUrl([
            'languages' => ['en-us', 'en', 'deutsch' => 'de'],
        ]);
        $this->assertEquals('de', Yii::$app->language);
        $this->assertEquals('de', Yii::$app->session->get('_language'));
        $cookie = Yii::$app->response->cookies->get('_language');
        $this->assertNotNull($cookie);
        $this->assertEquals('de', $cookie->value);
    }

    public function testAcceptsWildCardCountry()
    {
        $this->mockRequest('/es-bo/site/page');
        $this->mockLocaleUrl([
            'languages' => ['en-us', 'deutsch' => 'de', 'es-*'],
        ]);
        $this->assertEquals('es-BO', Yii::$app->language);
        $this->assertEquals('es-BO', Yii::$app->session->get('_language'));
        $cookie = Yii::$app->response->cookies->get('_language');
        $this->assertNotNull($cookie);
        $this->assertEquals('es-BO', $cookie->value);
    }

    public function testRedirectsIfUpperCaseWildCardCountry()
    {
        $this->expectRedirect(__METHOD__);
        $this->mockRequest('/es-BO/site/page');
        $this->mockLocaleUrl([
            'languages' => ['en-us', 'deutsch' => 'de', 'es-*'],
        ]);
    }

    public function testRedirectsIfDefaultLanguage()
    {
        $this->expectRedirect(__METHOD__);
        $this->mockRequest('/en/site/page');
        $this->mockLocaleUrl([
            'languages' => ['en-us', 'en', 'de'],
        ]);
    }

    public function testRedirectsToDefaultSuffixIfNoLanguageSpecified()
    {
        $this->expectRedirect(__METHOD__);
        $this->mockRequest('/site/page');
        $this->mockLocaleUrl([
            'languages' => ['en-us', 'en', 'de'],
            'enableDefaultSuffix' => true,
        ]);
    }

    public function testRedirectsOnAcceptedLanguage()
    {
        $this->expectRedirect(__METHOD__);
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de'],
        ]);
        $this->mockLocaleUrl([
            'languages' => ['en-us', 'en', 'de'],
        ]);
    }

    public function testRedirectsOnAcceptedLanguageWithCountry()
    {
        $this->expectRedirect(__METHOD__);
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de-at', 'de', 'en'],
        ]);
        $this->mockLocaleUrl([
            'languages' => ['en-us', 'en', 'de', 'at'=>'de-AT'],
        ]);
    }

    public function testRedirectsOnAcceptedLanguageWithCountryInLowercase()
    {
        $this->expectRedirect(__METHOD__);
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de-at'],
        ]);
        $this->mockLocaleUrl([
            'languages' => ['en-us', 'en', 'de', 'at' => 'de-AT'],
        ]);
    }

    public function testRedirectsToFallbackAcceptedLanguage()
    {
        $this->expectRedirect(__METHOD__);
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de-de'],
        ]);
        $this->mockLocaleUrl([
            'languages' => ['en-us', 'en', 'de'],
        ]);
    }
}
