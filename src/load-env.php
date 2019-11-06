<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 2019-02-12
 * Time: 10:17
 */

use Symfony\Component\Dotenv\Dotenv;

// The check is to ensure we don't use .env in production
if (!isset($_SERVER['APP_ENV']) && !isset($_ENV['APP_ENV'])) {
    if (!class_exists(Dotenv::class)) {
        throw new \RuntimeException(
            'APP_ENV environment variable is not defined. You need to define environment variables for configuration or add "symfony/dotenv" as a Composer dependency to load variables from a .env file.'
        );
    }
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        (new Dotenv())->load($envFile);
    }
}

$env = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev_local';
$debug = (bool) ($_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? ($env !== 'prod'));
