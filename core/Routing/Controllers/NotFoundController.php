<?php

namespace Core\Routing\Controllers;

use Core\Routing\Objects\Response;

class NotFoundController extends Controller
{

    public function handle(): Response
    {
        return Response::notFound('404 Not Found');
    }
}