<?php

namespace Application\Services;

use Application\DTOs\DTO;
use Domain\Contracts\RepositoryInterface;

abstract class BaseService
{
    protected RepositoryInterface $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(): array
    {
        return $this->repository->all();
    }

    public function getById(int $id)
    {
        return $this->repository->find($id);
    }

    public function create(DTO $dto)
    {
        return $this->repository->create($dto->toArray());
    }

    public function update(int $id, DTO $dto)
    {
        return $this->repository->update($id, $dto->toArray());
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
