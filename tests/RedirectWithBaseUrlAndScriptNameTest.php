<?php
namespace tests;

use yii\helpers\Url;

class RedirectWithBaseUrlAndScriptName extends RedirectTest
{
    protected $baseUrl = '/base';
    protected $showScriptName = true;
}
