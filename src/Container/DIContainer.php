<?php

namespace Konarsky\Container;

use Exception;
use LogicException;
use Psr\Container\ContainerInterface;

class DIContainer implements ContainerInterface
{
    private static ?self $instance = null;
    private array $singletons = [];

    private array $definitions = [];

    /**
     * @throws \ReflectionException
     */
    protected function __construct(array $config = [])
    {
        $config['singletons'][ContainerInterface::class] = $this;
        $this->singletons = $config['singletons'];
        $this->definitions = $config['definitions'];
    }

    /**
     * @throws \ReflectionException
     */
    public static function getInstance(array $config = []): self
    {
        if (self::$instance === null) {
            self::create($config);
        }

        return self::$instance;
    }

    /**
     * Запрещает клонирование объекта, являющегося синглтоном
     *
     * @throws LogicException
     */
    private function __clone(): void
    {
        throw new LogicException('Клонирование запрещено');
    }

    /**
     * Именованный конструктор
     * Создает экземпляр класса DIContainer
     *
     * @param array $config Массив конфигурации
     * @return DIContainer экземпляр класса DIContainer
     * @throws \ReflectionException
     */
    public static function create(array $config = []): self
    {
        // проверка попытки повторного конструирования объекта контейнера,
        // при подтверждении - выброс ошибки с информацией о заперете
        // повторного конструирования объекта
        if (self::$instance !== null) {
            throw new Exception("Запрещено повторное конструирование объекта контейнера");
        }

        self::$instance = new self($config);

        // возврат инстанса контейнера
        return self::$instance;
    }

    /**
     * Создание экземпляра объекта в зависимости от имени класса
     *
     * @param string $dependencyName имя зависимости, для которой нужно создать объект
     * @param array $args предподготовленные параметры конструктора
     * @return object возвращает экземпляр объекта в зависимости от имени класса
     * @throws \ReflectionException
     */
    public function build(string|callable $dependencyName, array $args = []): mixed
    {
        // при условии, что зависимостью является коллбек функцией,
        // выполнить возврат коллбек-функци с передачей контейнера внедрения зависимостей как аргумента функции
        if (is_callable($dependencyName) === true) {
            return $dependencyName($this);
        }

        try {
            $reflection = new \ReflectionClass($dependencyName);
        } catch (\ReflectionException $e) {
            echo '<pre>';
            throw new \ReflectionException("Ошибка рефлексии: {$e->getMessage()}");
        }

        // перебор параметров конструктора класса объекта
        if ($constructor = $reflection->getConstructor()) {
            $parameters = $constructor->getParameters();
            $dependencies = [];

            // форирование массива параметров конструктора в виде инстанциированных объектов-зависимостей
            foreach ($parameters as $parameter) {
                $parameterTypeName = $parameter->getType()->getName();
                $name = $parameter->getName();

                // значения имен параметров, переданых как предустановленные значения в массиве $args имеют приоритет перед конструируемыми значениями параметров
                if (isset($this->definitions[$parameterTypeName]) === true) {
                    $dependencies[] = $this->get($parameterTypeName);

                    continue;
                }

                if (isset($this->singletons[$parameterTypeName]) === true) {
                    $dependencies[] = $this->get($parameterTypeName);

                    continue;
                }

                if (array_key_exists($name, $args) === true) {
                    $dependencies[] = $args[$name];
                }
            }

            return $reflection->newInstanceArgs($dependencies);
        }

        return new $dependencyName();
    }

    /**
     * Создает экземпляр класса, реализующего указанный интерфейс, и сохраняет его в качестве синглтона.
     *
     * @param string $id имя контракта
     * @return object экземпляр класса с внедренными зависимостями
     * @throws Exception
     */
    public function get(string $id, array $args = []): object
    {
        if (isset($this->definitions[$id]) === true) {
            return $this->resolveDefinition($id, $args);
        }

        if (isset($this->singletons[$id]) === true) {
            return $this->resolveSingleton($id, $args);
        }

        if (class_exists($id) === false) {
            throw new \Exception("Контракт $id не найден");
        }

        return $this->build($id, $args);
    }

    /**
     * @param string $id
     * @param array $args
     * @return object
     * @throws \ReflectionException
     * @throws Exception
     */
    private function resolveDefinition(string $id, array $args): object
    {
        $definition = $this->definitions[$id];

        if (is_array($definition)=== true) {
            $args = array_merge(end($definition), $args);
            $definition = array_shift($definition);
        }

        if (is_string($definition)=== true || is_callable($definition)=== true) {
            return $this->definitions[$id] = $this->build($definition, $args);
        }

        $reflection = new \ReflectionClass($definition);
        return $this->get($reflection->getName());
    }

    /**
     * @param string $id
     * @param array $args
     * @return object
     * @throws \ReflectionException
     */
    private function resolveSingleton(string $id, array $args): object
    {
        $singleton = $this->singletons[$id];

        if (is_array($singleton) === true) {
            $args = array_merge(end($singleton), $args);
            $singleton = array_shift($singleton);
        }

        if (is_string($singleton)=== true || is_callable($singleton)=== true) {
            return $this->singletons[$id] = $this->build($singleton, $args);
        }

        return $singleton;
    }


    /**
     * Выполняет вызов указанного обработчика (callable или объекта)
     * с внедрением зависимостей в качестве параметров метода или аргументов функци
     *
     * @param object|string $handler обработчик
     * @param string $method имя метода
     * @param array $args предподготовленные параметры конструктора
     * @return mixed Результат выполнения обработчика
     * @throws \ReflectionException
     */
    public function call(object|string $handler, string $method, array $args = []): mixed
    {
        if (is_string($handler) === true) {
            $handler = $this->get($handler);
        }

        $reflector = new \ReflectionClass($handler);
        $method = $reflector->getMethod($method);

        $parameters = $method->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $parameterName = $parameter->getName();
            $dependency = $parameter->getType();

            if (isset($args[$parameterName]) === true) {
                $dependencies[] = $args[$parameterName];

                continue;
            }

            if ($dependency !== null) {
                $dependencies[] = $this->get($dependency->getName());

                continue;
            }

            throw new \InvalidArgumentException("Не передан параметр $dependency");
        }

        return $method->invokeArgs($handler, $dependencies);
    }

    /**
     * Проверяет наличие контракта в конфигурации.
     *
     * @param string $id имя контракта
     * @return bool возвращает true, если контракт существует в конфигурации
     */
    public function has(string $id): bool
    {
        // провека регистрации зависимости как синглтона или класса конструируемого по контракту
        return isset($this->bindings[$id]);
    }
}