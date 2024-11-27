<?php

namespace Konarsky\Middleware;

use Konarsky\Contracts\EventDispatcherInterface;
use Konarsky\Contracts\LoggerInterface;
use Konarsky\Contracts\MiddlewareInterface;
use Konarsky\EventDispatcher\Message;
use Konarsky\Logger\LogContextEvent;
use Psr\Http\Message\RequestInterface;

readonly class CalculationRequestLogMiddleware implements MiddlewareInterface
{
    public function __construct(private LoggerInterface $logger, private EventDispatcherInterface $eventDispatcher) { }

    public function __invoke(RequestInterface $request): void
    {
        $this->eventDispatcher->trigger(
            LogContextEvent::ATTACH_EXTRAS,
            new Message('Переданные параметры: ' . $request->getUri()->getQuery())
        );

        $this->logger->info(
            "Выполнен запрос на расчет стоимости",
            'Отработал ' . CalculationRequestLogMiddleware::class
        );
    }
}
