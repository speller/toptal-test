<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 2019-01-09
 * Time: 13:41
 */

ini_set('display_errors', 'stderr');

use App\Kernel;
use PhpOffice\PhpSpreadsheet\Settings;
use Spiral\Goridge\StreamRelay;
use Spiral\RoadRunner\PSR7Client;
use Spiral\RoadRunner\Worker;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/load-env.php';

if ($debug) {
    umask(0000);

    Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts(explode(',', $trustedHosts));
}

$kernel = new Kernel($env, $debug);
//$relay = new SocketRelay("127.0.0.1", 9005);
$relay = new StreamRelay(STDIN, STDOUT);
$psr7 = new PSR7Client(new Worker($relay));
$httpFoundationFactory = new HttpFoundationFactory();
$diactorosFactory = new DiactorosFactory();

while ($req = $psr7->acceptRequest()) {
    try {
        chdir($kernel->getProjectDir());
        $request = $httpFoundationFactory->createRequest($req);
        $response = $kernel->handle($request);
        // Dirty hack for excel library to avoid memory leaks between requests
        Settings::getCache()->clear();
        $psr7->respond($diactorosFactory->createResponse($response));
        $kernel->terminate($request, $response);
        $kernel->reboot(null);
    } catch (\Throwable $e) {
        $psr7->getWorker()->error((string)$e);
    }
}