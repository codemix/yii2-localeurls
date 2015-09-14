<?php

use yii\helpers\Url;

class UrlManagerTest extends TestCase
{
    public function testSetsDefaultLanguageIfNoLanguageSpecified()
    {
        $this->mockUrlManager( [
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/');
        $this->assertEquals('en', Yii::$app->language);
        $request = Yii::$app->request;
        $this->assertEquals('', $request->pathInfo);
    }

    public function testSetsLanguageFromUrl()
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

    public function testSetsLanguageFromUrlIfItMatchesWildcard()
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

    public function testCanUseLanguageAliasInUrl()
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

    public function testCanUseLanguageWithWildcardCountryInUrl()
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

    public function testRedirectsIfDefaultLanguageInUrlAndDefaultLanguageUsesNoSuffix()
    {
        $this->expectRedirect('/site/page');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/en/site/page');
    }

    public function testRedirectsToRootIfOnlyDefaultLanguageInUrlAndDefaultLanguageUsesNoSuffix()
    {
        $this->expectRedirect('/');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/en');
    }

    public function testRedirectsIfNoLanguageInUrlAndDefaultLanguageUsesSuffix()
    {
        $this->expectRedirect('/en/site/page');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'enableDefaultLanguageUrlCode' => true,
        ]);
        $this->mockRequest('/site/page');
    }

    public function testRedirectsIfDefaultLanguageInUrl()
    {
        $this->expectRedirect('/');
        $this->mockUrlManager([
            'languages' => ['en'],
        ]);
        $this->mockRequest('/en');
    }

    public function testRedirectsIfLanguageWithUpperCaseCountryInUrl()
    {
        $this->expectRedirect('/es-bo/site/page');
        $this->mockUrlManager([
            'languages' => ['en-US', 'deutsch' => 'de', 'es-BO'],
        ]);
        $this->mockRequest('/es-BO/site/page');
    }

