<?php

namespace Domain\Contracts;

interface TagRepositoryInterface extends RepositoryInterface
{
    public function findBySlug(string $slug);
    
    public function findByNome(string $nome);
    
    public function findOrCreateByNome(string $nome);
    
    public function findOrCreateMany(array $nomes): array;
}
