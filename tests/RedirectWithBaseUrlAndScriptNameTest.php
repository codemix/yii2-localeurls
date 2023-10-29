<?php

declare(strict_types=1);

namespace tests;

use yii\helpers\Url;

final class RedirectWithBaseUrlAndScriptNameTest extends RedirectTest
{
    protected $baseUrl = '/base';
    protected $showScriptName = true;
}
