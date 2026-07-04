<?php

declare(strict_types=1);

namespace App\Core;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionNamedType;

class Container
{
    private array $bindings = [];
    private array $singletons = [];
    private array $instances = [];

    public function bind(
        string $abstract,
        string|Closure|null $concrete = null
    ): void {
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'shared' => false,
        ];
    }

    public function singleton(
        string $abstract,
        string|Closure|null $concrete = null
    ): void {
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'shared' => true,
        ];
    }

    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function has(string $abstract): bool
    {
        return isset($this->instances[$abstract])
            || isset($this->bindings[$abstract])
            || class_exists($abstract);
    }

    public function get(string $abstract): object
    {
        return $this->resolve($abstract);
    }

    public function resolve(string $abstract): object
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $binding = $this->bindings[$abstract] ?? null;

        $concrete = $binding['concrete'] ?? $abstract;
        $shared = (bool) ($binding['shared'] ?? false);

        if ($concrete instanceof Closure) {
            $object = $concrete($this);
        } else {
            $object = $this->build($concrete);
        }

        if ($shared) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    private function build(string $class): object
    {
        $reflection = new ReflectionClass($class);

        if (!$reflection->isInstantiable()) {
            throw new Exception("Classe {$class} não pode ser instanciada.");
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return new $class();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                    continue;
                }

                throw new Exception(
                    "Não foi possível resolver o parâmetro {$parameter->getName()} em {$class}."
                );
            }

            $dependencies[] = $this->resolve($type->getName());
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}