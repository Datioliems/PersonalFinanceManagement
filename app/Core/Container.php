<?php

namespace App\Core;

class Container
{
    private array $bindings = [];

    public function bind(string $abstract, string $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function make(string $abstract)
    {
        $concrete = $this->bindings[$abstract] ?? $abstract;

        $ref = new \ReflectionClass($concrete);
        if (!$ref->isInstantiable()) {
            throw new \RuntimeException("Class {$concrete} is not instantiable");
        }

        $ctor = $ref->getConstructor();
        if ($ctor === null) {
            return $ref->newInstance();
        }

        $args = [];
        foreach ($ctor->getParameters() as $param) {
            $type = $param->getType();
            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $args[] = $this->make($type->getName());
                continue;
            }

            if ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
                continue;
            }

            throw new \RuntimeException(sprintf(
                'Cannot resolve constructor parameter "%s" for %s',
                $param->getName(),
                $concrete
            ));
        }

        return $ref->newInstanceArgs($args);
    }
}