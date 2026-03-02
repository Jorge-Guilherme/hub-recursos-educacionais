<?php

namespace Application\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private ?string $apiKey;
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    /**
     * Gera uma descrição para um recurso educacional
     *
     * @param string $titulo Título do recurso
     * @param string $tipo Tipo do recurso (video, pdf, link)
     * @param string|null $url URL do recurso (opcional)
     * @return string Descrição gerada
     * @throws \Exception
     */
    public function gerarDescricao(string $titulo, string $tipo, ?string $url = null): string
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Chave da API do Gemini não configurada');
        }

        $prompt = $this->construirPrompt($titulo, $tipo, $url);
        $startTime = microtime(true);

        Log::info('[AI Request] Iniciando geração de descrição', [
            'action' => 'gerar_descricao',
            'titulo' => $titulo,
            'tipo' => $tipo,
            'url' => $url,
        ]);

        try {
            $response = Http::timeout(30)
                ->post($this->apiUrl . '?key=' . $this->apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 1024,
                        'topP' => 0.9,
                        'topK' => 40,
                    ]
                ]);

            $latency = round((microtime(true) - $startTime) * 1000, 2);

            if (!$response->successful()) {
                $status = $response->status();
                $body = $response->body();
                
                Log::error('[AI Request] Erro na API do Gemini', [
                    'action' => 'gerar_descricao',
                    'titulo' => $titulo,
                    'tipo' => $tipo,
                    'status' => $status,
                    'latency_ms' => $latency,
                    'error_body' => $body
                ]);
                
                $errorMessage = match(true) {
                    $status === 429 => 'RATE_LIMIT|Muitas requisições. Aguarde alguns segundos e tente novamente.',
                    $status === 401 || $status === 403 => 'AUTH_ERROR|Erro de autenticação com a API de IA. Verifique a chave da API.',
                    $status === 400 => 'BAD_REQUEST|Requisição inválida para a API de IA.',
                    $status >= 500 => 'SERVER_ERROR|Erro temporário no servidor de IA. Tente novamente em alguns instantes.',
                    default => 'UNKNOWN_ERROR|Erro ao comunicar com a API de IA.'
                };
                
                throw new \Exception($errorMessage);
            }

            $data = $response->json();
            
            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                throw new \Exception('Resposta inesperada da API do Gemini');
            }

            $description = trim($data['candidates'][0]['content']['parts'][0]['text']);
            
            $promptTokens = $data['usageMetadata']['promptTokenCount'] ?? null;
            $completionTokens = $data['usageMetadata']['candidatesTokenCount'] ?? null;
            $totalTokens = $data['usageMetadata']['totalTokenCount'] ?? null;

            Log::info('[AI Request] Descrição gerada com sucesso', [
                'action' => 'gerar_descricao',
                'titulo' => $titulo,
                'tipo' => $tipo,
                'status' => 'success',
                'latency_ms' => $latency,
                'latency_s' => round($latency / 1000, 2),
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_tokens' => $totalTokens,
                'description_length' => strlen($description),
            ]);

            return $description;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $latency = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::error('[AI Request] Erro de conexão com Gemini', [
                'action' => 'gerar_descricao',
                'titulo' => $titulo,
                'tipo' => $tipo,
                'status' => 'connection_error',
                'latency_ms' => $latency,
                'error' => $e->getMessage()
            ]);
            
            throw new \Exception('Erro de conexão com a API do Gemini');
        } catch (\Exception $e) {
            $latency = round((microtime(true) - $startTime) * 1000, 2);
            
            if (!str_contains($e->getMessage(), '|')) {
                Log::error('[AI Request] Erro ao gerar descrição', [
                    'action' => 'gerar_descricao',
                    'titulo' => $titulo,
                    'tipo' => $tipo,
                    'status' => 'error',
                    'latency_ms' => $latency,
                    'error' => $e->getMessage()
                ]);
            }
            
            throw $e;
        }
    }

    /**
     * Constrói o prompt para a API do Gemini
     */
    private function construirPrompt(string $titulo, string $tipo, ?string $url): string
    {
        $tipoTexto = match($tipo) {
            'video' => 'vídeo',
            'pdf' => 'PDF',
            'link' => 'site',
            default => 'recurso'
        };

        $urlInfo = $url ? " URL: {$url}" : "";

        $prompt = <<<PROMPT
Crie uma descrição educacional ENVOLVENTE e COMPLETA para "{$titulo}" ({$tipoTexto}).{$urlInfo}

Escreva 1 parágrafos curtos 80 palavras no total incluindo:
- Abertura atraente
- Principais aprendizados
- Relevância prática
- Público-alvo (se aplicável)

Tom: Profissional mas inspirador. Português brasileiro.
NÃO use formatação, bullet points ou repita o título.
FINALIZE o texto com ponto final. Texto deve estar COMPLETO.

Descrição:
PROMPT;

        return $prompt;
    }

    /**
     * Gera uma descrição para um grupo de recursos educacionais
     *
     * @param string $nome Nome do grupo
     * @return string Descrição gerada
     * @throws \Exception
     */
    public function gerarDescricaoGrupo(string $nome): string
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Chave da API do Gemini não configurada');
        }

        $prompt = $this->construirPromptGrupo($nome);
        $startTime = microtime(true);

        Log::info('[AI Request] Iniciando geração de descrição de grupo', [
            'action' => 'gerar_descricao_grupo',
            'nome' => $nome,
        ]);

        try {
            $response = Http::timeout(30)
                ->post($this->apiUrl . '?key=' . $this->apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 1024,
                        'topP' => 0.9,
                        'topK' => 40,
                    ]
                ]);

            $latency = round((microtime(true) - $startTime) * 1000, 2);

            if (!$response->successful()) {
                $status = $response->status();
                $body = $response->body();
                
                Log::error('[AI Request] Erro na API do Gemini ao gerar descrição de grupo', [
                    'action' => 'gerar_descricao_grupo',
                    'nome' => $nome,
                    'status' => $status,
                    'latency_ms' => $latency,
                    'error_body' => $body
                ]);
                
                $errorMessage = match(true) {
                    $status === 429 => 'RATE_LIMIT|Muitas requisições. Aguarde alguns segundos e tente novamente.',
                    $status === 401 || $status === 403 => 'AUTH_ERROR|Erro de autenticação com a API de IA. Verifique a chave da API.',
                    $status === 400 => 'BAD_REQUEST|Requisição inválida para a API de IA.',
                    $status >= 500 => 'SERVER_ERROR|Erro temporário no servidor de IA. Tente novamente em alguns instantes.',
                    default => 'UNKNOWN_ERROR|Erro ao comunicar com a API de IA.'
                };
                
                throw new \Exception($errorMessage);
            }

            $data = $response->json();
            
            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                throw new \Exception('Resposta inesperada da API do Gemini');
            }

            $description = trim($data['candidates'][0]['content']['parts'][0]['text']);
            
            $promptTokens = $data['usageMetadata']['promptTokenCount'] ?? null;
            $completionTokens = $data['usageMetadata']['candidatesTokenCount'] ?? null;
            $totalTokens = $data['usageMetadata']['totalTokenCount'] ?? null;

            Log::info('[AI Request] Descrição de grupo gerada com sucesso', [
                'action' => 'gerar_descricao_grupo',
                'nome' => $nome,
                'status' => 'success',
                'latency_ms' => $latency,
                'latency_s' => round($latency / 1000, 2),
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_tokens' => $totalTokens,
                'text_length' => strlen($description),
            ]);

            return $description;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $latency = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::error('[AI Request] Erro de conexão com Gemini ao gerar descrição de grupo', [
                'action' => 'gerar_descricao_grupo',
                'nome' => $nome,
                'status' => 'connection_error',
                'latency_ms' => $latency,
                'error' => $e->getMessage()
            ]);
            
            throw new \Exception('Erro de conexão com a API do Gemini');
        } catch (\Exception $e) {
            $latency = round((microtime(true) - $startTime) * 1000, 2);
            
            if (!str_contains($e->getMessage(), '|')) {
                Log::error('[AI Request] Erro ao gerar descrição de grupo', [
                    'action' => 'gerar_descricao_grupo',
                    'nome' => $nome,
                    'status' => 'error',
                    'latency_ms' => $latency,
                    'error' => $e->getMessage()
                ]);
            }
            
            throw $e;
        }
    }

    /**
     * Constrói o prompt para gerar descrição de grupo
     */
    private function construirPromptGrupo(string $nome): string
    {
        $prompt = <<<PROMPT
Crie uma descrição educacional ENVOLVENTE e COMPLETA para o grupo de materiais didáticos "{$nome}".

Este é um agrupamento de diversos recursos educacionais (vídeos, PDFs, sites) organizados em torno de um tema ou objetivo específico.

Escreva 1 parágrafo curto de 80 palavras no total incluindo:
- Objetivo do grupo de estudos
- Tipo de conteúdo que pode conter
- Quem pode se beneficiar deste agrupamento
- Relevância para o aprendizado

Tom: Profissional mas acolhedor. Português brasileiro.
NÃO use formatação, bullet points ou repita o nome do grupo.
FINALIZE o texto com ponto final. Texto deve estar COMPLETO.

Descrição:
PROMPT;

        return $prompt;
    }

    /**
     * Gera tags relevantes para um recurso educacional
     *
     * @param string $titulo Título do recurso
     * @param string $tipo Tipo do recurso
     * @param string|null $descricao Descrição do recurso (opcional)
     * @return array Lista de tags geradas
     * @throws \Exception
     */
    public function gerarTags(string $titulo, string $tipo, ?string $descricao = null): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Chave da API do Gemini não configurada');
        }

        $prompt = $this->construirPromptTags($titulo, $tipo, $descricao);
        $startTime = microtime(true);

        Log::info('[AI Request] Iniciando geração de tags', [
            'action' => 'gerar_tags',
            'titulo' => $titulo,
            'tipo' => $tipo,
            'has_descricao' => !empty($descricao),
        ]);

        try {
            $response = Http::timeout(30)
                ->post($this->apiUrl . '?key=' . $this->apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.5,
                        'maxOutputTokens' => 250,
                        'topP' => 0.9,
                        'topK' => 40,
                    ]
                ]);

            $latency = round((microtime(true) - $startTime) * 1000, 2);

            if (!$response->successful()) {
                $status = $response->status();
                $body = $response->body();
                
                Log::error('[AI Request] Erro na API do Gemini ao gerar tags', [
                    'action' => 'gerar_tags',
                    'titulo' => $titulo,
                    'tipo' => $tipo,
                    'status' => $status,
                    'latency_ms' => $latency,
                    'error_body' => $body
                ]);
                
                $errorMessage = match(true) {
                    $status === 429 => 'RATE_LIMIT|Muitas requisições. Aguarde alguns segundos e tente novamente.',
                    $status === 401 || $status === 403 => 'AUTH_ERROR|Erro de autenticação com a API de IA. Verifique a chave da API.',
                    $status === 400 => 'BAD_REQUEST|Requisição inválida para a API de IA.',
                    $status >= 500 => 'SERVER_ERROR|Erro temporário no servidor de IA. Tente novamente em alguns instantes.',
                    default => 'UNKNOWN_ERROR|Erro ao comunicar com a API de IA.'
                };
                
                throw new \Exception($errorMessage);
            }

            $data = $response->json();
            
            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                throw new \Exception('Resposta inesperada da API do Gemini');
            }

            $texto = trim($data['candidates'][0]['content']['parts'][0]['text']);
            
            // Log da resposta bruta para debug
            Log::debug('[AI Request] Resposta bruta de tags', [
                'texto_original' => $texto
            ]);
            
            // Remove possíveis textos adicionais (ex: "Tags: palavra1,palavra2")
            $texto = preg_replace('/^(tags?:\s*)/i', '', $texto);
            $texto = preg_replace('/\n.*$/', '', $texto); // Remove quebras de linha e texto após elas
            
            // Separa por vírgula
            $tags = explode(',', $texto);
            
            // Processa cada tag
            $tags = array_map(function($tag) {
                // Remove espaços no início e fim
                $tag = trim($tag);
                // Remove apenas pontos e aspas no fim
                $tag = rtrim($tag, '."\'\'');
                // Remove apenas aspas no início
                $tag = ltrim($tag, '"\'\'');
                // Capitaliza primeira letra de cada palavra preservando acentos
                return mb_convert_case($tag, MB_CASE_TITLE, 'UTF-8');
            }, $tags);
            
            // Filtra tags válidas (entre 3 e 50 caracteres, não vazias)
            $tags = array_filter($tags, function($tag) {
                $tag = trim($tag);
                $len = mb_strlen($tag, 'UTF-8');
                // Verifica se tem pelo menos 3 caracteres e não é só números/símbolos
                return $len >= 3 && $len <= 50 && preg_match('/[a-zA-ZÀ-ÿ]/', $tag);
            });
            
            // Remove duplicatas (case-insensitive)
            $tagsLower = array_map(fn($t) => mb_strtolower($t, 'UTF-8'), $tags);
            $tags = array_intersect_key($tags, array_unique($tagsLower));
            
            // Retorna até 8 tags
            $finalTags = array_values(array_slice($tags, 0, 8));
            
            $promptTokens = $data['usageMetadata']['promptTokenCount'] ?? null;
            $completionTokens = $data['usageMetadata']['candidatesTokenCount'] ?? null;
            $totalTokens = $data['usageMetadata']['totalTokenCount'] ?? null;

            Log::info('[AI Request] Tags geradas com sucesso', [
                'action' => 'gerar_tags',
                'titulo' => $titulo,
                'tipo' => $tipo,
                'status' => 'success',
                'latency_ms' => $latency,
                'latency_s' => round($latency / 1000, 2),
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_tokens' => $totalTokens,
                'tags_count' => count($finalTags),
                'tags' => $finalTags,
            ]);

            return $finalTags;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $latency = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::error('[AI Request] Erro de conexão com Gemini ao gerar tags', [
                'action' => 'gerar_tags',
                'titulo' => $titulo,
                'tipo' => $tipo,
                'status' => 'connection_error',
                'latency_ms' => $latency,
                'error' => $e->getMessage()
            ]);
            
            throw new \Exception('Erro de conexão com a API do Gemini');
        } catch (\Exception $e) {
            $latency = round((microtime(true) - $startTime) * 1000, 2);
            
            if (!str_contains($e->getMessage(), '|')) {
                Log::error('[AI Request] Erro ao gerar tags', [
                    'action' => 'gerar_tags',
                    'titulo' => $titulo,
                    'tipo' => $tipo,
                    'status' => 'error',
                    'latency_ms' => $latency,
                    'error' => $e->getMessage()
                ]);
            }
            
            throw $e;
        }
    }

    /**
     * Constrói o prompt para gerar tags
     */
    private function construirPromptTags(string $titulo, string $tipo, ?string $descricao): string
    {
        $contexto = "{$titulo} ({$tipo})";
        
        if ($descricao) {
            $contexto .= ": " . substr($descricao, 0, 200);
        }

        $prompt = <<<PROMPT
Analise este recurso educacional e gere palavras-chave (tags) relevantes:

{$contexto}

REGRAS OBRIGATÓRIAS:
- Gere entre 4 e 6 tags
- Cada tag deve ter entre 1 e 3 palavras
- Separe tags APENAS com vírgula (sem espaço após a vírgula)
- Não adicione numeração, bullets ou formatação
- Foco em: área de conhecimento, tecnologias, conceitos-chave, público-alvo
- Use termos específicos e objetivos
- Não use palavras genéricas: "conteúdo", "material", "recurso", "educacional"
- Mantenha acentuação correta em português
- NÃO quebre palavras no meio
- Retorne APENAS as tags separadas por vírgula, nada mais

Exemplo de formato correto: Programação,Python,Iniciante,Algoritmos,Lógica

Tags:
PROMPT;

        return $prompt;
    }
}
