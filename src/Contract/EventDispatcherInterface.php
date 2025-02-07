<?php

namespace Konarsky\Contract;

use Konarsky\EventDispatcher\Message;

interface EventDispatcherInterface
{
    /**
     * Конфигурирует EventDispatcher с использованием предоставленного массива конфигурации
     *
     * @param array $config Массив конфигурации, где каждый элемент представляет собой массив [Event $event, ObserverInterface $observer]
     * @return void
     */
    public function configure(array $config): void;

    /**
     * Подписывает наблюдателя к определенному событию
     *
     * @param string $eventName Имя события, к которому присоединяется наблюдатель
     * @param ObserverInterface $observer Наблюдатель, который будет присоединен
     * @return void
     */
    public function attach(string $eventName, callable|array|ObserverInterface $observer): void;

    /**
     * Отписывает наблюдателя от определенного события
     *
     * @param string $eventName имя события, от которого отсоединяется наблюдатель
     * @return void
     */
    public function detach(string $eventName): void;

    /**
     * Запускает событие и уведомляет соответствующего наблюдателя с переданным сообщением
     *
     * @param string $eventName Имя события, которое будет запущено
     * @param Message $message Сообщение, передаваемое наблюдателю
     * @return void
     */
    public function trigger(string $eventName, Message $message): void;
}
