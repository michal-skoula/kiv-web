<?php

namespace Core\Routing\Contracts;

use Core\Routing\Objects\Request;

interface Controller
{
    public function handle(Request $request): mixed;
}