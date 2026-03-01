<?php

namespace Domain\Contracts;

interface GrupoRepositoryInterface extends RepositoryInterface
{
    public function findWithRecursos(int $id);
    
    public function addRecurso(int $grupoId, int $recursoId): void;
    
    public function removeRecurso(int $grupoId, int $recursoId): void;
    
    public function syncRecursos(int $grupoId, array $recursoIds): void;
    
    public function getRecursosByGrupo(int $grupoId): array;
}
