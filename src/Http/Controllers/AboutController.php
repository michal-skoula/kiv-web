<?php

namespace App\Http\Controllers;

use Core\Routing\Contracts\Controller;
use Core\Routing\Objects\Request;

class AboutController implements Controller
{
    public function handle(Request $request): mixed
    {
        return 'Hello World! This is the about page.';
    }
}