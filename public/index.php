<?php
chdir('..');
include('vendor/autoload.php');

/*
use Errbit\Errbit;

if(Config::$errbitHost) {
  Errbit::instance()
  ->configure(array(
    'api_key' => Config::$errbitKey,
    'host' => Config::$errbitHost,
    'port' => 443,
    'secure' => true
  ))
  ->start();
}
*/

initdb();

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
$router = new League\Route\RouteCollection;
$templates = new League\Plates\Engine(dirname(__FILE__).'/../views');

$router->addRoute('GET', '/', 'Controller::index');

$router->addRoute('GET', '/login', 'Auth::login');
$router->addRoute('GET', '/logout', 'Auth::logout');
$router->addRoute('POST', '/login/start', 'Auth::login_start');
$router->addRoute('GET', '/login/callback', 'Auth::login_callback');

$router->addRoute('GET', '/dashboard', 'Controller::dashboard');
$router->addRoute('GET', '/site/new', 'Controller::new_site');
$router->addRoute('GET', '/site/edit', 'Controller::new_site');
$router->addRoute('POST', '/site/save', 'Controller::save_site');

$dispatcher = $router->getDispatcher();
$request = Request::createFromGlobals();

try {
  $response = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());
  $response->send();
} catch(League\Route\Http\Exception\NotFoundException $e) {
  $response = new Response;
  $response->setStatusCode(404);
  $response->setContent("Not Found\n");
  $response->send();
} catch(League\Route\Http\Exception\MethodNotAllowedException $e) {
  $response = new Response;
  $response->setStatusCode(405);
  $response->setContent("Method not allowed\n");
  $response->send();
}
