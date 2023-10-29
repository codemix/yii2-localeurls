<?php

declare(strict_types=1);

namespace tests;

final class UrlManagerWithScriptNameAndBaseUrlTest extends UrlManagerTest
{
    protected $showScriptName = true;
    protected $baseUrl = '/base';
}
