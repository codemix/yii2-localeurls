<?php

declare(strict_types=1);

namespace tests;

final class UrlCreationWithBaseUrlAndScriptNameTest extends UrlCreationTest
{
    protected $baseUrl = '/base';
    protected $showScriptName = true;
}
