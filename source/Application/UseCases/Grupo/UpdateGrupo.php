<?php

namespace Application\UseCases\Grupo;

use Application\UseCases\UseCaseInterface;
use Domain\Contracts\GrupoRepositoryInterface;

class UpdateGrupo implements UseCaseInterface
{
    private GrupoRepositoryInterface $repository;

    public function __construct(GrupoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(mixed $input = null): bool
    {
        $id = $input['id'];
        unset($input['id']);
        
        return $this->repository->update($id, $input);
    }
}
