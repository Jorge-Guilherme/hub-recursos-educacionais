<?php

namespace Application\DTOs;

use Domain\ValueObjects\RecursoTipo;
use Domain\ValueObjects\RecursoUrl;

class CreateRecursoDTO extends DTO
{
    public string $titulo;
    public string $descricao;
    public string $tipo;
    public string $url;
    public array $tags;

    public function __construct(
        string $titulo,
        string $descricao,
        string $tipo,
        string $url,
        array $tags = []
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
        if (empty($this->titulo)) {
            throw new \InvalidArgumentException('Título é obrigatório');
        }

        if (empty($this->descricao)) {
            throw new \InvalidArgumentException('Descrição é obrigatória');
        }

        new RecursoTipo($this->tipo);
        new RecursoUrl($this->url);
    }

    public static function fromArray(array $data): static
    {
        return new self(
            titulo: $data['titulo'] ?? '',
            descricao: $data['descricao'] ?? '',
            tipo: $data['tipo'] ?? '',
            url: $data['url'] ?? '',
            tags: $data['tags'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'tipo' => $this->tipo,
            'url' => $this->url,
            'tags' => $this->tags,
        ];
    }
}
