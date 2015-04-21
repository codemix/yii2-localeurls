<?php

use yii\helpers\Url;

class UrlManagerWithScriptNameTest extends TestCase
{
    protected $showScriptName = true;

    public function testCreateUrlWithoutLanguageIfNoLanguageInUrl()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en_us', 'en', 'de'],
            ],
            'request' => [
                'url' => '/index.php/site/page',
            ]
        ]);
        $this->assertEquals('/index.php/demo/action', Url::to(['/demo/action']));
    }

    public function testCreateUrlWithLanguageFromUrl()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en_us', 'en', 'de'],
            ],
            'request' => [
                'url' => '/index.php/de/site/page',
            ]
        ]);
        $this->assertEquals('/index.php/de/demo/action', Url::to(['/demo/action']));
    }

    public function testCreateUrlWithSpecificLanguage()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['en_us', 'en', 'de'],
            ],
            'request' => [
                'url' => '/index.php/de/site/page',
            ]
        ]);
        $this->assertEquals('/index.php/en_us/demo/action', Url::to(['/demo/action', 'language' => 'en_us']));
    }

    public function testCreateUrlWithSpecificAliasedLanguage()
    {
        $this->mockComponents([
            'localeUrls' => [
                'languages' => ['fr', 'en', 'deutsch' => 'de'],
            ],
            'request' => [
                'url' => '/index.php/fr/site/page',
            ]
        ]);
        $this->assertEquals('/index.php/deutsch/demo/action', Url::to(['/demo/action', 'language' => 'de']));
    }
}
