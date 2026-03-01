<?php

namespace Application\UseCases\Grupo;

use Application\UseCases\UseCaseInterface;
use Domain\Contracts\GrupoRepositoryInterface;

class DeleteGrupo implements UseCaseInterface
{
    private GrupoRepositoryInterface $repository;

    public function __construct(GrupoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(mixed $input = null): bool
    {
        return $this->repository->delete($input['id']);
    }
}
