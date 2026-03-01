<?php

namespace Infrastructure\Factories;

interface FactoryInterface
{
    public function create(array $data): object;
}

abstract class AbstractFactory implements FactoryInterface
{
    abstract public function create(array $data): object;
    
    protected function validate(array $data, array $required): void
    {
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }
    }
}
