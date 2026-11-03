<?php

namespace Core\Routing\Controllers\Debug;

use Core\Routing\Contracts\Controller;
use Core\Routing\Objects\Response;
use Core\Routing\Objects\View;

/**
 * Controller that does nothing.
 */
class VoidController extends Controller
{
    public function handle(): View|Response
    {
        return Response::ok();
    }
}