<?php

declare(strict_types=1);

namespace tests;

use yii\helpers\Url;

final class UrlCreationWithBaseUrlTest extends UrlCreationTest
{
    protected $baseUrl = '/base';
}
