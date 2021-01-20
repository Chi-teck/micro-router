# Micro Router
A simple and fast routing system for PSR-7 requests.

## Requirements
* PHP 8.0+
* [psr/http-message provider](https://packagist.org/providers/psr/http-message-implementation)
* [psr/simple-cache provider](https://packagist.org/providers/psr/simple-cache-implementation)

## Installation
```sh
composer require chi-teck/micro-router
```

## Usage

### Define routes

```php
$routes = new RouteCollection();

$routes['article.view'] = new Route(
    methods: ['GET'],
    path: '/article/{id}',
    requirements: ['id' => '\d+'],
    handler: ArticleViewHandler::class,
);

$routes['article.update'] = new Route(
    methods: ['PUT'],
    path: '/article/{id}',
    requirements: ['id' => '\d+'],
    handler: ArticleUpdateHandler::class,
);

$routes['article.delete'] = new Route(
    methods: ['DELETE'],
    path: '/article/{id}',
    requirements: ['id' => '\d+'],
    handler: ArticleDeleteHandler::class,
);

$routes['article.create'] = new Route(
    methods: ['POST'],
    path: '/article',
    handler: ArticleCreateHandler::class,
);
```

Alternatively, the routes can be defined via `create` factory method.
```php
$routes = new RouteCollection();
$routes['article.view'] = Route::create('GET', '/article/{id:\d+}', ArticleViewHandler::class);
$routes['article.update'] = Route::create('PUT', '/article/{id:\d+}', ArticleUpdateHandler::class);
$routes['article.delete'] = Route::create('DELETE', '/article/{id:\d+}', ArticleDeleteHandler::class);
$routes['article.create'] = Route::create('POST', '/article', ArticleCreateHandler::class);
```

## Handle request
```php
use MicroRouter\Compiler;
use MicroRouter\Contract\Exception\MethodNotAllowedInterface;
use MicroRouter\Contract\Exception\ResourceNotFoundInterface;
use MicroRouter\Matcher;

/** @var \MicroRouter\Contract\RouteCollectionInterface $routes */
$routes = require __DIR__ . '/path/to/routes.php';

/** @var Psr\SimpleCache\CacheInterface $cache */
$matcher = Matcher::create($cache);

/** @var \Psr\Http\Message\ServerRequestFactoryInterface $request_factory */
// In real application the request is created from PHP super globals.
$request = $request_factory->createServerRequest('GET', '/article/123');
try {
    $result = $matcher->match($request, $routes);
    $response = \call_user_func_array(
        $result->getRoute()->getHandler(),
        $result->getParameters(),
    );
}
catch (ResourceNotFoundInterface) {
    /** @var \Psr\Http\Message\ResponseFactoryInterface $response_factory */
    $response = $response_factory->createResponse(404);
}
catch (MethodNotAllowedInterface $exception) {
    /** @var \Psr\Http\Message\ResponseFactoryInterface $response_factory */
    $response = $response_factory->createResponse(405)
        ->withHeader('Allowed', $exception->getAllowedMethods());
}
```

## License
MIT License.
