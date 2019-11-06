<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 2019-01-10
 * Time: 14:53
 */

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

/**
 * Handles raw JsonData controller return values
 */
class JsonDataResponseHandler
{
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        if ($event->getControllerResult() instanceof JsonData) {
            $event->setResponse(
                JsonResponse::create($event->getControllerResult())
            );
        }
    }
}