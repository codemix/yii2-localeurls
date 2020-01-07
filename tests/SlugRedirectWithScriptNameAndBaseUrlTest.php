<?php
namespace tests;

use yii\helpers\Url;

class SlugRedirectWithScriptNameAndBaseUrlTest extends SlugRedirectTest
{
    protected $showScriptName = true;
    protected $baseUrl = '/base';
}
