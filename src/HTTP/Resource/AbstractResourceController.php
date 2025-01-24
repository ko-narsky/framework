<?php

namespace Konarsky\HTTP\Resource;

use Konarsky\Contract\EventDispatcherInterface;
use Konarsky\Contract\FormRequestFactoryInterface;
use Konarsky\Contract\ResourceDataFilterInterface;
use Konarsky\Contract\ResourceWriterInterface;
use Konarsky\EventDispatcher\Message;
use Konarsky\Exception\Base\NotFoundException;
use Konarsky\Exception\HTTP\BadRequestHttpException;
use Konarsky\Exception\HTTP\ForbiddenHttpException;
use Konarsky\Exception\HTTP\NotFoundHttpException;
use Konarsky\HTTP\Enum\FormActionsEnum;
use Konarsky\HTTP\Enum\ResourceActionTypesEnum;
use Konarsky\HTTP\Form\FormRequest;
use Konarsky\HTTP\Response\CreateResponse;
use Konarsky\HTTP\Response\DeleteResponse;
use Konarsky\HTTP\Response\JsonResponse;
use Konarsky\HTTP\Response\PatchResponse;
use Konarsky\HTTP\Response\UpdateResponse;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractResourceController
{
    public function __construct(
        protected ResourceDataFilterInterface $resourceDataFilter,
        protected ServerRequestInterface $request,
        protected FormRequestFactoryInterface $formRequestFactory,
        protected ResourceWriterInterface $resourceWriter,
        protected EventDispatcherInterface $eventDispatcher,
    ) {
        $this->resourceDataFilter
            ->setResourceName($this->getResourceName())
            ->setAccessibleFields($this->getAccessibleFields())
            ->setAccessibleFilters($this->getAccessibleFilters());

        $this->resourceWriter
            ->setResourceName($this->getResourceName());
    }

    private array $forms = [
        ResourceActionTypesEnum::CREATE->value => FormRequest::class,
        ResourceActionTypesEnum::UPDATE->value => FormRequest::class,
        ResourceActionTypesEnum::PATCH->value => FormRequest::class,
    ];

    protected function getAvailableActions(): array
    {
        return [
            ResourceActionTypesEnum::INDEX,
            ResourceActionTypesEnum::VIEW,
            ResourceActionTypesEnum::CREATE,
            ResourceActionTypesEnum::UPDATE,
            ResourceActionTypesEnum::PATCH,
            ResourceActionTypesEnum::DELETE,
        ];
    }

    protected function getResourceName(): string
    {
        $className = explode('\\', static::class);

        $resourceName = preg_replace('/Controller$/', '', end($className));

        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $resourceName));
    }

    /**
     * Возврат имен свойств ресурса, доступных к чтению
     * Пример запроса:
     * ?fields=id,order_id,name
     * @return array
     */
    abstract protected function getAccessibleFields(): array;

    /**
     * Возврат имен свойств ресурса, доступных к фильтрации
     * Пример запроса:
     * ?filter[order_id][$eq]=3
     * @return array
     */
    abstract protected function getAccessibleFilters(): array;

    /**
     * @throws ForbiddenHttpException
     */
    private function checkCallAvailability(ResourceActionTypesEnum $actionType): void
    {
        if (in_array($actionType, $this->getAvailableActions()) === false) {
            throw new ForbiddenHttpException();
        }
    }

    /**
     * Возврат ресурсов, по ограничениям указанным в строке запроса
     *
     * Пример запроса:
     * ?fields[]=id&fields[]=order_id&fields[]=name&filter[order_id][$eq]=3
     * Пример ответа:
     * application/json
     * [
     *     {
     *         "id": 1,
     *         "order_id":3,
     *         "name": "Некоторое имя 1"
     *     },
     *     {
     *         "id": 2,
     *         "order_id":3,
     *         "name": "Некоторое имя 2"
     *     },
     *     ...
     * ]
     * @return JsonResponse
     */
    public function actionList(): JsonResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::INDEX);

        try {
            return new JsonResponse($this->resourceDataFilter->filterAll($this->request->getQueryParams()));
        } catch (NotFoundException) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * Возврат ресурса, по ограничениям указанным в строке запроса
     *
     * Пример запроса:
     * ?fields[]=id&fields[]=name&filter[id][$eq]=1
     * Пример ответа:
     * application/json
     * {
     *     "id": 1,
     *     "name": "Некоторое имя 1"
     * },
     * @return JsonResponse
     */
    public function actionView(string|int $id): JsonResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::VIEW);

        try {
            return new JsonResponse($this->resourceDataFilter->filterOne($id, $this->request->getQueryParams()));
        } catch (NotFoundException) {
            throw new NotFoundHttpException();
        }
    }

    public function actionCreate(): CreateResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::CREATE);

        $form = $this->formRequestFactory->create($this->forms[ResourceActionTypesEnum::CREATE->value]);

        $this->eventDispatcher->trigger(FormActionsEnum::AFTER_FORM_CREATED->value, new Message($form));

        $form->validate();

        if (empty($form->getErrors()) === false) {
            throw new BadRequestHttpException(json_encode($form->getErrors(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }

        $this->resourceWriter->create($form->getValues());

        return new CreateResponse();
    }

    public function actionUpdate(string|int $id): UpdateResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::UPDATE);

        $form = $this->formRequestFactory->create($this->forms[ResourceActionTypesEnum::UPDATE->value]);

        $this->eventDispatcher->trigger(FormActionsEnum::AFTER_FORM_CREATED->value, new Message($form));

        $form->validate();

        if (empty($form->getErrors()) === false) {
            throw new BadRequestHttpException(json_encode($form->getErrors(), JSON_UNESCAPED_UNICODE));
        }

        try {
            $this->resourceWriter->update($id, $form->getValues());
        } catch (NotFoundException) {
            throw new NotFoundHttpException();
        }

        return new UpdateResponse();
    }

    public function actionPatch(string|int $id): PatchResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::PATCH);

        $form = $this->formRequestFactory->create($this->forms[ResourceActionTypesEnum::PATCH->value]);

        $form->setSkipEmptyValues();

        $this->eventDispatcher->trigger(FormActionsEnum::AFTER_FORM_CREATED->value, new Message($form));

        $form->validate();

        if (empty($form->getErrors()) === false) {
            throw new BadRequestHttpException(json_encode($form->getErrors(), JSON_UNESCAPED_UNICODE));
        }

        try {
            $this->resourceWriter->patch($id, $form->getValues());
        } catch (NotFoundException) {
            throw new NotFoundHttpException();
        }

        return new PatchResponse();
    }

    public function actionDelete(string|int $id): DeleteResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::DELETE);

        try {
            $this->resourceWriter->delete($id);
        } catch (NotFoundException) {
            throw new NotFoundHttpException();
        }

        return new DeleteResponse();
    }
}
