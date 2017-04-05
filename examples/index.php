<?php
date_default_timezone_set('UTC');
define('__ROOT__', dirname(__DIR__));
require __ROOT__ . '/vendor/autoload.php';

use Polus\Adr\App;
use Polus\Adr\Test\Domain;
use Polus\Adr\Test\Responder;
use Polus\Adr\Test\TestInput;
use Zend\Diactoros\ServerRequestFactory;

$appNs = 'Polus\Test';
$app = new App($appNs);

$app->get('/test/{id}', Domain\Test::class);
$app->get('/input-test/{id}', Domain\InputTest::class);
$app->get('/test-custom-input/{id}', Domain\Test::class, TestInput::class);
$app->get('/responder_test', null, null, Responder\ResponderTest::class);

$urls = [
    '/test/world',
    '/test/extra-info',
    '/input-test/info-name-id',
    '/input-test/test-99',
    '/test-custom-input/99-test',
    '/test-custom-input/test-99',
    '/responder_test',
    '/hello/error',
];

foreach ($urls as $url) {
    echo "------ PATH: " . $url . " ------ \n\n";
    $_SERVER['REQUEST_URI'] = $url;
    $app->setRequest(ServerRequestFactory::fromGlobals());
    $app->run();
    echo "\n--------------\n\n";
}
