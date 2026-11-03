<?php

namespace Core\Routing\Controllers\Debug;

use Core\Facades\App;
use Core\Routing\Contracts\Controller;
use Core\Routing\Objects\Response;

class CoverageRedirectController extends Controller
{
    public function handle(): Response
    {
        if(App::inProduction()) {
            return Response::notFound('404 Not Found');
        }

        return Response::redirect('/coverage/index.html', permanent: true);
    }
}