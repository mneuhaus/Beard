<?php
require_once(__DIR__ . '/../../vendor/autoload.php');

define('WORKING_DIRECTORY', $_ENV['PWD']);
define('BASE_DIRECTORY', __DIR__);
define('PHAR_MODE', boolval(Phar::running()));
define('BOX_PATH', dirname(dirname(__DIR__)));

\Mia3\Koseki\ClassRegister::setCacheFile(__DIR__ . '/../ClassRegisterCache.php');

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', '\Famelo\Beard\Scaffold\Core\WebController:index');
    $r->addRoute('GET', '/package/new/{package}', '\Famelo\Beard\Scaffold\Core\WebController:newPackage');
    $r->addRoute('POST', '/package/new/{package}', '\Famelo\Beard\Scaffold\Core\WebController:createPackage');

    $r->addRoute('GET', '/package/{package}/{path}', '\Famelo\Beard\Scaffold\Core\WebController:editPackage');
    $r->addRoute('POST', '/package/{package}/{path}', '\Famelo\Beard\Scaffold\Core\WebController:savePackage');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        break;

    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $arguments = $routeInfo[2];
        $parts = explode(':', $handler);
        $controller = new $parts[0]();
        $action = $parts[1];
        $controller->$action($arguments);
        break;
}
