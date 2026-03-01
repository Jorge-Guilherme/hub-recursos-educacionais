<?php

namespace Application\UseCases\Grupo;

use Application\UseCases\UseCaseInterface;
use Domain\Contracts\GrupoRepositoryInterface;

class CreateGrupo implements UseCaseInterface
{
    private GrupoRepositoryInterface $repository;

    public function __construct(GrupoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(mixed $input = null): array
    {
        return $this->repository->create($input);
    }
}
