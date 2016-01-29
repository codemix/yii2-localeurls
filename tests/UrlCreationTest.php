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
        $this->assertEquals($this->prepareUrl('/de'), Url::to(['/site/index']));
        $this->assertEquals($this->prepareUrl('/de?x=y'), Url::to(['/site/index', 'x' => 'y']));
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
        $this->assertEquals($this->prepareUrl('/en-us'), Url::to(['/', 'language' => 'en-US']));
        $this->assertEquals($this->prepareUrl('/en-us/demo/action'), Url::to(['/demo/action', 'language' => 'en-US']));
        $this->assertEquals($this->prepareUrl('/en-us/demo/action?x=y'), Url::to(['/demo/action', 'language' => 'en-US', 'x'=>'y']));
        $this->assertEquals($this->prepareUrl('/en-us/foo/baz/bar'), Url::to(['/slug/action', 'language' => 'en-US', 'term' => 'baz']));
        $this->assertEquals($this->prepareUrl('/en-us/foo/baz/bar?x=y'), Url::to(['/slug/action', 'language' => 'en-US', 'x' => 'y', 'term' => 'baz']));
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

    public function testCreateUrlWithLanguageAndTrailingSlashFromUrl()
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

}
