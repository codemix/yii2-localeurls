<?php

use yii\helpers\Url;

class UrlManagerWithoutScriptNameTest extends TestCase
{
    protected $showScriptName = false;

    public function testCreateUrlWithoutLanguageIfNoLanguageInUrl()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en_us', 'en', 'de'],
            ],
            'request' => [
                'url' => '/site/page',
            ]
        ]);
        $this->assertEquals('/demo/action', Url::to(['/demo/action']));
    }

    public function testCreateUrlWithLanguageFromUrl()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en_us', 'en', 'de'],
            ],
            'request' => [
                'url' => '/de/site/page',
            ]
        ]);
        $this->assertEquals('/de/demo/action', Url::to(['/demo/action']));
    }

    public function testCreateUrlWithSpecificLanguage()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en_us', 'en', 'de'],
            ],
            'request' => [
                'url' => '/de/site/page',
            ]
        ]);
        $this->assertEquals('/en_us/demo/action', Url::to(['/demo/action', 'language' => 'en_us']));
    }
}
