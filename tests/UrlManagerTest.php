<?php

declare(strict_types=1);

namespace tests;

use Yii;
use yii\helpers\Url;

/**
 * Generic tests for UrlManager.
 */
class UrlManagerTest extends TestCase
{
    public function testSetsDefaultLanguageIfNoLanguageSpecified(): void
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/');
        $this->assertEquals('en', Yii::$app->language);
        $request = Yii::$app->request;
        $this->assertEquals('', $request->pathInfo);
    }

    public function testSetsLanguageFromUrl(): void
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/en-us/site/page');
        $this->assertEquals('en-US', Yii::$app->language);
        $this->assertEquals('en-US', Yii::$app->session->get('_language'));
        $cookie = Yii::$app->response->cookies->get('_language');
        $this->assertNotNull($cookie);
        $this->assertEquals('en-US', $cookie->value);
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testSetsLanguageFromUrlOrder(): void
    {
        $this->mockUrlManager([
            'languages' => ['en', 'en-US', 'de'],
        ]);
        $this->mockRequest('/en-us/site/page');
        $this->assertEquals('en-US', Yii::$app->language);
        $this->assertEquals('en-US', Yii::$app->session->get('_language'));
        $cookie = Yii::$app->response->cookies->get('_language');
        $this->assertNotNull($cookie);
        $this->assertEquals('en-US', $cookie->value);
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testSetsLanguageFromUrlIfUppercaseEnabled(): void
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'keepUppercaseLanguageCode' => true,
        ]);
        $this->mockRequest('/en-US/site/page');
        $this->assertEquals('en-US', Yii::$app->language);
        $this->assertEquals('en-US', Yii::$app->session->get('_language'));
        $cookie = Yii::$app->response->cookies->get('_language');
        $this->assertNotNull($cookie);
        $this->assertEquals('en-US', $cookie->value);
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testSetsLanguageFromUrlIfItMatchesWildcard(): void
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'de-*'],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals('de', Yii::$app->language);
        $this->assertEquals('de', Yii::$app->session->get('_language'));
        $cookie = Yii::$app->response->cookies->get('_language');
        $this->assertNotNull($cookie);
        $this->assertEquals('de', $cookie->value);
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testCanUseLanguageAliasInUrl(): void
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'deutsch' => 'de'],
        ]);
        $this->mockRequest('/deutsch/site/page');
        $this->assertEquals('de', Yii::$app->language);
        $this->assertEquals('de', Yii::$app->session->get('_language'));
        $cookie = Yii::$app->response->cookies->get('_language');
        $this->assertNotNull($cookie);
        $this->assertEquals('de', $cookie->value);
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testCanUseLanguageWithWildcardCountryInUrl(): void
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'deutsch' => 'de', 'es-*'],
        ]);
        $this->mockRequest('/es-bo/site/page');
        $this->assertEquals('es-BO', Yii::$app->language);
        $this->assertEquals('es-BO', Yii::$app->session->get('_language'));
        $cookie = Yii::$app->response->cookies->get('_language');
        $this->assertNotNull($cookie);
        $this->assertEquals('es-BO', $cookie->value);
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testCanUseLanguageWithScriptCode(): void
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'deutsch' => 'de', 'sr-Latn'],
        ]);
        $this->mockRequest('/sr-latn/site/page');
        $this->assertEquals('sr-Latn', Yii::$app->language);
        $this->assertEquals('sr-Latn', Yii::$app->session->get('_language'));
        $cookie = Yii::$app->response->cookies->get('_language');
        $this->assertNotNull($cookie);
        $this->assertEquals('sr-Latn', $cookie->value);
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    /**
     * Tests for situations where no action is expected:
     */
    public function testDoesNothingIfLocaleUrlsDisabled(): void
    {
        $this->mockUrlManager([
            'enableLocaleUrls' => false,
            'languages' => ['en-US', 'en', 'de'],
            'rules' => [
                '' => 'site/index',
            ],
        ]);
        $this->mockRequest('/site/page', [
            'acceptableLanguages' => ['de'],
        ]);
        $this->assertEquals('en', Yii::$app->language);
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);

        // If a URL rule is configured for the home URL, it will always have a trailing slash
        $this->assertEquals($this->prepareUrl('/'), Url::to(['/site/index']));
        $this->assertEquals($this->prepareUrl('/?x=y'), Url::to(['/site/index', 'x' => 'y']));
        // Other URLs have no trailing slash
        $this->assertEquals($this->prepareUrl('/site/test'), Url::to(['/site/test']));
        $this->assertEquals($this->prepareUrl('/site/test?x=y'), Url::to(['/site/test', 'x' => 'y']));
    }

    public function testDoesNothingIfNoLanguagesConfigured(): void
    {
        $this->mockUrlManager([
            'languages' => [],
        ]);
        $this->mockRequest('/site/page', [
            'acceptableLanguages' => ['de'],
        ]);
        $this->assertEquals('en', Yii::$app->language);
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testDoesNothingIfUrlMatchesIgnoresUrls(): void
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'ignoreLanguageUrlPatterns' => [
                '#not/used#' => '#^site/page#',
            ],
        ]);
        $this->mockRequest('/site/page', [
            'acceptableLanguages' => ['de'],
        ]);
        $this->assertEquals('en', Yii::$app->language);
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testDoesNothingIfInvalidLanguageInCookie(): void
    {
        $_COOKIE['_language'] = 'fr';
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/site/page');
        $this->assertTrue(true);
    }

    public function testDoesNothingIfInvalidLanguageInSession(): void
    {
        @session_start();
        $_SESSION['_language'] = 'fr';
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/site/page');
        $this->assertTrue(true);
    }

    /**
     * Tests for disabled features:
     */
    public function testCanDisableLanguageDetection(): void
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'enableLanguageDetection' => false,
        ]);
        $this->mockRequest('/site/page', [
            'acceptableLanguages' => ['de'],
        ]);
        $this->assertEquals('en', Yii::$app->language);
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testCanDisablePersistence(): void
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'enableLanguagePersistence' => false,
        ]);
        $this->mockRequest('/en-us/site/page');
        $this->assertEquals('en-US', Yii::$app->language);
        $this->assertNull(Yii::$app->session->get('_language'));
        $this->assertNull(Yii::$app->response->cookies->get('_language'));
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testCanDisableCookieOnly(): void
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'languageCookieDuration' => false,
        ]);
        $this->mockRequest('/en-us/site/page');
        $this->assertEquals('en-US', Yii::$app->language);
        $this->assertEquals('en-US', Yii::$app->session->get('_language'));
        $this->assertNull(Yii::$app->response->cookies->get('_language'));
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testCanDisableSessionOnly(): void
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'languageSessionKey' => false,
        ]);
        $this->mockRequest('/en-us/site/page');
        $this->assertEquals('en-US', Yii::$app->language);
        $this->assertNull(Yii::$app->session->get('_language'));
        $this->assertEquals('en-US', Yii::$app->response->cookies->get('_language'));
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }
}
