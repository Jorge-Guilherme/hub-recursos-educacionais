<?php

namespace Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Application\UseCases\Recurso\GetAllRecursos;
use Application\UseCases\Recurso\GetRecursoById;
use Application\UseCases\Recurso\CreateRecurso;
use Application\UseCases\Recurso\UpdateRecurso;
use Application\UseCases\Recurso\DeleteRecurso;
use Application\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecursoController extends Controller
{
    private GetAllRecursos $getAllRecursos;
    private GetRecursoById $getRecursoById;
    private CreateRecurso $createRecurso;
    private UpdateRecurso $updateRecurso;
    private DeleteRecurso $deleteRecurso;
    private GeminiService $geminiService;

    public function __construct(
        GetAllRecursos $getAllRecursos,
        GetRecursoById $getRecursoById,
        CreateRecurso $createRecurso,
        UpdateRecurso $updateRecurso,
        DeleteRecurso $deleteRecurso,
        GeminiService $geminiService
    ) {
        $this->getAllRecursos = $getAllRecursos;
        $this->getRecursoById = $getRecursoById;
        $this->createRecurso = $createRecurso;
        $this->updateRecurso = $updateRecurso;
        $this->deleteRecurso = $deleteRecurso;
        $this->geminiService = $geminiService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $tags = $request->get('tags');
            if (is_string($tags)) {
                $tags = array_filter(explode(',', $tags));
            }
            
            $data = $this->getAllRecursos->execute([
                'per_page' => $request->get('per_page', 15),
                'page' => $request->get('page', 1),
                'search' => $request->get('search', ''),
                'tipo' => $request->get('tipo'),
                'tags' => !empty($tags) ? $tags : null,
            ]);

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao listar recursos',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $recurso = $this->getRecursoById->execute(['id' => $id]);
            return response()->json($recurso);
        } catch (\RuntimeException $e) {
            return response()->json([
                'error' => 'Recurso não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao buscar recurso',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'titulo' => 'required|string|max:255',
                'descricao' => 'required|string',
                'tipo' => 'required|in:video,pdf,link',
                'url' => 'required|url',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:50',
            ]);

            $recurso = $this->createRecurso->execute($validated);

            return response()->json($recurso, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => 'Dados inválidos',
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao criar recurso',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'titulo' => 'sometimes|string|max:255',
                'descricao' => 'sometimes|string',
                'tipo' => 'sometimes|in:video,pdf,link',
                'url' => 'sometimes|url',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:50',
            ]);

            $recurso = $this->updateRecurso->execute([
                'id' => $id,
                'data' => $validated
            ]);

            return response()->json($recurso);
        } catch (\RuntimeException $e) {
            return response()->json([
                'error' => 'Recurso não encontrado'
            ], 404);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => 'Dados inválidos',
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao atualizar recurso',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->deleteRecurso->execute(['id' => $id]);
            
            return response()->json([
                'message' => 'Recurso excluído com sucesso'
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'error' => 'Recurso não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao excluir recurso',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function gerarDescricao(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'titulo' => 'required|string|max:255',
                'tipo' => 'required|in:video,pdf,link',
                'url' => 'nullable|url',
            ]);

            $descricao = $this->geminiService->gerarDescricao(
                $validated['titulo'],
                $validated['tipo'],
                $validated['url'] ?? null
            );

            return response()->json([
                'descricao' => $descricao
            ]);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            
            if (str_contains($message, '|')) {
                [$errorCode, $userMessage] = explode('|', $message, 2);
                
                $statusCode = match($errorCode) {
                    'RATE_LIMIT' => 429,
                    'AUTH_ERROR' => 401,
                    'BAD_REQUEST' => 400,
                    'SERVER_ERROR' => 503,
                    default => 500
                };
                
                return response()->json([
                    'error' => $errorCode,
                    'message' => $userMessage
                ], $statusCode);
            }
            
            return response()->json([
                'error' => 'GENERATION_ERROR',
                'message' => 'Erro ao gerar descrição com IA. Tente novamente.'
            ], 500);
        }
    }

    public function gerarTags(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'titulo' => 'required|string|max:255',
                'tipo' => 'required|in:video,pdf,link',
                'descricao' => 'nullable|string',
            ]);

            $tags = $this->geminiService->gerarTags(
                $validated['titulo'],
                $validated['tipo'],
                $validated['descricao'] ?? null
            );

            return response()->json([
                'tags' => $tags
            ]);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            
            if (str_contains($message, '|')) {
                [$errorCode, $userMessage] = explode('|', $message, 2);
                
                $statusCode = match($errorCode) {
                    'RATE_LIMIT' => 429,
                    'AUTH_ERROR' => 401,
                    'BAD_REQUEST' => 400,
                    'SERVER_ERROR' => 503,
                    default => 500
                };
                
                return response()->json([
                    'error' => $errorCode,
                    'message' => $userMessage
                ], $statusCode);
            }
            
            return response()->json([
                'error' => 'GENERATION_ERROR',
                'message' => 'Erro ao gerar tags com IA. Tente novamente.'
            ], 500);
        }
    }
}
