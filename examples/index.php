#!/opt/homebrew/bin/php
<?php

use Oladesoftware\Httpcrafter\Router\Router;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$router = Router::getInstance();

$router
    ->get('/', function () { return 'Hello World!'; }, 'home')
    ->group(
        '/blog',
        [
            fn() => $router->get('/', function () { return 'Hello Blog!'; }, 'blog.index'),
            fn() => $router->get('/article/(?<slug>[a-z-]+)', function (string $slug) { return 'Hello ' . $slug . '!'; }, 'blog.article'),
            fn() => $router->get('/author/{author:alpha}', function (string $author) { return 'Hello ' . $author . '!'; }, 'blog.author'),
        ]
    )
    ->form('/contact', function () { return 'Hello Contact!'; })
;

$result = $router
        ->handle(
        'GET',
        '/'
        );

if (!$result) {
    echo 'Not found' . PHP_EOL;
    exit();
}

echo $result . PHP_EOL;
exit();