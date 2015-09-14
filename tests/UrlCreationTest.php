<?php

use yii\helpers\Url;

class UrlCreationTest extends TestCase
{
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
        $this->assertEquals($this->prepareUrl('/demo/action?x=y'), Url::to(['/demo/action', 'x' => 'y']));
    }

    public function testCreateUrlWithLanguageFromUrl()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals($this->prepareUrl('/de/demo/action'), Url::to(['/demo/action']));
        $this->assertEquals($this->prepareUrl('/de/demo/action?x=y'), Url::to(['/demo/action', 'x' => 'y']));
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
        $this->assertEquals($this->prepareUrl('/de?x=y'), Url::to(['/site/index', 'x' => 'y']));
    }

    public function testCreateUrlWithSpecificLanguage()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
        ]);
        $this->mockRequest('/de/site/page');
        $this->assertEquals($this->prepareUrl('/en-us'), Url::to(['/', 'language' => 'en-US']));
        $this->assertEquals($this->prepareUrl('/en-us/demo/action'), Url::to(['/demo/action', 'language' => 'en-US']));
        $this->assertEquals($this->prepareUrl('/en-us/demo/action?x=y'), Url::to(['/demo/action', 'language' => 'en-US', 'x'=>'y']));
    }

    public function testCreateUrlWithSpecificAliasedLanguage()
    {
        $this->mockUrlManager([
            'languages' => ['fr', 'en', 'deutsch' => 'de'],
        ]);
        $this->mockRequest('/fr/site/page');
        $this->assertEquals($this->prepareUrl('/deutsch/demo/action'), Url::to(['/demo/action', 'language' => 'de']));
        $this->assertEquals($this->prepareUrl('/deutsch/demo/action?x=y'), Url::to(['/demo/action', 'language' => 'de','x'=>'y']));
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
        $this->assertEquals($this->prepareUrl('/demo/action?x=y'), Url::to(['/demo/action', 'x'=>'y']));
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
        $this->assertEquals($this->prepareUrl('/de/demo/action?x=y'), Url::to(['/demo/action', 'x'=>'y']));
    }

    public function testCreateUrlWithLanguageAndTrailingSlashFromUrl()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'suffix' => '/',
        ]);
        $this->mockRequest('/de/site/page/');
        $this->assertEquals($this->prepareUrl('/de/demo/action/'), Url::to(['/demo/action']));
        $this->assertEquals($this->prepareUrl('/de/demo/action/?x=y'), Url::to(['/demo/action', 'x'=>'y']));
    }

    public function testCreateHomeUrlWithTrailingSlashWithLanguageFromUrl()
    {
        $this->mockUrlManager([
            'languages' => ['en-US', 'en', 'de'],
            'rules' => [
                '' => 'site/index',
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
        ]);
        $this->mockRequest('/de/site/page/');
        $this->assertEquals($this->prepareUrl('/en-us/demo/action/'), Url::to(['/demo/action', 'language' => 'en-US']));
        $this->assertEquals($this->prepareUrl('/en-us/demo/action/?x=y'), Url::to(['/demo/action', 'language' => 'en-US', 'x'=>'y']));
    }
}
