<?php
class LocaleUrlTest extends TestCase
{
    public function testSetsDefaultLanguageIfNoLanguageSpecified()
    {
        $this->mockRequest('/');
        $this->mockLocaleUrl( [
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->assertEquals('en', Yii::$app->language);
        $request = Yii::$app->request;
        $this->assertEquals('', $request->pathInfo);
    }

    public function testSetsLanguageFromUrl()
    {
        $this->mockRequest('/en-us/site/page');
        $this->mockLocaleUrl([
            'languages' => ['en-US', 'en', 'de'],
        ]);
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
        $this->mockRequest('/de/site/page');
        $this->mockLocaleUrl([
            'languages' => ['en-US', 'de-*'],
        ]);
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
        $this->mockRequest('/deutsch/site/page');
        $this->mockLocaleUrl([
            'languages' => ['en-US', 'en', 'deutsch' => 'de'],
        ]);
        $this->assertEquals('de', Yii::$app->language);
        $this->assertEquals('de', Yii::$app->session->get('_language'));
        $cookie = Yii::$app->response->cookies->get('_language');
        $this->assertNotNull($cookie);
        $this->assertEquals('de', $cookie->value);
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testCanUseCountryAliasInUrl()
    {
        $this->mockRequest('/at/site/page');
        $this->mockLocaleUrl([
            'languages' => ['en-US', 'en', 'at' => 'de-AT', 'de'],
        ]);
        $this->assertEquals('de-AT', Yii::$app->language);
        $this->assertEquals('de-AT', Yii::$app->session->get('_language'));
        $cookie = Yii::$app->response->cookies->get('_language');
        $this->assertNotNull($cookie);
        $this->assertEquals('de-AT', $cookie->value);
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testCanUseLanguageWithWildcardCountryInUrl()
    {
        $this->mockRequest('/es-bo/site/page');
        $this->mockLocaleUrl([
            'languages' => ['en-US', 'deutsch' => 'de', 'es-*'],
        ]);
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
        $this->mockRequest('/en/site/page');
        $this->mockLocaleUrl([
            'languages' => ['en-US', 'en', 'de'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndDefaultLanguageUsesSuffix()
    {
        $this->expectRedirect('/en/site/page');
        $this->mockRequest('/site/page');
        $this->mockLocaleUrl([
            'languages' => ['en-US', 'en', 'de'],
            'enableDefaultSuffix' => true,
        ]);
    }

    public function testRedirectsIfDefaultLanguageInUrl()
    {
        $this->expectRedirect('');
        $this->mockRequest('/en');
        $this->mockLocaleUrl([
            'languages' => ['en'],
        ]);
    }

    public function testRedirectsIfLanguageWithUpperCaseCountryInUrl()
    {
        $this->expectRedirect('/es-bo/site/page');
        $this->mockRequest('/es-BO/site/page');
        $this->mockLocaleUrl([
            'languages' => ['en-US', 'deutsch' => 'de', 'es-BO'],
        ]);
    }

    public function testRedirectsIfLanguageWithUpperCaseWildcardCountryInUrl()
    {
        $this->expectRedirect('/es-bo/site/page');
        $this->mockRequest('/es-BO/site/page');
        $this->mockLocaleUrl([
            'languages' => ['en-US', 'deutsch' => 'de', 'es-*'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageMatches()
    {
        $this->expectRedirect('/de/site/page');
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de'],
        ]);
        $this->mockLocaleUrl([
            'languages' => ['en-US', 'en', 'de'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageMatchesWildcard()
    {
        $this->expectRedirect('/de/site/page');
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de'],
        ]);
        $this->mockLocaleUrl([
            'languages' => ['en-US', 'en', 'de-*'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithCountryMatches()
    {
        $this->expectRedirect('/de-at/site/page');
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de-AT', 'de', 'en'],
        ]);
        $this->mockLocaleUrl([
            'languages' => ['en-US', 'en', 'de', 'de-AT'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithCountryMatchesWildcard()
    {
        $this->expectRedirect('/de-at/site/page');
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de-AT', 'de', 'en'],
        ]);
        $this->mockLocaleUrl([
            'languages' => ['en-US', 'en', 'de-*'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithCountryMatchesCountryAlias()
    {
        $this->expectRedirect('/at/site/page');
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de-at', 'de'],
        ]);
        $this->mockLocaleUrl([
            'languages' => ['de', 'at'=>'de-AT'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageMatchesLanguageAndCountryAlias()
    {
        $this->expectRedirect('/de/site/page');
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockLocaleUrl([
            'languages' => ['de', 'at'=>'de-AT'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithLowercaseCountryMatches()
    {
        $this->expectRedirect('/de-at/site/page');
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de-at', 'de', 'en'],
        ]);
        $this->mockLocaleUrl([
            'languages' => ['en-US', 'en', 'de', 'de-AT'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithCountryMatchesLanguage()
    {
        $this->expectRedirect('/de/site/page');
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de-at'],
        ]);
        $this->mockLocaleUrl([
            'languages' => ['en-US', 'en', 'de'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndLanguageInSession()
    {
        $this->expectRedirect('/de/site/page');
        @session_start();
        $_SESSION['_language'] = 'de';
        $this->mockRequest('/site/page');
        $this->mockLocaleUrl( [
            'languages' => ['en-US', 'en', 'de'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndLanguageInSessionMatchesWildcard()
    {
        $this->expectRedirect('/de/site/page');
        @session_start();
        $_SESSION['_language'] = 'de';
        $this->mockRequest('/site/page');
        $this->mockLocaleUrl( [
            'languages' => ['en-US', 'en', 'de-*'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndLanguageInCookie()
    {
        $this->expectRedirect('/de/site/page');
        $_COOKIE['_language'] = 'de';
        $this->mockRequest('/site/page');
        $this->mockLocaleUrl( [
            'languages' => ['en-US', 'en', 'de'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndLanguageInCookieMatchesWildcard()
    {
        $this->expectRedirect('/de/site/page');
        $_COOKIE['_language'] = 'de';
        $this->mockRequest('/site/page');
        $this->mockLocaleUrl( [
            'languages' => ['en-US', 'en', 'de-*'],
        ]);
    }

    public function testCanDisableLanguageDetection()
    {
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['de'],
        ]);
        $this->mockLocaleUrl([
            'languages' => ['en-US', 'en', 'de'],
            'enableLanguageDetection' => false,
        ]);
        $this->assertEquals('en', Yii::$app->language);
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testCanDisablePersistence()
    {
        $this->mockRequest('/en-us/site/page');
        $this->mockLocaleUrl([
            'languages' => ['en-US', 'en', 'de'],
            'enablePersistence' => false,
        ]);
        $this->assertEquals('en-US', Yii::$app->language);
        $this->assertNull(Yii::$app->session->get('_language'));
        $this->assertNull(Yii::$app->response->cookies->get('_language'));
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }

    public function testCanDisableCookieOnly()
    {
        $this->mockRequest('/en-us/site/page');
        $this->mockLocaleUrl([
            'languages' => ['en-US', 'en', 'de'],
            'languageCookieDuration' => false,
        ]);
        $this->assertEquals('en-US', Yii::$app->language);
        $this->assertEquals('en-US', Yii::$app->session->get('_language'));
        $this->assertNull(Yii::$app->response->cookies->get('_language'));
        $request = Yii::$app->request;
        $this->assertEquals('site/page', $request->pathInfo);
    }
}
