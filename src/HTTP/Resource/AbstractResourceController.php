<?php

namespace Konarsky\HTTP\Resource;

use Konarsky\Contract\DataBaseConnectionInterface;
use Konarsky\Contract\EventDispatcherInterface;
use Konarsky\Contract\FormRequestFactoryInterface;
use Konarsky\Contract\ResourceDataFilterInterface;
use Konarsky\Contract\ResourceWriterInterface;
use Konarsky\EventDispatcher\Message;
use Konarsky\Exception\HTTP\BadRequestHttpException;
use Konarsky\Exception\HTTP\ForbiddenHttpException;
use Konarsky\Exception\Resource\BadRequestResourceException;
use Konarsky\Exception\Resource\ForbiddenResourceException;
use Konarsky\Exception\Resource\NotFoundResourceException;
use Konarsky\HTTP\Enum\FormActionsEnum;
use Konarsky\HTTP\Enum\ResourceActionTypesEnum;
use Konarsky\HTTP\Form\FormRequest;
use Konarsky\HTTP\Response\CreateResponse;
use Konarsky\HTTP\Response\DeleteResponse;
use Konarsky\HTTP\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

abstract class AbstractResourceController
{
    public function __construct(
        protected ResourceDataFilterInterface $resourceDataFilter,
        protected ServerRequestInterface $request,
        protected FormRequestFactoryInterface $formRequestFactory,
        protected ResourceWriterInterface $resourceWriter,
        protected EventDispatcherInterface $eventDispatcher,
        protected DataBaseConnectionInterface $connection
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

    protected function getRelationships(): array
    {
        return [];
    }

    /**
     * @throws ForbiddenHttpException
     */
    private function checkCallAvailability(ResourceActionTypesEnum $actionType): void
    {
        if (in_array($actionType, $this->getAvailableActions()) === false) {
            throw new ForbiddenResourceException();
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

        $data = $this->resourceDataFilter->filterAll($this->request->getQueryParams());

        if (empty($data) === true) {
            throw new NotFoundResourceException();
        }

        return new JsonResponse($data);
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

        $data = $this->resourceDataFilter->filterOne($id, $this->request->getQueryParams());

        if ($data === null) {
            throw new NotFoundResourceException();
        }

        return new JsonResponse($data);
    }

    public function actionCreate(): CreateResponse
    {
        try {
            $this->checkCallAvailability(ResourceActionTypesEnum::CREATE);

            $form = $this->formRequestFactory->create($this->forms[ResourceActionTypesEnum::CREATE->value]);

            $this->eventDispatcher->trigger(FormActionsEnum::AFTER_FORM_CREATED->value, new Message($form));

            $form->validate();

            if (empty($form->getErrors()) === false) {
                throw new BadRequestHttpException(json_encode($form->getErrors(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            }

            $insertId = $this->resourceWriter->create($form->getValues());

            if (isset($this->request->getParsedBody()['relationships']) === false) {
                return new CreateResponse();
            }

            $relationsRequest = $this->request->getParsedBody()['relationships'];
            $filteredRelations = array_intersect_key($this->getRelationships(), array_flip(array_keys($relationsRequest)));

            foreach ($filteredRelations as $name => $relation) {
                $this->connection->insert(
                    $relation['table'],
                    [
                        $relation['resourceKey'] => $insertId,
                        current($relation['relationshipKey']) => $relationsRequest[$name]['data'][0][key($relation['relationshipKey'])],
                    ]
                );
            }

            return new CreateResponse();
        } catch (Throwable) {
            throw new BadRequestResourceException();
        }
    }

    public function actionUpdate(string|int $id): CreateResponse
    {
        try {
            $this->checkCallAvailability(ResourceActionTypesEnum::UPDATE);

            $form = $this->formRequestFactory->create($this->forms[ResourceActionTypesEnum::UPDATE->value]);

            $this->eventDispatcher->trigger(FormActionsEnum::AFTER_FORM_CREATED->value, new Message($form));

            $form->validate();

            if (empty($form->getErrors()) === false) {
                throw new BadRequestHttpException(json_encode($form->getErrors(), JSON_UNESCAPED_UNICODE));
            }

            if ($this->resourceDataFilter->filterOne($id, []) === null) {
                throw new NotFoundResourceException();
            }

            $this->resourceWriter->update($id, $form->getValues());

            return new CreateResponse();
        } catch (NotFoundResourceException $e) {
            throw $e;
        } catch (Throwable) {
            throw new BadRequestResourceException();
        }
    }

    public function actionPatch(string|int $id): CreateResponse
    {
        try {
            $this->checkCallAvailability(ResourceActionTypesEnum::PATCH);

            $form = $this->formRequestFactory->create($this->forms[ResourceActionTypesEnum::PATCH->value]);

            $form->setSkipEmptyValues();

            $this->eventDispatcher->trigger(FormActionsEnum::AFTER_FORM_CREATED->value, new Message($form));

            $form->validate();

        if (empty($form->getErrors()) === false) {
            throw new BadRequestHttpException(json_encode($form->getErrors(), JSON_UNESCAPED_UNICODE));
        }

            if ($this->resourceDataFilter->filterOne($id, []) === null) {
                throw new NotFoundResourceException();
            }

            $this->resourceWriter->patch($id, $form->getValues());

            return new CreateResponse();
        } catch (NotFoundResourceException $e) {
            throw $e;
        } catch (Throwable) {
            throw new BadRequestResourceException();
        }
    }

    public function actionDelete(string|int $id = null): DeleteResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::DELETE);

        if ($this->resourceDataFilter->filterOne($id, []) === null) {
            throw new NotFoundResourceException();
        }

        $this->resourceWriter->delete($id);

        return new DeleteResponse();
    }
}
