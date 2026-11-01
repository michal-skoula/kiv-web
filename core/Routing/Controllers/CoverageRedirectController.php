<?php

namespace Core\Routing\Controllers;

use Core\Routing\Contracts\Controller;
use Core\Routing\Objects\Response;

class CoverageRedirectController extends Controller
{
    public function handle(): Response
    {
        return Response::redirect('/coverage/index.html', permanent: true);
    }
}