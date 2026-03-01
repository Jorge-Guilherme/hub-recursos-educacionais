<?php

namespace Application\UseCases\Recurso;

use Application\UseCases\UseCaseInterface;
use Domain\Contracts\RecursoRepositoryInterface;

class GetAllRecursos implements UseCaseInterface
{
    private RecursoRepositoryInterface $repository;

    public function __construct(RecursoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(mixed $input = null): array
    {
        $perPage = $input['per_page'] ?? 15;
        $page = $input['page'] ?? 1;
        $search = $input['search'] ?? '';
        $tipo = $input['tipo'] ?? null;
        $tags = $input['tags'] ?? null;
        
        return $this->repository->paginate($perPage, $page, $search, $tipo, $tags);
    }
}
