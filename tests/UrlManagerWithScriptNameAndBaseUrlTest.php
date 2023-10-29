<?php

declare(strict_types=1);

namespace tests;

use yii\helpers\Url;

final class UrlManagerWithScriptNameAndBaseUrlTest extends UrlManagerTest
{
    protected $showScriptName = true;
    protected $baseUrl = '/base';
}
