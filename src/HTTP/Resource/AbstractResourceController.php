<?php

namespace Konarsky\HTTP\Resource;

use Konarsky\Contract\FormRequestFactoryInterface;
use Konarsky\Contract\ResourceDataFilterInterface;
use Konarsky\Contract\ResourceWriterInterface;
use Konarsky\Exception\HTTP\BadRequestHttpException;
use Konarsky\Exception\HTTP\ForbiddenHttpException;
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
    ) {
        $this->resourceDataFilter
            ->setResourceName($this->getResourceName())
            ->setAccessibleFields($this->getAccessibleFilters())
            ->setAccessibleFilters($this->getAccessibleFields());

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
        // возвращать имя ресурса из названия унаследованного контроллера
        // имя унаследованного контроллера получать средствами позднего статического связывания
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
        // реализовать проверку доступности вызова метода на основании разрешенных действий с ресурсом метода getAvailableActions()
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

        return new JsonResponse($this->resourceDataFilter->filterAll($this->request->getQueryParams()));
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
    public function actionView(): JsonResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::VIEW);

        return new JsonResponse($this->resourceDataFilter->filterOne($this->request->getQueryParams()));
    }

    public function actionCreate(): CreateResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::CREATE);

        $form = $this->formRequestFactory->create($this->forms[ResourceActionTypesEnum::CREATE->value]);

        $form->validate();

        if (empty($form->getErrors()) === false) {
            throw new BadRequestHttpException($form->getErrors());
        }

        $this->resourceWriter->create($form->getValues());

        return new CreateResponse();
    }

    public function actionUpdate(string|int $id): UpdateResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::UPDATE);

        $form = $this->formRequestFactory->create($this->forms[ResourceActionTypesEnum::UPDATE->value]);

        $form->validate();

        if (empty($form->getErrors()) === false) {
            throw new BadRequestHttpException($form->getErrors());
        }

        $this->resourceWriter->update($id, $form->getValues());

        return new UpdateResponse();
    }

    public function actionPatch(string|int $id): PatchResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::PATCH);

        $form = $this->formRequestFactory->create($this->forms[ResourceActionTypesEnum::PATCH->value]);

        $form->setSkipEmptyValues();

        $form->validate();

        if (empty($form->getErrors()) === false) {
            throw new BadRequestHttpException($form->getErrors());
        }

        $this->resourceWriter->patch($id, $form->getValues());

        return new PatchResponse();
    }

    public function actionDelete(string|int $id): DeleteResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::DELETE);

        $this->resourceWriter->delete($id);
        return new DeleteResponse();
    }
}
