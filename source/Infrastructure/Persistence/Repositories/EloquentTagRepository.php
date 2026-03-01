<?php

namespace Infrastructure\Persistence\Repositories;

use App\Models\Tag as TagModel;
use Domain\Contracts\TagRepositoryInterface;
use Illuminate\Support\Str;

class EloquentTagRepository extends EloquentRepository implements TagRepositoryInterface
{
    public function __construct(TagModel $model)
    {
        $this->model = $model;
    }

    public function find(int $id): ?array
    {
        $tag = $this->model->find($id);
        return $tag ? $this->toArray($tag) : null;
    }

    public function all(): array
    {
        return $this->model->all()->map(fn($t) => $this->toArray($t))->toArray();
    }

    public function create(array $data): array
    {
        $tag = $this->model->create($data);
        return $this->toArray($tag);
    }

    public function update(int $id, array $data): bool
    {
        $tag = $this->model->find($id);
        
        if (!$tag) {
            return false;
        }
        
        return $tag->update($data);
    }

    public function delete(int $id): bool
    {
        $tag = $this->model->find($id);
        
        if (!$tag) {
            return false;
        }
        
        return $tag->delete();
    }

    public function findBySlug(string $slug): ?array
    {
        $tag = $this->model->where('slug', $slug)->first();
        return $tag ? $this->toArray($tag) : null;
    }

    public function findByNome(string $nome): ?array
    {
        $tag = $this->model->where('nome', $nome)->first();
        return $tag ? $this->toArray($tag) : null;
    }

    public function findOrCreateByNome(string $nome): array
    {
        $slug = Str::slug($nome);
        
        $tag = $this->model->firstOrCreate(
            ['slug' => $slug],
            ['nome' => $nome]
        );
        
        return $this->toArray($tag);
    }

    public function findOrCreateMany(array $nomes): array
    {
        $tags = [];
        
        foreach ($nomes as $nome) {
            $tags[] = $this->findOrCreateByNome($nome);
        }
        
        return $tags;
    }

    private function toArray($tag): array
    {
        return [
            'id' => $tag->id,
            'nome' => $tag->nome,
            'slug' => $tag->slug,
            'created_at' => $tag->created_at?->toIso8601String(),
            'updated_at' => $tag->updated_at?->toIso8601String(),
        ];
    }
}
