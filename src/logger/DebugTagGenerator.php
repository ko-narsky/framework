<?php

namespace Konarsky\logger;

use Konarsky\contracts\{DebugTagGeneratorInterface, DebugTagStorageInterface};
use Psr\Http\Message\RequestInterface;
use RuntimeException;

final readonly class DebugTagGenerator implements DebugTagGeneratorInterface
{
    public function __construct(
        private DebugTagStorageInterface $debugTagStorage,
        private bool $canRecreate = true,
        private ?RequestInterface $request = null
    ) {
        $this->generate();
    }

    private function generate(): void
    {
        if ($this->canRecreate === true) {
            $this->debugTagStorage->setTag(md5(uniqid('x_debug_tag_', true)));

            return;
        }

        if ($this->request->hasHeader('X-Debug-Tag') === true) {
            $this->debugTagStorage->setTag($this->request->getHeader('X-Debug-Tag')[0]);

            return;
        }

        try {
            $this->debugTagStorage->getTag();

            throw new RuntimeException('Ошибка создания X-Debug-Tag');
        } catch (RuntimeException $e) {
            if ($e->getMessage() === 'Тег отладки не определен') {
                $this->debugTagStorage->setTag(md5(uniqid('x_debug_tag_', true)));
            }
        }
    }
}