    public function testRedirectsIfLanguageWithUpperCaseWildcardCountryInUrl()
    {
        $this->expectRedirect('/es-bo/site/page');
        $this->mockUrlManager([
            'languages' => ['en-US', 'deutsch' => 'de', 'es-*'],
        ]);
        $this->mockRequest('/es-BO/site/page');
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageMatches()
    {
        $this->expectRedirect('/de/site/page');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageMatchesWildcard()
    {
        $this->expectRedirect('/de/site/page');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de-*'],
        ]);
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithCountryMatches()
    {
        $this->expectRedirect('/de-at/site/page');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de', 'de-AT'],
        ]);
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de-AT', 'de', 'en'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithCountryMatchesWildcard()
    {
        $this->expectRedirect('/de-at/site/page');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de-*'],
        ]);
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de-AT', 'de', 'en'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithCountryMatchesCountryAlias()
    {
        $this->expectRedirect('/at/site/page');
        $this->mockUrlManager([
            'languages' => ['de', 'at'=>'de-AT'],
        ]);
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de-at', 'de'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageMatchesLanguageAndCountryAlias()
    {
        $this->expectRedirect('/de/site/page');
        $this->mockUrlManager([
            'languages' => ['de', 'at'=>'de-AT'],
        ]);
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['en-US', 'en', 'de'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithLowercaseCountryMatches()
    {
        $this->expectRedirect('/de-at/site/page');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de', 'de-AT'],
        ]);
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de-at', 'de', 'en'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithCountryMatchesLanguage()
    {
        $this->expectRedirect('/de/site/page');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de-at'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndLanguageInSession()
    {
        $this->expectRedirect('/de/site/page');
        @session_start();
        $_SESSION['_language'] = 'de';
        $this->mockUrlManager( [
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/site/page');
    }

    public function testRedirectsIfNoLanguageInUrlAndLanguageInSessionMatchesWildcard()
    {
        $this->expectRedirect('/de/site/page');
        @session_start();
        $_SESSION['_language'] = 'de';
        $this->mockUrlManager( [
            'languages' => ['en-US', 'en', 'de-*'],
        ]);
        $this->mockRequest('/site/page');
    }

    public function testRedirectsIfNoLanguageInUrlAndLanguageInCookie()
    {
        $this->expectRedirect('/de/site/page');
        $_COOKIE['_language'] = 'de';
        $this->mockUrlManager( [
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/site/page');
    }

    public function testRedirectsIfNoLanguageInUrlAndLanguageInCookieMatchesWildcard()
    {
        $this->expectRedirect('/de/site/page');
        $_COOKIE['_language'] = 'de';
        $this->mockUrlManager( [
            'languages' => ['en-US', 'en', 'de-*'],
        ]);
        $this->mockRequest('/site/page');
    }

    public function testRedirectsIfUrlDoesNotMatchIgnoresUrls()
    {
        $this->expectRedirect('/site/page');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'ignoreLanguageUrlPatterns' => [
                '#not/used#' => '#^site/other#'
            ],
        ]);
        $this->mockRequest('/en/site/page');
    }

    public function testDoesNothingIfLocaleUrlsDisabled()
    {
        $this->mockUrlManager([
            'enableLocaleUrls' => false,
            'languages' => ['en-US', 'en', 'de'],
            'rules' => [
                '' => 'site/index',
            ],
        ]);
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de'],
        ]);
        $this->assertEquals('en', Yii::$app->language);
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);

        // If a URL rule is configured for the home URL, it will always have a trailing slash
        $this->assertEquals($this->prepareUrl('/'), Url::to(['/site/index']));
        // Other URLs have no trailing slash
        $this->assertEquals($this->prepareUrl('/site/test'), Url::to(['/site/test']));
    }

    public function testDoesNothingIfNoLanguagesConfigured()
    {
        $this->mockUrlManager([
            'languages' => [],
        ]);
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de'],
        ]);
        $this->assertEquals('en', Yii::$app->language);
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testDoesNothingIfUrlMatchesIgnoresUrls()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'ignoreLanguageUrlPatterns' => [
                '#not/used#' => '#^site/page#'
            ],
        ]);
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de'],
        ]);
        $this->assertEquals('en', Yii::$app->language);
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testDoesNothingIfInvalidLanguageInCookie()
    {
        $_COOKIE['_language'] = 'fr';
        $this->mockUrlManager( [
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/site/page');
    }

    public function testDoesNothingIfInvalidLanguageInSession()
    {
        @session_start();
        $_SESSION['_language'] = 'fr';
        $this->mockUrlManager( [
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/site/page');
    }

    public function testCanDisableLanguageDetection()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'enableLanguageDetection' => false,
        ]);
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de'],
        ]);
        $this->assertEquals('en', Yii::$app->language);
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testCanDisablePersistence()
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

    public function testCanDisableCookieOnly()
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

    public function testCreateNormalUrlIfLocaleUrlsDisabled()
    {
        $this->mockUrlManager([
            'enableLocaleUrls' => false,
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/site/page');
        $this->assertEquals($this->prepareUrl('/demo/action?language=de'), Url::to(['/demo/action', 'language' => 'de']));
    }

    public function testCreateNormalUrlIfNoLanguagesConfigured()
    {
        $this->mockUrlManager([
            'languages' => [],
        ]);
        $this->mockRequest('/site/page');
        $this->assertEquals($this->prepareUrl('/demo/action?language=de'), Url::to(['/demo/action', 'language' => 'de']));
    }

    public function testCreateUrlWithoutLanguageIfNoLanguageInUrl()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/site/page');
        $this->assertEquals($this->prepareUrl('/demo/action'), Url::to(['/demo/action']));
    }

    public function testCreateUrlWithLanguageFromUrl()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals($this->prepareUrl('/de/demo/action'), Url::to(['/demo/action']));
    }

    public function testCreateHomeUrlWithLanguageFromUrl()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'rules' => [
                '' => 'site/index',
            ],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals($this->prepareUrl('/de'), Url::to(['/site/index']));
    }

    public function testCreateUrlWithSpecificLanguage()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals($this->prepareUrl('/en-us/demo/action'), Url::to(['/demo/action', 'language' => 'en-US']));
    }

    public function testCreateUrlWithSpecificAliasedLanguage()
    {
        $this->mockUrlManager([
            'languages' => ['fr', 'en', 'deutsch' => 'de'],
        ]);
        $this->mockRequest('/fr/site/page');
        $this->assertEquals($this->prepareUrl('/deutsch/demo/action'), Url::to(['/demo/action', 'language' => 'de']));
    }

    public function testCreateNormalUrlIfIgnoreRoutesMatches()
    {
        $this->mockUrlManager([
            'languages' => ['en-us', 'en', 'de'],
            'ignoreLanguageUrlPatterns' => [
                '#^demo/.*#' => '#not/used#'
            ],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals($this->prepareUrl('/demo/action'), Url::to(['/demo/action']));
    }

    public function testCreateUrlWithLanguageFromUrlIfIgnoreRouteDoesNotMatch()
    {
        $this->mockUrlManager([
            'languages' => ['en-us', 'en', 'de'],
            'ignoreLanguageUrlPatterns' => [
                '#^other/.*#' => '#not/used#'
            ],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals($this->prepareUrl('/de/demo/action'), Url::to(['/demo/action']));
    }
}
