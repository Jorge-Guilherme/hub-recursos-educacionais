<?php

namespace Domain\Contracts;

interface RecursoRepositoryInterface extends RepositoryInterface
{
    public function findByTipo(string $tipo): array;
    
    public function findByTag(string $tagSlug): array;
    
    public function paginate(int $perPage = 15, int $page = 1, string $search = ''): array;
    
    public function search(string $query): array;
    
    public function syncTags(int $recursoId, array $tagIds): void;
}
