<?php

namespace App\Http\Controllers;

use Core\Routing\Contracts\Controller;
use Core\Routing\Objects\Response;

class AboutController extends Controller
{
    public function handle(): Response
    {
        return Response::ok('Hello World! This is the about page.');
    }
}