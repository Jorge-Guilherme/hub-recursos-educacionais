<?php

namespace Application\UseCases\Grupo;

use Application\UseCases\UseCaseInterface;
use Domain\Contracts\GrupoRepositoryInterface;

class GetGrupoById implements UseCaseInterface
{
    private GrupoRepositoryInterface $repository;

    public function __construct(GrupoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(mixed $input = null): ?array
    {
        $id = $input['id'];
        $withRecursos = $input['with_recursos'] ?? false;
        
        if ($withRecursos) {
            return $this->repository->findWithRecursos($id);
        }
        
        return $this->repository->find($id);
    }
}
