<?php

namespace App\Http\Controllers;

use Core\Routing\Contracts\Controller;
use Core\Routing\Objects\Response;

class AboutController extends Controller
{
    public function handle(): Response
    {
        echo 'Hello World! This is the about page.';
        return new Response();
    }
}