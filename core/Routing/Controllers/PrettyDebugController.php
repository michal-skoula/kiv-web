<?php

namespace Core\Routing\Controllers;

use Core\Facades\App;
use Core\Routing\Objects\Response;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeZone;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

/**
 * Debug controller that renders the HTML view with Symfony VarDumper instead of plain JSON.
 * Requires symfony/var-dumper (dev dependency).
 */
class PrettyDebugController extends DebugController
{
    public function handle(): Response
    {
        // symfony/var-dumper is a dev-only dependency.
        if(App::isProduction()) {
            return Response::serverError('Cannot use pretty debug controller in production. Use the regular controller instead.');
        }

        return parent::handle();
    }

    protected function getHtml(): string
    {
        $title = 'Debug: ' . ($_ENV['APP_NAME'] ?? 'my_app');
        $time = $this->getTime();
        // One dumper for all dumps, so its JS/CSS header is only emitted once
        $cloner = new VarCloner();
        $dumper = new HtmlDumper();
        $dumper->setTheme('dark');
        $dumper->setDisplayOptions(['maxDepth' => 3]);

        $dump = fn (mixed $value): string => $dumper->dump($cloner->cloneVar($value), true);

        return sprintf(
            self::MARKUP,
            $title,
            $time,
            $dump($this->request),
            $dump(App::instance()->router->routes),
            $dump([
                "Server" => $_SERVER,
                "Get" => $_GET,
                "Post" => $_POST,
                "Cookie" => $_COOKIE
            ])
        );
    }
}
