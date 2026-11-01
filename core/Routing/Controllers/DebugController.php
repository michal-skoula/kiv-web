<?php

namespace Core\Routing\Controllers;

use Core\Routing\Contracts\Controller;
use Core\Routing\Objects\Response;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Controller used for debugging the application. Dumps values with pretty formatting.
 */
class DebugController extends Controller
{
    public function handle(): Response
    {
        // Runs through all of them, whichever it finds first it will show.
        foreach($this->request->accepts as $accept) {
            if($accept === 'application/json') {
                header('Content-Type: application/json');
                http_response_code(200);
                echo json_encode($this->request->toArray());
                return new Response();
            }
            elseif($accept === 'text/html') {
                $this->showHtml();
                return new Response();
            }
        }

        // Default
        $this->showHtml();
        return new Response();
        }

    protected function showHtml(): void
    {
        $title = 'Debug: ' . $_ENV['APP_NAME'] ?? 'my_app';
        $time = new DateTimeImmutable(timezone: new DateTimeZone('Europe/Prague'))->format('d.m.Y H:i:s');
        $requestData = $this->jsonEncode($this->request->toArray());
        $debugData = $this->jsonEncode([
            "Server" => $_SERVER,
            "Get" => $_GET,
            "Post" => $_POST,
            "Cookie" => $_COOKIE
        ]);

        http_response_code(200);
        header('Content-Type: text/html');
        printf(self::MARKUP, $title, $time, $requestData, $debugData);
    }

    protected function jsonEncode(array $data): string
    {
        return json_encode($data, flags: JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    protected const string MARKUP = <<<'HTML'
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
                    font-weight: 600;
                    font-size: 45px;
                    margin-bottom: 20px;
                }
                
                h2 { 
                    font-size: 30px; 
                    margin-bottom: 20px
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
            <span>🕑 Timestamp: %s</span>
            <h1>🪲 DEBUG INFO 🪲</h1>
            <h2>Request Object</h2>
            
            <pre class="debug">%s</pre>
            
            <h2>Superglobals</h2>
            <pre class="debug">%s</pre>
        </body>
        </html>
        HTML;
}