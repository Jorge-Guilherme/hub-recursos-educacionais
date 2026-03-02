<?php

namespace Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Application\UseCases\Grupo\GetAllGrupos;
use Application\UseCases\Grupo\GetGrupoById;
use Application\UseCases\Grupo\CreateGrupo;
use Application\UseCases\Grupo\UpdateGrupo;
use Application\UseCases\Grupo\DeleteGrupo;
use Application\UseCases\Grupo\SyncGrupoRecursos;
use Application\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GrupoController extends Controller
{
    private GetAllGrupos $getAllGrupos;
    private GetGrupoById $getGrupoById;
    private CreateGrupo $createGrupo;
    private UpdateGrupo $updateGrupo;
    private DeleteGrupo $deleteGrupo;
    private SyncGrupoRecursos $syncGrupoRecursos;
    private GeminiService $geminiService;

    public function __construct(
        GetAllGrupos $getAllGrupos,
        GetGrupoById $getGrupoById,
        CreateGrupo $createGrupo,
        UpdateGrupo $updateGrupo,
        DeleteGrupo $deleteGrupo,
        SyncGrupoRecursos $syncGrupoRecursos,
        GeminiService $geminiService
    ) {
        $this->getAllGrupos = $getAllGrupos;
        $this->getGrupoById = $getGrupoById;
        $this->createGrupo = $createGrupo;
        $this->updateGrupo = $updateGrupo;
        $this->deleteGrupo = $deleteGrupo;
        $this->syncGrupoRecursos = $syncGrupoRecursos;
        $this->geminiService = $geminiService;
    }

    public function index(): JsonResponse
    {
        try {
            $grupos = $this->getAllGrupos->execute();
            return response()->json($grupos);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao listar grupos',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $withRecursos = $request->query('with_recursos') === 'true';
            
            $grupo = $this->getGrupoById->execute([
                'id' => $id,
                'with_recursos' => $withRecursos
            ]);
            
            if (!$grupo) {
                return response()->json([
                    'error' => 'Grupo não encontrado'
                ], 404);
            }
            
            return response()->json($grupo);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao buscar grupo',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'descricao' => 'nullable|string',
                'recurso_ids' => 'nullable|array',
                'recurso_ids.*' => 'exists:recursos,id'
            ]);

            $grupo = $this->createGrupo->execute([
                'nome' => $validated['nome'],
                'descricao' => $validated['descricao'] ?? null,
            ]);

            if (!empty($validated['recurso_ids'])) {
                $this->syncGrupoRecursos->execute([
                    'grupo_id' => $grupo['id'],
                    'recurso_ids' => $validated['recurso_ids']
                ]);
                
                $grupo = $this->getGrupoById->execute([
                    'id' => $grupo['id'],
                    'with_recursos' => true
                ]);
            }

            return response()->json($grupo, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Dados inválidos',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao criar grupo',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nome' => 'sometimes|required|string|max:255',
                'descricao' => 'nullable|string',
                'recurso_ids' => 'nullable|array',
                'recurso_ids.*' => 'exists:recursos,id'
            ]);

            $updateData = [
                'id' => $id,
                'nome' => $validated['nome'] ?? null,
                'descricao' => $validated['descricao'] ?? null,
            ];
            
            $updateData = array_filter($updateData, fn($value) => $value !== null);

            $success = $this->updateGrupo->execute($updateData);

            if (!$success) {
                return response()->json([
                    'error' => 'Grupo não encontrado'
                ], 404);
            }

            if (isset($validated['recurso_ids'])) {
                $this->syncGrupoRecursos->execute([
                    'grupo_id' => $id,
                    'recurso_ids' => $validated['recurso_ids']
                ]);
            }

            $grupo = $this->getGrupoById->execute([
                'id' => $id,
                'with_recursos' => true
            ]);

            return response()->json($grupo);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Dados inválidos',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao atualizar grupo',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $success = $this->deleteGrupo->execute(['id' => $id]);

            if (!$success) {
                return response()->json([
                    'error' => 'Grupo não encontrado'
                ], 404);
            }

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao excluir grupo',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function gerarDescricao(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255',
            ]);

            $descricao = $this->geminiService->gerarDescricaoGrupo($validated['nome']);

            return response()->json([
                'descricao' => $descricao
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Dados inválidos',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            
            if (str_contains($message, '|')) {
                [$code, $userMessage] = explode('|', $message, 2);
                $statusCode = match($code) {
                    'RATE_LIMIT' => 429,
                    'AUTH_ERROR' => 401,
                    'BAD_REQUEST' => 400,
                    'SERVER_ERROR' => 503,
                    default => 500
                };
                
                return response()->json([
                    'error' => 'Erro ao gerar descrição com IA',
                    'message' => $userMessage
                ], $statusCode);
            }

            return response()->json([
                'error' => 'Erro ao gerar descrição',
                'message' => $message
            ], 500);
        }
    }

    public function syncRecursos(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'recurso_ids' => 'required|array',
                'recurso_ids.*' => 'exists:recursos,id'
            ]);

            $this->syncGrupoRecursos->execute([
                'grupo_id' => $id,
                'recurso_ids' => $validated['recurso_ids']
            ]);

            $grupo = $this->getGrupoById->execute([
                'id' => $id,
                'with_recursos' => true
            ]);

            return response()->json($grupo);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Dados inválidos',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao sincronizar recursos',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
