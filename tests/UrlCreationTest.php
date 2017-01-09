<?php

use yii\helpers\Url;

class UrlCreationTest extends TestCase
{
    public function testCreateNormalUrlIfLocaleUrlsDisabled()
    {
        $this->mockUrlManager([
            'enableLocaleUrls' => false,
            'languages' => ['en-US', 'en', 'de'],
            'rules' => [
                '/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/site/page');
        $this->assertEquals($this->prepareUrl('/demo/action?language=de'), Url::to(['/demo/action', 'language' => 'de']));
        $this->assertEquals($this->prepareUrl('/foo/baz/bar?language=de'), Url::to(['/slug/action', 'language' => 'de', 'term' => 'baz']));
    }

    public function testCreateNormalUrlIfNoLanguagesConfigured()
    {
        $this->mockUrlManager([
            'languages' => [],
            'rules' => [
                '/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/site/page');
        $this->assertEquals($this->prepareUrl('/demo/action?language=de'), Url::to(['/demo/action', 'language' => 'de']));
        $this->assertEquals($this->prepareUrl('/foo/baz/bar?language=de'), Url::to(['/slug/action', 'language' => 'de', 'term' => 'baz']));
    }

    public function testCreateUrlWithoutLanguageIfNoLanguageInUrl()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'rules' => [
                '/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/site/page');
        $this->assertEquals($this->prepareUrl('/demo/action'), Url::to(['/demo/action']));
        $this->assertEquals($this->prepareUrl('/demo/action?x=y'), Url::to(['/demo/action', 'x' => 'y']));
        $this->assertEquals($this->prepareUrl('/foo/baz/bar'), Url::to(['/slug/action', 'term' => 'baz']));
        $this->assertEquals($this->prepareUrl('/foo/baz/bar?x=y'), Url::to(['/slug/action', 'x' => 'y', 'term' => 'baz']));
    }

    public function testCreateUrlWithLanguageFromUrl()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'rules' => [
                '/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals($this->prepareUrl('/de/demo/action'), Url::to(['/demo/action']));
        $this->assertEquals($this->prepareUrl('/de/demo/action?x=y'), Url::to(['/demo/action', 'x' => 'y']));
        $this->assertEquals($this->prepareUrl('/de/foo/baz/bar'), Url::to(['/slug/action', 'term' => 'baz']));
        $this->assertEquals($this->prepareUrl('/de/foo/baz/bar?x=y'), Url::to(['/slug/action', 'x' => 'y', 'term' => 'baz']));
    }

    public function testCreateAbsoluteUrlWithLanguageFromUrl()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'rules' => [
                '/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals('http://localhost'.$this->prepareUrl('/de/demo/action'), Url::to(['/demo/action'], 'http'));
        $this->assertEquals('http://localhost'.$this->prepareUrl('/de/demo/action?x=y'), Url::to(['/demo/action', 'x' => 'y'], 'http'));
        $this->assertEquals('http://localhost'.$this->prepareUrl('/de/foo/baz/bar'), Url::to(['/slug/action', 'term' => 'baz'], 'http'));
        $this->assertEquals('http://localhost'.$this->prepareUrl('/de/foo/baz/bar?x=y'), Url::to(['/slug/action', 'x' => 'y', 'term' => 'baz'], 'http'));
    }

    public function testCreateServerNameUrlWithLanguageFromUrl()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'rules' => [
                'http://www.example.com/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals('http://www.example.com'.$this->prepareUrl('/de/foo/baz/bar'), Url::to(['/slug/action', 'term' => 'baz']));
        $this->assertEquals('http://www.example.com'.$this->prepareUrl('/de/foo/baz/bar?x=y'), Url::to(['/slug/action', 'x' => 'y', 'term' => 'baz']));
    }

    public function testCreateUrlWithLanguageFromUrlIfUppercaseEnabled()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'keepUppercaseLanguageCode' => true,
            'rules' => [
                '/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/en-US/site/page');
        $this->assertEquals($this->prepareUrl('/en-US/demo/action'), Url::to(['/demo/action']));
        $this->assertEquals($this->prepareUrl('/en-US/demo/action?x=y'), Url::to(['/demo/action', 'x' => 'y']));
        $this->assertEquals($this->prepareUrl('/en-US/foo/baz/bar'), Url::to(['/slug/action', 'term' => 'baz']));
        $this->assertEquals($this->prepareUrl('/en-US/foo/baz/bar?x=y'), Url::to(['/slug/action', 'x' => 'y', 'term' => 'baz']));
    }

    public function testCreateHomeUrlWithLanguageFromUrl()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'rules' => [
                '' => 'site/index',
                '/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals($this->prepareUrl('/'), Url::to(['/site/index', 'language' => '']));
        $this->assertEquals($this->prepareUrl('/de'), Url::to(['/site/index']));
        $this->assertEquals($this->prepareUrl('/de?x=y'), Url::to(['/site/index', 'x' => 'y']));
    }

    public function testCreateAbsoluteHomeUrlWithLanguageFromUrl()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'rules' => [
                '' => 'site/index',
                '/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals('http://localhost'.$this->prepareUrl('/'), Url::to(['/site/index', 'language' => ''], 'http'));
        $this->assertEquals('http://localhost'.$this->prepareUrl('/de'), Url::to(['/site/index'], 'http'));
        $this->assertEquals('http://localhost'.$this->prepareUrl('/de?x=y'), Url::to(['/site/index', 'x' => 'y'], 'http'));
    }

    public function testCreateServerNameHomeUrlWithLanguageFromUrl()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'rules' => [
                'http://www.example.com' => 'site/index',
            ],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals('http://www.example.com'.$this->prepareUrl('/de'), Url::to(['/site/index']));
    }

    public function testCreateUrlWithSpecificLanguage()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'rules' => [
                '/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals($this->prepareUrl('/'), Url::to(['/', 'language' => '']));
        $this->assertEquals($this->prepareUrl('/demo/action'), Url::to(['/demo/action', 'language' => '']));
        $this->assertEquals($this->prepareUrl('/en'), Url::to(['/', 'language' => 'en']));
        $this->assertEquals($this->prepareUrl('/en/demo/action'), Url::to(['/demo/action', 'language' => 'en']));
        $this->assertEquals($this->prepareUrl('/en-us'), Url::to(['/', 'language' => 'en-US']));
        $this->assertEquals($this->prepareUrl('/en-us/demo/action'), Url::to(['/demo/action', 'language' => 'en-US']));
        $this->assertEquals($this->prepareUrl('/en-us/demo/action?x=y'), Url::to(['/demo/action', 'language' => 'en-US', 'x'=>'y']));
        $this->assertEquals($this->prepareUrl('/en-us/foo/baz/bar'), Url::to(['/slug/action', 'language' => 'en-US', 'term' => 'baz']));
        $this->assertEquals($this->prepareUrl('/en-us/foo/baz/bar?x=y'), Url::to(['/slug/action', 'language' => 'en-US', 'x' => 'y', 'term' => 'baz']));
    }

    public function testCreateAbsoluteUrlWithSpecificLanguage()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'rules' => [
                '/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals('http://localhost'.$this->prepareUrl('/en-us'), Url::to(['/', 'language' => 'en-US'], 'http'));
        $this->assertEquals('http://localhost'.$this->prepareUrl('/en-us/demo/action'), Url::to(['/demo/action', 'language' => 'en-US'], 'http'));
        $this->assertEquals('http://localhost'.$this->prepareUrl('/en-us/demo/action?x=y'), Url::to(['/demo/action', 'language' => 'en-US', 'x'=>'y'], 'http'));
        $this->assertEquals('http://localhost'.$this->prepareUrl('/en-us/foo/baz/bar'), Url::to(['/slug/action', 'language' => 'en-US', 'term' => 'baz'], 'http'));
        $this->assertEquals('http://localhost'.$this->prepareUrl('/en-us/foo/baz/bar?x=y'), Url::to(['/slug/action', 'language' => 'en-US', 'x' => 'y', 'term' => 'baz'], 'http'));
    }

    public function testCreateServerNameUrlWithSpecificLanguage()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'rules' => [
                'http://www.example.com/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals('http://www.example.com'.$this->prepareUrl('/en-us/foo/baz/bar'), Url::to(['/slug/action', 'language' => 'en-US', 'term' => 'baz']));
        $this->assertEquals('http://www.example.com'.$this->prepareUrl('/en-us/foo/baz/bar?x=y'), Url::to(['/slug/action', 'language' => 'en-US', 'x' => 'y', 'term' => 'baz']));
    }

    public function testCreateUrlWithSpecificAliasedLanguage()
    {
        $this->mockUrlManager([
            'languages' => ['fr', 'en', 'deutsch' => 'de'],
            'rules' => [
                '/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/fr/site/page');
        $this->assertEquals($this->prepareUrl('/deutsch/demo/action'), Url::to(['/demo/action', 'language' => 'de']));
        $this->assertEquals($this->prepareUrl('/deutsch/demo/action?x=y'), Url::to(['/demo/action', 'language' => 'de','x'=>'y']));
        $this->assertEquals($this->prepareUrl('/deutsch/foo/baz/bar'), Url::to(['/slug/action', 'language' => 'de', 'term' => 'baz']));
        $this->assertEquals($this->prepareUrl('/deutsch/foo/baz/bar?x=y'), Url::to(['/slug/action', 'language' => 'de', 'x' => 'y', 'term' => 'baz']));
    }

    public function testCreateNormalUrlIfIgnoreRoutesMatches()
    {
        $this->mockUrlManager([
            'languages' => ['en-us', 'en', 'de'],
            'ignoreLanguageUrlPatterns' => [
                '#^demo/.*#' => '#not/used#',
                '#^slug/.*#' => '#not/used#',
            ],
            'rules' => [
                '/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals($this->prepareUrl('/demo/action'), Url::to(['/demo/action']));
        $this->assertEquals($this->prepareUrl('/demo/action?x=y'), Url::to(['/demo/action', 'x'=>'y']));
        $this->assertEquals($this->prepareUrl('/foo/baz/bar'), Url::to(['/slug/action', 'term' => 'baz']));
        $this->assertEquals($this->prepareUrl('/foo/baz/bar?x=y'), Url::to(['/slug/action', 'x' => 'y', 'term' => 'baz']));
    }

    public function testCreateUrlWithLanguageFromUrlIfIgnoreRouteDoesNotMatch()
    {
        $this->mockUrlManager([
            'languages' => ['en-us', 'en', 'de'],
            'ignoreLanguageUrlPatterns' => [
                '#^other/.*#' => '#not/used#'
            ],
            'rules' => [
                '/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals($this->prepareUrl('/de/demo/action?x=y'), Url::to(['/demo/action', 'x'=>'y']));
        $this->assertEquals($this->prepareUrl('/de/foo/baz/bar?x=y'), Url::to(['/slug/action', 'x' => 'y', 'term' => 'baz']));
    }

    public function testCreateUrlWithTralingSlashWithLanguageFromUrl()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'suffix' => '/',
            'rules' => [
                '/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/de/site/page/');
        $this->assertEquals($this->prepareUrl('/de/demo/action/'), Url::to(['/demo/action']));
        $this->assertEquals($this->prepareUrl('/de/demo/action/?x=y'), Url::to(['/demo/action', 'x'=>'y']));
        $this->assertEquals($this->prepareUrl('/de/foo/baz/bar/'), Url::to(['/slug/action', 'term' => 'baz']));
        $this->assertEquals($this->prepareUrl('/de/foo/baz/bar/?x=y'), Url::to(['/slug/action', 'x' => 'y', 'term' => 'baz']));
    }

    public function testCreateAbsoluteUrlWithTralingSlashWithLanguageFromUrl()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'suffix' => '/',
            'rules' => [
                '/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/de/site/page/');
        $this->assertEquals('http://localhost'.$this->prepareUrl('/de/demo/action/'), Url::to(['/demo/action'], 'http'));
        $this->assertEquals('http://localhost'.$this->prepareUrl('/de/demo/action/?x=y'), Url::to(['/demo/action', 'x'=>'y'], 'http'));
        $this->assertEquals('http://localhost'.$this->prepareUrl('/de/foo/baz/bar/'), Url::to(['/slug/action', 'term' => 'baz'], 'http'));
        $this->assertEquals('http://localhost'.$this->prepareUrl('/de/foo/baz/bar/?x=y'), Url::to(['/slug/action', 'x' => 'y', 'term' => 'baz'], 'http'));
    }

    public function testCreateServerNameUrlWithTralingSlashWithLanguageFromUrl()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'suffix' => '/',
            'rules' => [
                'http://www.example.com/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/de/site/page/');
        $this->assertEquals('http://www.example.com'.$this->prepareUrl('/de/foo/baz/bar/'), Url::to(['/slug/action', 'term' => 'baz']));
        $this->assertEquals('http://www.example.com'.$this->prepareUrl('/de/foo/baz/bar/?x=y'), Url::to(['/slug/action', 'x' => 'y', 'term' => 'baz']));
    }

    public function testCreateHomeUrlWithTrailingSlashWithLanguageFromUrl()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'rules' => [
                '' => 'site/index',
                '/foo/<term:.+>/bar' => 'slug/action',
            ],
            'suffix' => '/',
        ]);
        $this->mockRequest('/de/site/page/');
        $this->assertEquals($this->prepareUrl('/de/'), Url::to(['/site/index']));
        $this->assertEquals($this->prepareUrl('/de/?x=y'), Url::to(['/site/index', 'x'=>'y']));
    }

    public function testCreateUrlWithSpecificLanguageWithTrailingSlash()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'suffix' => '/',
            'rules' => [
                '/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/de/site/page/');
        $this->assertEquals($this->prepareUrl('/en-us/demo/action/'), Url::to(['/demo/action', 'language' => 'en-US']));
        $this->assertEquals($this->prepareUrl('/en-us/demo/action/?x=y'), Url::to(['/demo/action', 'language' => 'en-US', 'x'=>'y']));
        $this->assertEquals($this->prepareUrl('/en-us/foo/baz/bar/'), Url::to(['/slug/action', 'language' => 'en-US', 'term' => 'baz']));
        $this->assertEquals($this->prepareUrl('/en-us/foo/baz/bar/?x=y'), Url::to(['/slug/action', 'language' => 'en-US', 'x' => 'y', 'term' => 'baz']));
    }

    public function testCreateUrlWithUppercaseLanguageIfEnabled()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'keepUppercaseLanguageCode' => true,
            'rules' => [
                '/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals($this->prepareUrl('/en-US'), Url::to(['/', 'language' => 'en-US']));
        $this->assertEquals($this->prepareUrl('/en-US/demo/action'), Url::to(['/demo/action', 'language' => 'en-US']));
        $this->assertEquals($this->prepareUrl('/en-US/demo/action?x=y'), Url::to(['/demo/action', 'language' => 'en-US', 'x'=>'y']));
        $this->assertEquals($this->prepareUrl('/en-US/foo/baz/bar'), Url::to(['/slug/action', 'language' => 'en-US', 'term' => 'baz']));
        $this->assertEquals($this->prepareUrl('/en-US/foo/baz/bar?x=y'), Url::to(['/slug/action', 'language' => 'en-US', 'x' => 'y', 'term' => 'baz']));
    }

    public function testCreateResetUrlWithLanguageIfPersistenceAndDetectionEnabled()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'rules' => [
                '/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals($this->prepareUrl('/en/demo/action'), Url::to(['/demo/action', 'language' => 'en']));
        $this->assertEquals($this->prepareUrl('/en/demo/action?x=y'), Url::to(['/demo/action', 'x' => 'y', 'language' => 'en']));
        $this->assertEquals($this->prepareUrl('/en/foo/baz/bar'), Url::to(['/slug/action', 'term' => 'baz', 'language' => 'en']));
        $this->assertEquals($this->prepareUrl('/en/foo/baz/bar?x=y'), Url::to(['/slug/action', 'x' => 'y', 'term' => 'baz', 'language' => 'en']));
    }

    public function testCreateResetUrlWithLanguageIfPersistenceDisabled()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'enableLanguagePersistence' => false,
            'rules' => [
                '/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals($this->prepareUrl('/en/demo/action'), Url::to(['/demo/action', 'language' => 'en']));
        $this->assertEquals($this->prepareUrl('/en/demo/action?x=y'), Url::to(['/demo/action', 'x' => 'y', 'language' => 'en']));
        $this->assertEquals($this->prepareUrl('/en/foo/baz/bar'), Url::to(['/slug/action', 'term' => 'baz', 'language' => 'en']));
        $this->assertEquals($this->prepareUrl('/en/foo/baz/bar?x=y'), Url::to(['/slug/action', 'x' => 'y', 'term' => 'baz', 'language' => 'en']));
    }

    public function testCreateResetUrlWithLanguageIfDetectionDisabled()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'enableLanguageDetection' => false,
            'rules' => [
                '/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals($this->prepareUrl('/en/demo/action'), Url::to(['/demo/action', 'language' => 'en']));
        $this->assertEquals($this->prepareUrl('/en/demo/action?x=y'), Url::to(['/demo/action', 'x' => 'y', 'language' => 'en']));
        $this->assertEquals($this->prepareUrl('/en/foo/baz/bar'), Url::to(['/slug/action', 'term' => 'baz', 'language' => 'en']));
        $this->assertEquals($this->prepareUrl('/en/foo/baz/bar?x=y'), Url::to(['/slug/action', 'x' => 'y', 'term' => 'baz', 'language' => 'en']));
    }

    public function testCreateUrlWithoutDefaultLanguageIfPersistenceAndDetectionDisabled()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'enableLanguagePersistence' => false,
            'enableLanguageDetection' => false,
            'rules' => [
                '' => 'site/index',
                '/foo/<term:.+>/bar' => 'slug/action',
            ],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals($this->prepareUrl('/'), Url::to(['/site/index', 'language' => 'en']));
        $this->assertEquals($this->prepareUrl('/demo/action'), Url::to(['/demo/action', 'language' => 'en']));
        $this->assertEquals($this->prepareUrl('/demo/action?x=y'), Url::to(['/demo/action', 'x' => 'y', 'language' => 'en']));
        $this->assertEquals($this->prepareUrl('/foo/baz/bar'), Url::to(['/slug/action', 'term' => 'baz', 'language' => 'en']));
        $this->assertEquals($this->prepareUrl('/foo/baz/bar?x=y'), Url::to(['/slug/action', 'x' => 'y', 'term' => 'baz', 'language' => 'en']));
    }
}
