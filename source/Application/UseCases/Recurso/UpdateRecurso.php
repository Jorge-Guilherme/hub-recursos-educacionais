<?php

namespace Application\UseCases\Recurso;

use Application\DTOs\UpdateRecursoDTO;
use Application\UseCases\UseCaseInterface;
use Domain\Contracts\RecursoRepositoryInterface;
use Domain\Contracts\TagRepositoryInterface;

class UpdateRecurso implements UseCaseInterface
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
        $id = $input['id'];
        
        $recurso = $this->recursoRepository->find($id);
        if (!$recurso) {
            throw new \RuntimeException('Recurso não encontrado');
        }

        if ($input['data'] instanceof UpdateRecursoDTO) {
            $dto = $input['data'];
        } else {
            $dto = UpdateRecursoDTO::fromArray($input['data']);
        }

        $data = $dto->toArray();
        $tags = null;
        
        if (isset($data['tags'])) {
            $tags = $data['tags'];
            unset($data['tags']);
        }

        if (!empty($data)) {
            $this->recursoRepository->update($id, $data);
        }

        if ($tags !== null) {
            $tagModels = $this->tagRepository->findOrCreateMany($tags);
            $tagIds = array_column($tagModels, 'id');
            $this->recursoRepository->syncTags($id, $tagIds);
        }

        return $this->recursoRepository->find($id);
    }
}
