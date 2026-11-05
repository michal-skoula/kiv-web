<?php

namespace Core\Routing\Controllers\Debug;

use Core\Facades\App;
use Core\Routing\Controllers\Controller;
use Core\Routing\Objects\Response;

class CoverageRedirectController extends Controller
{
    public function handle(): Response
    {
        if(App::inProduction()) {
            return Response::notFound('404 Not Found');
        }

        return Response::redirect('/storage/coverage/index.html', permanent: true);
    }
}