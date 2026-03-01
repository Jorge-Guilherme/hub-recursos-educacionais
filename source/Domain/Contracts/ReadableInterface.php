<?php

namespace Domain\Contracts;

interface ReadableInterface
{
    public function find(int $id);
    public function all();
}
