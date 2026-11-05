<?php

namespace Core\Routing\Controllers\Debug;

use Core\Facades\App;
use Core\Routing\Controllers\Controller;
use Core\Routing\Objects\Response;
use Core\Routing\Objects\Route;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Controller used for debugging the application. Dumps values with formatting.
 */
class DebugController extends Controller
{
    public function handle(): Response
    {
        $options = ['application/json', 'text/html'];
        $preferred = $this->request->preferredContentType($options);

        return match ($preferred) {
            'application/json' => Response::ok($this->getJson())
                ->addHeader('Content-Type', 'application/json'),
            default => Response::ok($this->getHtml())
                ->addHeader('Content-Type', 'text/html'),
        };
    }

    protected function getHtml(): string
    {
        $title = 'Debug: ' . $_ENV['APP_NAME'] ?? 'my_app';
        $time = $this->getTime();
        $requestData = $this->prettyJson($this->request->toArray());
        $routeData = $this->prettyJson($this->getRouteInfo());
        $parametersData = $this->prettyJson($this->parameters);
        $debugData = $this->prettyJson([
            "Server" => $_SERVER,
            "Get" => $_GET,
            "Post" => $_POST,
            "Cookie" => $_COOKIE
        ]);

        return sprintf(
            self::MARKUP, $title,
            $time,
            $requestData,
            $routeData,
            $parametersData,
            $debugData
        );
    }

    protected function getTime(): string
    {
        try {
            return new DateTimeImmutable(timezone: new DateTimeZone('Europe/Prague'))->format('d.m.Y H:i:s');
        } catch(DateMalformedStringException) {
            App::log()->error('Misconfigured DateTimeZone on DebugController');
            return 'unknown';
        }
    }

    /**
     * @return array{Controller: class-string<static>, Route: ?Route}
     */
    protected function getRouteInfo(): array
    {
        return [
            'Route' => $this->route,
            'Controller' => static::class,
        ];
    }

    protected function getJson(): string
    {
        return json_encode([
            'Request' => $this->request->toArray(),
            'Route' => $this->getRouteInfo(),
            'Parameters' => $this->parameters,
        ]);
    }

    protected function prettyJson(array $data): string
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
                    padding: 16px;
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
            
            <h2>Resolved Route</h2>
            <pre class="debug">%s</pre>
            
            <h2>Route Parameters</h2>
            <pre class="debug">%s</pre>
            
            <h2>Superglobals</h2>
            <pre class="debug">%s</pre>
        </body>
        </html>
        HTML;
}