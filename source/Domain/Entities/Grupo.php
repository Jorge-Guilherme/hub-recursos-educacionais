<?php

namespace Domain\Entities;

class Grupo extends Entity
{
    private string $nome;
    private ?string $descricao;
    private array $recursos;

    public function __construct(
        string $nome,
        ?string $descricao = null,
        array $recursos = []
    ) {
        $this->nome = $nome;
        $this->descricao = $descricao;
        $this->recursos = $recursos;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): void
    {
        $this->nome = $nome;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(?string $descricao): void
    {
        $this->descricao = $descricao;
    }

    public function getRecursos(): array
    {
        return $this->recursos;
    }

    public function setRecursos(array $recursos): void
    {
        $this->recursos = $recursos;
    }

    public function addRecurso(Recurso $recurso): void
    {
        $this->recursos[] = $recurso;
    }

    public function removeRecurso(int $recursoId): void
    {
        $this->recursos = array_filter(
            $this->recursos, 
            fn($r) => $r->getId() !== $recursoId
        );
    }
}
