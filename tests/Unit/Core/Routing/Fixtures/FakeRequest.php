<?php

namespace Tests\Unit\Core\Routing\Fixtures;

use Core\Routing\Enums\Method;
use Core\Routing\Objects\Request;

/**
 * Test fixture that replaces calls from stuff like $_GET that
 * don't exist when running tests with dummy defaults.
 */
class FakeRequest extends Request
{
    public function __construct(Method $method = Method::GET, string $uri = '/tests')
    {
        parent::__construct();

        $this->host = 'tests.cli';
        $this->uri = $uri;
        $this->method = $method;
        $this->userAgent = null;
        $this->accepts = null;
        $this->headers = [];
        $this->params = [];
        $this->cookies = [];

    }
}