<?php

namespace Core\Routing\Controllers;

use Core\Routing\Contracts\Controller;
use Core\Routing\Objects\Response;
use Core\Routing\Objects\View;

class NotFoundController extends Controller
{

    public function handle(): Response
    {
        return Response::notFound('404 Not Found');
    }
}