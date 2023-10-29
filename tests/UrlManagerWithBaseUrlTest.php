<?php

declare(strict_types=1);

namespace tests;

use yii\helpers\Url;

final class UrlManagerWithBaseUrlTest extends UrlManagerTest
{
    protected $baseUrl = '/base';
}
