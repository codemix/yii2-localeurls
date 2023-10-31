<?php

declare(strict_types=1);

namespace tests;

class SlugRedirectTest extends TestCase
{
    public function mockUrlManager($config = []): void
    {
        if (!isset($config['rules'])) {
            $config['rules'] = [];
        }
        $config['rules']['/foo/<term:.+>/bar'] = 'slug/action';

        parent::mockUrlManager($config);
    }

    public function testRedirectsIfDefaultLanguageInUrlAndDefaultLanguageUsesNoSuffix(): void
    {
        $this->expectRedirect('/foo/baz/bar');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/en/foo/baz/bar');
    }

    public function testRedirectsIfNoLanguageInUrlAndDefaultLanguageUsesSuffix(): void
    {
        $this->expectRedirect('/en/foo/baz/bar');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'enableDefaultLanguageUrlCode' => true,
        ]);
        $this->mockRequest('/foo/baz/bar');
    }

    public function testRedirectsIfLanguageWithUpperCaseCountryInUrl(): void
    {
        $this->expectRedirect('/es-bo/foo/baz/bar');
        $this->mockUrlManager([
            'languages' => ['en-US', 'deutsch' => 'de', 'es-BO'],
        ]);
        $this->mockRequest('/es-BO/foo/baz/bar');
    }

    public function testRedirectsIfLanguageWithUpperCaseWildcardCountryInUrl(): void
    {
        $this->expectRedirect('/es-bo/foo/baz/bar');
        $this->mockUrlManager([
            'languages' => ['en-US', 'deutsch' => 'de', 'es-*'],
        ]);
        $this->mockRequest('/es-BO/foo/baz/bar');
    }

    /**
     * Accepted language tests
     */
    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageMatches(): void
    {
        $this->expectRedirect('/de/foo/baz/bar');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/foo/baz/bar', [
            'acceptableLanguages' => ['de'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageMatchesWildcard(): void
    {
        $this->expectRedirect('/de/foo/baz/bar');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de-*'],
        ]);
        $this->mockRequest('/foo/baz/bar', [
            'acceptableLanguages' => ['de'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithCountryMatches(): void
    {
        $this->expectRedirect('/de-at/foo/baz/bar');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de', 'de-AT'],
        ]);
        $this->mockRequest('/foo/baz/bar', [
            'acceptableLanguages' => ['de-AT', 'de', 'en'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithCountryMatchesWildcard(): void
    {
        $this->expectRedirect('/de-at/foo/baz/bar');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de-*'],
        ]);
        $this->mockRequest('/foo/baz/bar', [
            'acceptableLanguages' => ['de-AT', 'de', 'en'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithCountryMatchesCountryAlias(): void
    {
        $this->expectRedirect('/at/foo/baz/bar');
        $this->mockUrlManager([
            'languages' => ['de', 'at' => 'de-AT'],
        ]);
        $this->mockRequest('/foo/baz/bar', [
            'acceptableLanguages' => ['de-at', 'de'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageMatchesLanguageAndCountryAlias(): void
    {
        $this->expectRedirect('/de/foo/baz/bar');
        $this->mockUrlManager([
            'languages' => ['de', 'at' => 'de-AT'],
        ]);
        $this->mockRequest('/foo/baz/bar', [
            'acceptableLanguages' => ['en-US', 'en', 'de'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithLowercaseCountryMatches(): void
    {
        $this->expectRedirect('/de-at/foo/baz/bar');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de', 'de-AT'],
        ]);
        $this->mockRequest('/foo/baz/bar', [
            'acceptableLanguages' => ['de-at', 'de', 'en'],
        ]);
    }

    public function testRedirectsIfNoLanguageInUrlAndAcceptedLanguageWithCountryMatchesLanguage(): void
    {
        $this->expectRedirect('/de/foo/baz/bar');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/foo/baz/bar', [
            'acceptableLanguages' => ['de-at'],
        ]);
    }

    /**
     * GeoIp ltests
     */
    public function testRedirectsIfNoLanguageInUrlAndGeoIpMatches(): void
    {
        $this->expectRedirect('/de/foo/baz/bar');
        $_SERVER['HTTP_X_GEO_COUNTRY'] = 'DEU';
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'geoIpLanguageCountries' => [
                'de' => ['DEU'],
                'en-US' => ['USA'],
            ],
        ]);
        $this->mockRequest('/foo/baz/bar');
    }

    public function testRedirectsIfNoLanguageInUrlAndGeoIpMatchesWildcard(): void
    {
        $this->expectRedirect('/de/foo/baz/bar');
        $_SERVER['HTTP_X_GEO_COUNTRY'] = 'DEU';
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de-*'],
            'geoIpLanguageCountries' => [
                'de' => ['DEU'],
                'en-US' => ['USA'],
            ],
        ]);
        $this->mockRequest('/foo/baz/bar');
    }

    public function testRedirectsIfNoLanguageInUrlAndGeoIpWithCountryMatches(): void
    {
        $this->expectRedirect('/de-at/foo/baz/bar');
        $_SERVER['HTTP_X_GEO_COUNTRY'] = 'AUT';
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de', 'de-AT'],
            'geoIpLanguageCountries' => [
                'de-DE' => ['DEU'],
                'de-AT' => ['AUT'],
            ],
        ]);
        $this->mockRequest('/foo/baz/bar');
    }

    public function testRedirectsIfNoLanguageInUrlAndGeoIpWithCountryMatchesWildcard(): void
    {
        $this->expectRedirect('/de-at/foo/baz/bar');
        $_SERVER['HTTP_X_GEO_COUNTRY'] = 'AUT';
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de-*'],
            'geoIpLanguageCountries' => [
                'de-DE' => ['DEU'],
                'de-AT' => ['AUT'],
            ],
        ]);
        $this->mockRequest('/foo/baz/bar');
    }

    public function testRedirectsIfNoLanguageInUrlAndGeoIpWithCountryMatchesCountryAlias(): void
    {
        $this->expectRedirect('/at/foo/baz/bar');
        $_SERVER['HTTP_X_GEO_COUNTRY'] = 'AUT';
        $this->mockUrlManager([
            'languages' => ['de', 'at' => 'de-AT'],
            'geoIpLanguageCountries' => [
                'de' => ['DEU'],
                'de-AT' => ['AUT'],
            ],
        ]);
        $this->mockRequest('/foo/baz/bar');
    }

    /**
     * Session test
     */
    public function testRedirectsIfNoLanguageInUrlAndLanguageInSession(): void
    {
        $this->expectRedirect('/de/foo/baz/bar');
        @session_start();
        $_SESSION['_language'] = 'de';
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/foo/baz/bar');
    }

    public function testRedirectsIfNoLanguageInUrlAndLanguageInSessionMatchesWildcard(): void
    {
        $this->expectRedirect('/de/foo/baz/bar');
        @session_start();
        $_SESSION['_language'] = 'de';
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de-*'],
        ]);
        $this->mockRequest('/foo/baz/bar');
    }

    /**
     * Cookie test
     */
    public function testRedirectsIfNoLanguageInUrlAndLanguageInCookie(): void
    {
        $this->expectRedirect('/de/foo/baz/bar');
        $_COOKIE['_language'] = 'de';
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/foo/baz/bar');
    }

    public function testRedirectsIfNoLanguageInUrlAndLanguageInCookieMatchesWildcard(): void
    {
        $this->expectRedirect('/de/foo/baz/bar');
        $_COOKIE['_language'] = 'de';
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de-*'],
        ]);
        $this->mockRequest('/foo/baz/bar');
    }

    /**
     * Ignore URL test
     */
    public function testRedirectsIfUrlDoesNotMatchIgnoresUrls(): void
    {
        $this->expectRedirect('/foo/baz/bar');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'ignoreLanguageUrlPatterns' => [
                '#not/used#' => '#^site/other#',
            ],
        ]);
        $this->mockRequest('/en/foo/baz/bar');
    }

    /**
     * Trailing slash tests
     */
    public function testRedirectsIfDefaultLanguageInUrlAndDefaultLanguageUsesNoSuffixAndTrailingSlashEnabled(): void
    {
        $this->expectRedirect('/foo/baz/bar/');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'suffix' => '/',
        ]);
        $this->mockRequest('/en/foo/baz/bar/');
    }

    public function testRedirectsToRootIfOnlyDefaultLanguageInUrlAndDefaultLanguageUsesNoSuffixAndTrailingSlashEnabled(): void
    {
        $this->expectRedirect('/');
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'suffix' => '/',
        ]);
        $this->mockRequest('/en');
    }

    public function testRedirectsRootToDefaultLanguageIfDefaultLanguageUsesSuffixAndTrailingSlashEnabled(): void
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
