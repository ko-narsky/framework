<?php

declare(strict_types=1);

namespace Konarsky\HTTP\Factory;

use Konarsky\Contract\FormRequestFactoryInterface;
use Konarsky\Contract\FormRequestInterface;
use LogicException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class FormRequestFactory implements FormRequestFactoryInterface
{
    public function __construct(
        private ContainerInterface $container,
        private ServerRequestInterface $request,
    ) { }

    /**
     * @inheritDoc
     */
    public function create(string $formClassName): FormRequestInterface
    {
        $attributes = $this->request->getParsedBody()['attributes'] ?? null;

        if ($attributes === null) {
            throw new LogicException('Отсутствуют данные attributes в запросе');
        }

        if (class_exists($formClassName)) {
            return $this->container->get($formClassName, ['values' => $attributes]);
        }

        throw new LogicException('Класс формы ' . $formClassName . ' не существует');
    }
}
