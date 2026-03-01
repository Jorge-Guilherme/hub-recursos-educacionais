<?php

namespace Application\UseCases;

interface UseCaseInterface
{
    public function execute(mixed $input): mixed;
}
