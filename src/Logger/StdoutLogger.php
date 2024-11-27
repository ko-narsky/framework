<?php

namespace Konarsky\Logger;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Konarsky\Contracts\DebugTagStorageInterface;
use Konarsky\Contracts\EventDispatcherInterface;
use Konarsky\Logger\Enums\LogLevelNumber;
use Konarsky\Logger\Observers\ObserveAttachContext;
use Konarsky\Logger\Observers\ObserveAttachExtras;
use Konarsky\Logger\Observers\ObserveDetachContext;
use Konarsky\Logger\Observers\ObserveFlushContext;
use Konarsky\Logger\Observers\ObserveFlushExtras;
use Psr\Container\ContainerInterface;

class StdoutLogger extends AbstractLogger
{
    private LogStorageDto $storage;
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly string $index,
        private readonly string $actionType,
    ) {
        $this->initListeners();
    }

    /**
     * @inheritDoc
     */
    protected function formatMessage(string $level, mixed $logMessageData): string
    {
        $storage = clone $this->storage;

        $storage->index = $this->index;

        $storage->context = empty($storage->context) === false ? implode(':', $storage->context) : null;

        $storage->level = LogLevelNumber::fromName(strtoupper($level))->value;

        $storage->level_name = $level;

        $this->storage->action_type = $this->actionType;

        $utcDate = new DateTime('now', new DateTimeZone('UTC'));
        $storage->datetime = $utcDate->format('Y-m-d\TH:i:s.uP');
        $storage->timestamp = (new DateTimeImmutable())->format('Y-m-d\TH:i:s.uP');

        $storage->x_debug_tag = $this->container->get(DebugTagStorageInterface::class)->getTag();

        list($storage->message, $storage->category) = $logMessageData;
        if (
            $storage->message instanceof Exception
            ||
            (class_exists(\Error::class) && $storage->message instanceof \Error)
        ) {
            $storage->exception = [
                'file' => $storage->message->getFile(),
                'line' => $storage->message->getLine(),
                'code' => $storage->message->getCode(),
                'trace' => explode(PHP_EOL, $storage->message->getTraceAsString()),
            ];

            $storage->message = $storage->message->getMessage();
        }

        return json_encode((array) $storage, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @inheritDoc
     */
    protected function writeLog(string $log): void
    {
        fwrite(fopen('php://stdout', 'w'), $log . PHP_EOL);
    }

    private function initListeners(): void
    {
        $this->storage = new LogStorageDto();
        $this->dispatcher->attach(LogContextEvent::ATTACH_CONTEXT, new ObserveAttachContext($this->storage));
        $this->dispatcher->attach(LogContextEvent::DETACH_CONTEXT, new ObserveDetachContext($this->storage));
        $this->dispatcher->attach(LogContextEvent::FLUSH_CONTEXT, new ObserveFlushContext($this->storage));
        $this->dispatcher->attach(LogContextEvent::ATTACH_EXTRAS, new ObserveAttachExtras($this->storage));
        $this->dispatcher->attach(LogContextEvent::FLUSH_EXTRAS, new ObserveFlushExtras($this->storage));
    }
}
