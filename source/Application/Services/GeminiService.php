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

            if (!$response->successful()) {
                $status = $response->status();
                $body = $response->body();
                
                Log::error('Erro na API do Gemini', [
                    'status' => $status,
                    'body' => $body
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

            return trim($data['candidates'][0]['content']['parts'][0]['text']);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Erro de conexão com Gemini', ['error' => $e->getMessage()]);
            throw new \Exception('Erro de conexão com a API do Gemini');
        } catch (\Exception $e) {
            Log::error('Erro ao gerar descrição', ['error' => $e->getMessage()]);
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
                        'temperature' => 0.4,
                        'maxOutputTokens' => 128,
                        'topP' => 0.9,
                        'topK' => 20,
                    ]
                ]);

            if (!$response->successful()) {
                $status = $response->status();
                $body = $response->body();
                
                Log::error('Erro na API do Gemini ao gerar tags', [
                    'status' => $status,
                    'body' => $body
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
            
            $tags = array_map('trim', explode(',', $texto));
            
            $tags = array_filter($tags, function($tag) {
                $len = strlen($tag);
                return $len >= 2 && $len <= 50;
            });
            
            $tags = array_map(function($tag) {
                return ucfirst(strtolower($tag));
            }, $tags);
            
            $tags = array_unique($tags);
            
            return array_slice(array_values($tags), 0, 8);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Erro de conexão com Gemini ao gerar tags', ['error' => $e->getMessage()]);
            throw new \Exception('Erro de conexão com a API do Gemini');
        } catch (\Exception $e) {
            Log::error('Erro ao gerar tags', ['error' => $e->getMessage()]);
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
            $contexto .= ": " . substr($descricao, 0, 150);
        }

        $prompt = <<<PROMPT
Gere 5 tags para: {$contexto}

Retorne apenas tags separadas por vírgula. Máximo 3 palavras por tag.
Foco: área, nível, tecnologia, conceitos.

Tags:
PROMPT;

        return $prompt;
    }
}
