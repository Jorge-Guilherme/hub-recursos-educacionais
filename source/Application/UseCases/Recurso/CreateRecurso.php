<?php

namespace Application\UseCases\Recurso;

use Application\DTOs\CreateRecursoDTO;
use Application\UseCases\UseCaseInterface;
use Domain\Contracts\RecursoRepositoryInterface;
use Domain\Contracts\TagRepositoryInterface;

class CreateRecurso implements UseCaseInterface
{
    private RecursoRepositoryInterface $recursoRepository;
    private TagRepositoryInterface $tagRepository;

    public function __construct(
        RecursoRepositoryInterface $recursoRepository,
        TagRepositoryInterface $tagRepository
    ) {
        $this->recursoRepository = $recursoRepository;
        $this->tagRepository = $tagRepository;
    }

    public function execute(mixed $input): mixed
    {
        if ($input instanceof CreateRecursoDTO) {
            $dto = $input;
        } else {
            $dto = CreateRecursoDTO::fromArray($input);
        }

        $recurso = $this->recursoRepository->create([
            'titulo' => $dto->titulo,
            'descricao' => $dto->descricao,
            'tipo' => $dto->tipo,
            'url' => $dto->url,
        ]);

        if (!empty($dto->tags)) {
            $tags = $this->tagRepository->findOrCreateMany($dto->tags);
            $tagIds = array_column($tags, 'id');
            $this->recursoRepository->syncTags($recurso['id'], $tagIds);
        }

        return $this->recursoRepository->find($recurso['id']);
    }
}
