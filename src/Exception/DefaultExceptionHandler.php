<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 2019-01-10
 * Time: 14:34
 */

namespace App\Exception;

use App\Response\JsonData;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handles unhandled exceptions and transforms them to JSON format
 */
class DefaultExceptionHandler
{
    /**
     * @var bool
     */
    private $isDebug;

    /**
     * DefaultExceptionHandler constructor.
     * @param bool $isDebug
     */
    public function __construct(bool $isDebug)
    {
        $this->isDebug = $isDebug;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $e = $event->getException();
        $statusCode = $e instanceof HttpException ? $e->getStatusCode() : 404;
        if (
            $e instanceof MethodNotAllowedHttpException ||
            $e instanceof NotFoundHttpException
        ) {
            $msg = 'Invalid request URL or HTTP method';
            $statusCode = 404;
        } elseif ($e instanceof RequestFailException) {
            $msg = $e->getMessage();
        } else {
            $msg = 'Unexpected error. Contact an administrator or try again later';
            error_log("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}\n{$e->getTraceAsString()}");
        }

        if ($this->isDebug) {
            $msg .= '. Debug info: ' . $e->getMessage();
        }

        $response = JsonResponse::create(JsonData::error($msg, -2));
        $response->setStatusCode($statusCode);
        $event->setResponse($response);
    }
}