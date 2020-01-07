<?php
namespace tests;

use yii\helpers\Url;

class UrlCreationWithBaseUrlAndScriptNameTest extends UrlCreationTest
{
    protected $baseUrl = '/base';
    protected $showScriptName = true;
}
