<?php

namespace Core\Routing\Contracts;

use Core\Routing\Objects\Request;
use Core\Routing\Objects\Response;
use Core\Routing\Objects\View;

abstract class Controller
{
    public function __construct(protected Request $request)
    {
        //
    }

    abstract public function handle(): View|Response|string;
}