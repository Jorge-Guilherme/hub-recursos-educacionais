<?php

namespace Application\UseCases\Grupo;

use Application\UseCases\UseCaseInterface;
use Domain\Contracts\GrupoRepositoryInterface;

class SyncGrupoRecursos implements UseCaseInterface
{
    private GrupoRepositoryInterface $repository;

    public function __construct(GrupoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(mixed $input = null): mixed
    {
        $grupoId = $input['grupo_id'];
        $recursoIds = $input['recurso_ids'] ?? [];
        
        $this->repository->syncRecursos($grupoId, $recursoIds);
        
        return true;
    }
}
