<?php

namespace Application\DTOs;

use Domain\ValueObjects\RecursoTipo;
use Domain\ValueObjects\RecursoUrl;

class UpdateRecursoDTO extends DTO
{
    public ?string $titulo;
    public ?string $descricao;
    public ?string $tipo;
    public ?string $url;
    public ?array $tags;

    public function __construct(
        ?string $titulo = null,
        ?string $descricao = null,
        ?string $tipo = null,
        ?string $url = null,
        ?array $tags = null
    ) {
        $this->titulo = $titulo;
        $this->descricao = $descricao;
        $this->tipo = $tipo;
        $this->url = $url;
        $this->tags = $tags;
        
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->tipo !== null) {
            new RecursoTipo($this->tipo);
        }

        if ($this->url !== null) {
            new RecursoUrl($this->url);
        }
    }

    public static function fromArray(array $data): static
    {
        return new self(
            titulo: $data['titulo'] ?? null,
            descricao: $data['descricao'] ?? null,
            tipo: $data['tipo'] ?? null,
            url: $data['url'] ?? null,
            tags: $data['tags'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'tipo' => $this->tipo,
            'url' => $this->url,
            'tags' => $this->tags,
        ], fn($value) => $value !== null);
    }
}
