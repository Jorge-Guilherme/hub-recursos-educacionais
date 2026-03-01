<?php

namespace Application\UseCases\Recurso;

use Application\UseCases\UseCaseInterface;
use Domain\Contracts\RecursoRepositoryInterface;

class GetRecursoById implements UseCaseInterface
{
    private RecursoRepositoryInterface $repository;

    public function __construct(RecursoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(mixed $input): mixed
    {
        $id = $input['id'] ?? $input;
        
        $recurso = $this->repository->find($id);
        
        if (!$recurso) {
            throw new \RuntimeException('Recurso não encontrado');
        }
        
        return $recurso;
    }
}
