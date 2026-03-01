<?php

namespace Infrastructure\Persistence\Repositories;

use App\Models\Grupo as GrupoModel;
use Domain\Contracts\GrupoRepositoryInterface;

class EloquentGrupoRepository extends EloquentRepository implements GrupoRepositoryInterface
{
    public function __construct(GrupoModel $model)
    {
        $this->model = $model;
    }

    public function find(int $id): ?array
    {
        $grupo = $this->model->find($id);
        
        if (!$grupo) {
            return null;
        }
        
        return $this->toArray($grupo);
    }

    public function findWithRecursos(int $id): ?array
    {
        $grupo = $this->model->with(['recursos' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }, 'recursos.tags'])->find($id);
        
        if (!$grupo) {
            return null;
        }
        
        return $this->toArrayWithRecursos($grupo);
    }

    public function all(): array
    {
        return $this->model
            ->withCount('recursos')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($g) => $this->toArray($g))
            ->toArray();
    }

    public function create(array $data): array
    {
        $grupo = $this->model->create($data);
        return $this->toArray($grupo);
    }

    public function update(int $id, array $data): bool
    {
        $grupo = $this->model->find($id);
        
        if (!$grupo) {
            return false;
        }
        
        return $grupo->update($data);
    }

    public function delete(int $id): bool
    {
        $grupo = $this->model->find($id);
        
        if (!$grupo) {
            return false;
        }
        
        return $grupo->delete();
    }

    public function addRecurso(int $grupoId, int $recursoId): void
    {
        $grupo = $this->model->find($grupoId);
        
        if ($grupo && !$grupo->recursos()->where('recurso_id', $recursoId)->exists()) {
            $grupo->recursos()->attach($recursoId);
        }
    }

    public function removeRecurso(int $grupoId, int $recursoId): void
    {
        $grupo = $this->model->find($grupoId);
        
        if ($grupo) {
            $grupo->recursos()->detach($recursoId);
        }
    }

    public function syncRecursos(int $grupoId, array $recursoIds): void
    {
        $grupo = $this->model->find($grupoId);
        
        if ($grupo) {
            $grupo->recursos()->sync($recursoIds);
        }
    }

    public function getRecursosByGrupo(int $grupoId): array
    {
        $grupo = $this->model->with(['recursos' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }, 'recursos.tags'])->find($grupoId);
        
        if (!$grupo) {
            return [];
        }
        
        return $grupo->recursos->map(fn($recurso) => [
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
        ])->toArray();
    }

    private function toArray($grupo): array
    {
        return [
            'id' => $grupo->id,
            'nome' => $grupo->nome,
            'descricao' => $grupo->descricao,
            'recursos_count' => $grupo->recursos_count ?? 0,
            'created_at' => $grupo->created_at?->toIso8601String(),
            'updated_at' => $grupo->updated_at?->toIso8601String(),
        ];
    }

    private function toArrayWithRecursos($grupo): array
    {
        $data = $this->toArray($grupo);
        
        $data['recursos'] = $grupo->recursos->map(fn($recurso) => [
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
        ])->toArray();
        
        return $data;
    }
}
