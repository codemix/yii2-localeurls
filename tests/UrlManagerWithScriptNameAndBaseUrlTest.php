<?php
namespace tests;

use yii\helpers\Url;

class UrlManagerWithScriptNameAndBaseUrlTest extends UrlManagerTest
{
    protected $showScriptName = true;
    protected $baseUrl = '/base';
}
