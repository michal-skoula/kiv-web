<?php

namespace Core\Routing\Controllers;

use Core\Routing\Contracts\Controller;
use Core\Routing\Objects\Request;

class DebugFallbackController implements Controller
{
    public function handle(Request $request): mixed
    {
        $title = 'Debug: ' . $_ENV['APP_NAME'] ?? 'my_app';
        $debugData = json_encode(
            value: [
                "Server" => $_SERVER,
                "Request" => $_REQUEST,
                "Get" => $_GET,
                "Post" => $_POST,
                "Cookie" => $_COOKIE
            ],
            flags: JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
        );

        printf(self::MARKUP, $title, $debugData);

        return 'hi';
    }

    private const string MARKUP = <<<'HTML'
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">

            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&display=swap" rel="stylesheet">
            
            <title>%s</title>
            
            <style>
                html {
                    background: #1e1e1e;
                    color: #cbcbcb
                }
                
                h1 {
                    font-size: 60px;
                    margin-bottom: 48px;
                }
                
                .font {
                     font-family: "JetBrains Mono", monospace;
                    font-optical-sizing: auto;
                    font-weight: <weight>;
                    font-style: normal;
                }
                
                .debug {
                    border: 1px solid #aaa;
                    border-radius: 8px;
                    padding: 32px;
                    width: 94vw;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
            </style>
        </head>
        <body class="font">
            <h1> FALLBACK ROUTE DEBUG</h1>
            <pre class="debug">%s</pre>
        </body>
        </html>
        HTML;
}