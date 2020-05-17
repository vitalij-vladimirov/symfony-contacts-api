<?php
declare(strict_types=1);

namespace App\EventListener;

use App\Exception\ApiException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event)
    {
        $e = $event->getThrowable();
        if (!$e instanceof ApiException) {
            return;
        }

        /** @var ApiException $apiException */
        $apiException = $e;

        $exception = [
            'error_code' => $apiException->getShortCode(),
            'message' => $apiException->getMessage(),
        ];

        if (count($apiException->getData()) !== 0) {
            $exception['data'] = $apiException->getData();
        }

        $event->setResponse(
            new JsonResponse(
                $exception,
                $apiException->getHttpCode()
            )
        );
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException'
        ];
    }
}
