<?php

declare(strict_types=1);

namespace tests;

use yii\helpers\Url;

final class SlugRedirectWithBaseUrlTest extends SlugRedirectTest
{
    protected $baseUrl = '/base';
}
