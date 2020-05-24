<?php
use DI\ContainerBuilder;
use GovReader\GovNewsFeed;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;

$configFile = __DIR__ . '/settings.php';
if (!file_exists($configFile)) {
    throw new \RuntimeException('Copy settings.php.sample to settings.php');
}
$config = require $configFile;
$builder = new ContainerBuilder();
$builder->addDefinitions(__DIR__ . '/di-config.php');
$container = $builder->build();
AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addErrorMiddleware($config['display_errors'], false, false);

/**
 * Attaches headers to every request to all endpoints
 */
$app->add(function (Request $request, RequestHandler $handler): Response {
    $response = $handler->handle($request);

    if ($response->getStatusCode() !== 200) { // error pages don't need headers
        return $response;
    }

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withHeader('Access-Control-Allow-Origin', 'http://jkhntr.com/govreader')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

/**
 * Main endpoint - JSON feed
 */
$app->get('/feed', function (Request $request, Response $response) use ($container): Response {
    $response->getBody()->write(
        json_encode(
            $container->get(GovNewsFeed::class)->get($request->getQueryParams()['department'] ?? null)
        )
    );
    return $response;
});

$app->run();
