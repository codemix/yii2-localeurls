<?php

declare(strict_types=1);

namespace tests;

use yii\helpers\Url;

final class RedirectWithBaseUrlTest extends RedirectTest
{
    protected $baseUrl = '/base';
}
