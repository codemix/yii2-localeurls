<?php

use yii\helpers\Url;

class RedirectTest extends TestCase
{
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

    public function testRedirectsRootToDefaultLanguageIfDefaultLanguageUsesSuffix()
    {
        $this->expectRedirect('/en');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'enableDefaultLanguageUrlCode' => true,
        ]);
        $this->mockRequest('/');
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

    public function testRedirectsIfLanguageWithUppercaseCountryInUrl()
    {
        $this->expectRedirect('/es-bo/site/page');
        $this->mockUrlManager([
            'languages' => ['en-US', 'deutsch' => 'de', 'es-BO'],
        ]);
        $this->mockRequest('/es-BO/site/page');
    }

    public function testNoRedirectIfLanguageWithUppercaseCountryInUrlAndUppercaseEnabled()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'deutsch' => 'de', 'es-BO'],
            'keepUppercaseLanguageCode' => true,
        ]);
        $this->mockRequest('/es-BO/site/page');
    }

    public function testRedirectsIfLanguageWithUppercaseWildcardCountryInUrl()
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

    public function testNoRedirectIfNoLanguageInUrlAndAcceptedLanguageMatchesDefaultLanguage()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/site/page',[
            'acceptableLanguages' => ['en'],
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

    public function testRedirectsNoLanguageInUrlAndUppercaseLanguageInCookieAndUppercaseEnabled()
    {
        $this->expectRedirect('/en-US/site/page');
        $_COOKIE['_language'] = 'en-US';
        $this->mockUrlManager( [
            'languages' => ['en-US', 'en', 'de'],
            'keepUppercaseLanguageCode' => true,
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

    public function testRedirectsIfDefaultLanguageInUrlAndDefaultLanguageUsesNoSuffixAndTrailingSlashEnabled()
    {
        $this->expectRedirect('/site/page/');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'suffix' => '/'
        ]);
        $this->mockRequest('/en/site/page/');
    }

    public function testRedirectsToRootIfOnlyDefaultLanguageInUrlAndDefaultLanguageUsesNoSuffixAndTrailingSlashEnabled()
    {
        $this->expectRedirect('/');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'suffix' => '/',
        ]);
        $this->mockRequest('/en');
    }

    public function testRedirectsRootToDefaultLanguageIfDefaultLanguageUsesSuffixAndTrailingSlashEnabled()
    {
        $this->expectRedirect('/en/');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'enableDefaultLanguageUrlCode' => true,
            'suffix' => '/',
        ]);
        $this->mockRequest('/');
    }
}
