<?php

use yii\helpers\Url;

class UrlManagerTest extends TestCase
{
    public function testCreateUrlWithoutLanguageIfNoLanguageInUrl()
    {
        $this->mockRequest('/site/page');
        $this->mockLocaleUrl([
            'languages' => ['en-us', 'en', 'de'],
        ]);
        $this->assertEquals($this->prepareUrl('/demo/action'), Url::to(['/demo/action']));
    }

    public function testCreateUrlWithLanguageFromUrl()
    {
        $this->mockRequest('/de/site/page');
        $this->mockLocaleUrl([
            'languages' => ['en-us', 'en', 'de'],
        ]);
        $this->assertEquals($this->prepareUrl('/de/demo/action'), Url::to(['/demo/action']));
    }

    public function testCreateUrlWithSpecificLanguage()
    {
        $this->mockRequest('/de/site/page');
        $this->mockLocaleUrl([
            'languages' => ['en-us', 'en', 'de'],
        ]);
        $this->assertEquals($this->prepareUrl('/en-us/demo/action'), Url::to(['/demo/action', 'language' => 'en-us']));
    }

    public function testCreateUrlWithSpecificAliasedLanguage()
    {
        $this->mockRequest('/fr/site/page');
        $this->mockLocaleUrl([
            'languages' => ['fr', 'en', 'deutsch' => 'de'],
        ]);
        $this->assertEquals($this->prepareUrl('/deutsch/demo/action'), Url::to(['/demo/action', 'language' => 'de']));
    }
}
