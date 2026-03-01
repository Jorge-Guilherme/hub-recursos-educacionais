<?php

namespace Infrastructure\Persistence\Repositories;

use App\Models\Recurso as RecursoModel;
use Domain\Contracts\RecursoRepositoryInterface;

class EloquentRecursoRepository extends EloquentRepository implements RecursoRepositoryInterface
{
    public function __construct(RecursoModel $model)
    {
        $this->model = $model;
    }

    public function find(int $id): ?array
    {
        $recurso = $this->model->with('tags')->find($id);
        
        if (!$recurso) {
            return null;
        }
        
        return $this->toArray($recurso);
    }

    public function all(): array
    {
        return $this->model
            ->with('tags')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($r) => $this->toArray($r))
            ->toArray();
    }

    public function create(array $data): array
    {
        $recurso = $this->model->create($data);
        return $this->toArray($recurso->load('tags'));
    }

    public function update(int $id, array $data): bool
    {
        $recurso = $this->model->find($id);
        
        if (!$recurso) {
            return false;
        }
        
        return $recurso->update($data);
    }

    public function delete(int $id): bool
    {
        $recurso = $this->model->find($id);
        
        if (!$recurso) {
            return false;
        }
        
        return $recurso->delete();
    }

    public function findByTipo(string $tipo): array
    {
        return $this->model
            ->with('tags')
            ->where('tipo', $tipo)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($r) => $this->toArray($r))
            ->toArray();
    }

    public function findByTag(string $tagSlug): array
    {
        return $this->model
            ->with('tags')
            ->whereHas('tags', function ($query) use ($tagSlug) {
                $query->where('slug', $tagSlug);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($r) => $this->toArray($r))
            ->toArray();
    }

    public function paginate(int $perPage = 15, int $page = 1, string $search = '', ?string $tipo = null, ?array $tags = null): array
    {
        $query = $this->model->with('tags');
        
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'LIKE', "%{$search}%")
                  ->orWhere('descricao', 'LIKE', "%{$search}%");
            });
        }
        
        if (!empty($tipo)) {
            $query->where('tipo', $tipo);
        }
        
        if (!empty($tags)) {
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('slug', $tags);
            });
        }
        
        $query->orderBy('created_at', 'desc');
        
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        
        return [
            'data' => $paginator->items(),
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    public function search(string $query): array
    {
        return $this->model
            ->with('tags')
            ->where(function ($q) use ($query) {
                $q->where('titulo', 'LIKE', "%{$query}%")
                  ->orWhere('descricao', 'LIKE', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($r) => $this->toArray($r))
            ->toArray();
    }

    public function syncTags(int $recursoId, array $tagIds): void
    {
        $recurso = $this->model->find($recursoId);
        
        if ($recurso) {
            $recurso->tags()->sync($tagIds);
        }
    }

    private function toArray($recurso): array
    {
        return [
            'id' => $recurso->id,
            'titulo' => $recurso->titulo,
            'descricao' => $recurso->descricao,
            'tipo' => $recurso->tipo,
            'url' => $recurso->url,
            'tags' => $recurso->tags->map(fn($tag) => [
                'id' => $tag->id,
                'nome' => $tag->nome,
                'slug' => $tag->slug,
            ])->toArray(),
            'created_at' => $recurso->created_at?->toIso8601String(),
            'updated_at' => $recurso->updated_at?->toIso8601String(),
        ];
    }
}
