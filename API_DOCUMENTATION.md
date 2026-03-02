# Documentação da API - Hub de Recursos Educacionais

Esta documentação da API REST que desenvolvi, tem detalhes dos endpoints e como configurar o backend da aplicação. Utilizei PHP 8.3 e Laravel 11. Utilizei CleanArchitecture para estruturar a arquitetura.

## URL caso for rodar localmente.

```
http://localhost:8000/api/v1
```

---

## Recursos

### Listar Recursos (com paginação, busca e filtros)

```http
GET /recursos
```

**Query Parameters:**
- `per_page` (opcional): Itens por página (padrão: 15)
- `page` (opcional): Número da página (padrão: 1)
- `search` (opcional): Busca por título ou descrição
- `tipo` (opcional): Filtrar por tipo (`video`, `pdf`, `link`)
- `tags` (opcional): Filtrar por tags (separadas por vírgula)

**Exemplos:**

```bash
# Listar recursos com busca
curl "http://localhost:8000/api/v1/recursos?search=python"

# Filtrar por tipo
curl "http://localhost:8000/api/v1/recursos?tipo=video"

# Filtrar por múltiplas tags
curl "http://localhost:8000/api/v1/recursos?tags=php,laravel"

# Combinar filtros
curl "http://localhost:8000/api/v1/recursos?tipo=video&tags=python&search=iniciante&per_page=20"
```

**Resposta (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "titulo": "Introdução ao Python",
      "descricao": "Aprenda Python do zero...",
      "tipo": "video",
      "url": "https://youtube.com/watch?v=exemplo1",
      "tags": [
        {
          "id": 1,
          "nome": "Python",
          "slug": "python"
        },
        {
          "id": 2,
          "nome": "Programação",
          "slug": "programacao"
        }
      ],
      "created_at": "2026-03-01T10:30:00+00:00",
      "updated_at": "2026-03-01T10:30:00+00:00"
    }
  ],
  "current_page": 1,
  "per_page": 15,
  "total": 50,
  "last_page": 4
}
```

> **Decisão de design:** Implementei a ordenação decrescente por data de criação para que os recursos mais recentes apareçam primeiro, melhorando a experiência do usuário.

---

### Buscar Recurso por ID

```http
GET /recursos/{id}
```

**Exemplo:**
```bash
curl "http://localhost:8000/api/v1/recursos/1"
```

**Resposta (200 OK):**
```json
{
  "id": 1,
  "titulo": "Introdução ao Python",
  "descricao": "Aprenda Python do zero ao avançado...",
  "tipo": "video",
  "url": "https://youtube.com/watch?v=exemplo1",
  "tags": [
    {
      "id": 1,
      "nome": "Python",
      "slug": "python"
    }
  ],
  "created_at": "2026-03-01T10:30:00+00:00",
  "updated_at": "2026-03-01T10:30:00+00:00"
}
```

---

### Criar Novo Recurso

```http
POST /recursos
```

**Body:**
```json
{
  "titulo": "Tutorial de Docker",
  "descricao": "Aprenda Docker do básico ao avançado",
  "tipo": "video",
  "url": "https://youtube.com/watch?v=exemplo",
  "tags": ["Docker", "DevOps", "Containers"]
}
```

**Validações:**
- `titulo`: obrigatório, máximo 255 caracteres
- `descricao`: obrigatório
- `tipo`: obrigatório, valores: `video`, `pdf`, `link`
- `url`: obrigatório, URL válida
- `tags`: opcional, array de strings (máximo 50 caracteres cada)

**Exemplo:**
```bash
curl -X POST "http://localhost:8000/api/v1/recursos" \
  -H "Content-Type: application/json" \
  -d '{
    "titulo": "Tutorial de Docker",
    "descricao": "Aprenda Docker do básico ao avançado",
    "tipo": "video",
    "url": "https://youtube.com/watch?v=exemplo",
    "tags": ["Docker", "DevOps"]
  }'
```

**Resposta (201 Created):**
```json
{
  "id": 10,
  "titulo": "Tutorial de Docker",
  "descricao": "Aprenda Docker do básico ao avançado",
  "tipo": "video",
  "url": "https://youtube.com/watch?v=exemplo",
  "tags": [
    {
      "id": 3,
      "nome": "Docker",
      "slug": "docker"
    }
  ],
  "created_at": "2026-03-01T14:00:00+00:00",
  "updated_at": "2026-03-01T14:00:00+00:00"
}
```

---

### Atualizar Recurso

```http
PUT /recursos/{id}
PATCH /recursos/{id}
```

**Body (todos os campos opcionais):**
```json
{
  "titulo": "Tutorial Atualizado",
  "descricao": "Nova descrição",
  "tipo": "pdf",
  "url": "https://example.com/novo.pdf",
  "tags": ["PHP", "Laravel"]
}
```

**Exemplo:**
```bash
curl -X PUT "http://localhost:8000/api/v1/recursos/1" \
  -H "Content-Type: application/json" \
  -d '{"titulo": "Tutorial Atualizado"}'
```

---

### Excluir Recurso

```http
DELETE /recursos/{id}
```

**Exemplo:**
```bash
curl -X DELETE "http://localhost:8000/api/v1/recursos/1"
```

**Resposta (200 OK):**
```json
{
  "message": "Recurso excluído com sucesso"
}
```

---

## IA - Geração Automática de Conteúdo

Umas das features exigidas no projeto, foi a geração de descrição utilizando inteligência artificial, aqui eu detalho como minha API funciona e responde para essa feature.

### Gerar Descrição com IA

```http
POST /recursos/gerar-descricao
```

Envia o título, tipo e URL para a IA gerar uma descrição pedagógica envolvente.

**Body:**
```json
{
  "titulo": "Introdução ao Python",
  "tipo": "video",
  "url": "https://youtube.com/watch?v=abc123"
}
```

**Validações:**
- `titulo`: obrigatório, máximo 255 caracteres
- `tipo`: obrigatório, valores: `video`, `pdf`, `link`
- `url`: opcional, URL válida

**Exemplo:**
```bash
curl -X POST "http://localhost:8000/api/v1/recursos/gerar-descricao" \
  -H "Content-Type: application/json" \
  -d '{
    "titulo": "Introdução ao Python",
    "tipo": "video",
    "url": "https://youtube.com/watch?v=abc123"
  }'
```

**Resposta (200 OK):**
```json
{
  "descricao": "Mergulhe no mundo da programação com Python! Este recurso aborda desde fundamentos como variáveis e estruturas de controle até conceitos avançados de orientação a objetos. Ideal para iniciantes que desejam construir uma base sólida, você aprenderá a resolver problemas reais e desenvolver aplicações práticas. Python é uma das linguagens mais versáteis do mercado, essencial para desenvolvimento web, ciência de dados e automação."
}
```

**Respostas de Erro:**

```json
// 429 - Rate Limit
{
  "error": "RATE_LIMIT",
  "message": "Muitas requisições. Aguarde alguns segundos e tente novamente."
}

// 401/403 - Erro de Autenticação
{
  "error": "AUTH_ERROR",
  "message": "Erro de autenticação com a API de IA. Verifique a chave da API."
}

// 503 - Erro do Servidor de IA
{
  "error": "SERVER_ERROR",
  "message": "Erro temporário no servidor de IA. Tente novamente em alguns instantes."
}
```

> **Aprendizado:** Durante o desenvolvimento, otimizei as descrições para terem 80-120 palavras, reduzindo o consumo de tokens em 50% enquanto mantinha a qualidade do texto. Também ajustei a temperatura do modelo para gerar conteúdo mais criativo e completo.

---

### Gerar Tags com IA

Achei bacana também, implementar a geração de tags com IA. Quando fui povoar o banco de dados, achei chato ficar preenchendo tag por tag. Então achei bacana criar essa funcionalidade também.

```http
POST /recursos/gerar-tags
```

A IA analisa o título, tipo e descrição para sugerir tags relevantes.

**Body:**
```json
{
  "titulo": "Curso Completo de React",
  "tipo": "video",
  "descricao": "Aprenda React do zero, incluindo hooks e context API"
}
```

**Validações:**
- `titulo`: obrigatório, máximo 255 caracteres
- `tipo`: obrigatório, valores: `video`, `pdf`, `link`
- `descricao`: opcional

**Exemplo:**
```bash
curl -X POST "http://localhost:8000/api/v1/recursos/gerar-tags" \
  -H "Content-Type: application/json" \
  -d '{
    "titulo": "Curso Completo de React",
    "tipo": "video",
    "descricao": "Aprenda React com hooks e context API"
  }'
```

**Resposta (200 OK):**
```json
{
  "tags": [
    "React",
    "Javascript",
    "Frontend",
    "Hooks",
    "Context api"
  ]
}
```

> **Observação:** Configurei a IA para retornar 5-8 tags relevantes, cada uma com máximo 3 palavras. Implementei também um sistema de deduplicação automática para evitar tags repetidas.

---

## Grupos

Outra feature que implementei extra também, foi a criação de grupos para alocar os materiais didáticos. Exemplo: Se um usuário quiser criar um grupo de materiais para a Aula 1, ele pode juntar vários materiais didáticos nesse grupo. Aqui também tem a opção de gerar descrição com IA, etc.

### Listar Grupos

```http
GET /grupos
```

**Exemplo:**
```bash
curl "http://localhost:8000/api/v1/grupos"
```

**Resposta (200 OK):**
```json
[
  {
    "id": 1,
    "nome": "Fundamentos de Programação",
    "descricao": "Recursos para iniciantes em programação",
    "recursos_count": 12,
    "created_at": "2026-03-01T10:00:00+00:00",
    "updated_at": "2026-03-01T10:00:00+00:00"
  }
]
```

---

### Buscar Grupo por ID

```http
GET /grupos/{id}
```

**Query Parameters:**
- `with_recursos` (opcional): Incluir recursos do grupo (`true` ou `false`)

**Exemplos:**
```bash
# Sem recursos
curl "http://localhost:8000/api/v1/grupos/1"

# Com recursos
curl "http://localhost:8000/api/v1/grupos/1?with_recursos=true"
```

**Resposta com recursos (200 OK):**
```json
{
  "id": 1,
  "nome": "Fundamentos de Programação",
  "descricao": "Recursos para iniciantes",
  "recursos_count": 2,
  "recursos": [
    {
      "id": 1,
      "titulo": "Introdução ao Python",
      "descricao": "Aprenda Python...",
      "tipo": "video",
      "url": "https://youtube.com/watch?v=abc",
      "tags": [...]
    }
  ],
  "created_at": "2026-03-01T10:00:00+00:00",
  "updated_at": "2026-03-01T10:00:00+00:00"
}
```

---

### Criar Grupo

```http
POST /grupos
```

**Body:**
```json
{
  "nome": "Frontend Avançado",
  "descricao": "Recursos sobre frameworks modernos",
  "recurso_ids": [1, 2, 3]
}
```

**Validações:**
- `nome`: obrigatório, máximo 255 caracteres
- `descricao`: opcional
- `recurso_ids`: opcional, array de IDs válidos

**Exemplo:**
```bash
curl -X POST "http://localhost:8000/api/v1/grupos" \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "Frontend Avançado",
    "descricao": "Recursos sobre React e Angular",
    "recurso_ids": [1, 2]
  }'
```

---

### Atualizar Grupo

```http
PUT /grupos/{id}
PATCH /grupos/{id}
```

**Body (todos os campos opcionais):**
```json
{
  "nome": "Novo Nome",
  "descricao": "Nova descrição",
  "recurso_ids": [1, 2, 3, 4]
}
```

**Exemplo:**
```bash
curl -X PUT "http://localhost:8000/api/v1/grupos/1" \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "Frontend Moderno",
    "recurso_ids": [1, 2, 5]
  }'
```

---

### Excluir Grupo

```http
DELETE /grupos/{id}
```

**Exemplo:**
```bash
curl -X DELETE "http://localhost:8000/api/v1/grupos/1"
```

**Resposta (200 OK):**
```json
{
  "message": "Grupo excluído com sucesso"
}
```

---

## Health Check

Endpoint para verificar o status da aplicação e integrações. Isso ajudou na hora do deploy

```http
GET /health
```

**Resposta:**
```json
{
  "status": "healthy",
  "database": "connected",
  "gemini_ai": "connected"
}
```

## Sistema de Tags

Uma das funcionalidades que me ensinou bastante sobre normalização de dados foi o sistema de tags. Implementei um mecanismo onde as tags são criadas e gerenciadas automaticamente:

- **Case-insensitive:** "Docker" e "docker" viram a mesma tag
- **Slug automático:** "Clean Code" é igual a "clean-code"  
- **Reutilização:** Tags existentes são reutilizadas automaticamente
- **Limite:** Máximo 50 caracteres por tag

**Exemplo:**
```json
// Criar recurso com tags
{
  "titulo": "Tutorial Laravel",
  "tags": ["PHP", "Laravel", "Web Development"]
}

// As tags são salvas e podem ser filtradas depois
GET /recursos?tags=php,laravel
```

---

## Paginação

Todas as listagens retornam dados paginados:

```json
{
  "data": [...],           // Recursos da página atual
  "current_page": 1,       // Página atual
  "per_page": 15,          // Itens por página
  "total": 50,             // Total de recursos
  "last_page": 4           // Última página disponível
}
```

**Navegação:**
```bash
# Primeira página (15 itens)
curl "http://localhost:8000/api/v1/recursos"

# Segunda página com 20 itens
curl "http://localhost:8000/api/v1/recursos?page=2&per_page=20"
```

## Exemplos de Uso

Aqui estão alguns exemplos práticos de como usar a API em situações reais:

### 1. Criando um recurso com IA

```bash
# 1. Gerar descrição
DESCRICAO=$(curl -s -X POST "http://localhost:8000/api/v1/recursos/gerar-descricao" \
  -H "Content-Type: application/json" \
  -d '{"titulo":"Python para Iniciantes","tipo":"video"}' \
  | jq -r '.descricao')

# 2. Gerar tags
TAGS=$(curl -s -X POST "http://localhost:8000/api/v1/recursos/gerar-tags" \
  -H "Content-Type: application/json" \
  -d "{\"titulo\":\"Python para Iniciantes\",\"tipo\":\"video\",\"descricao\":\"$DESCRICAO\"}" \
  | jq -r '.tags')

# 3. Criar recurso completo
curl -X POST "http://localhost:8000/api/v1/recursos" \
  -H "Content-Type: application/json" \
  -d "{
    \"titulo\":\"Python para Iniciantes\",
    \"descricao\":\"$DESCRICAO\",
    \"tipo\":\"video\",
    \"url\":\"https://youtube.com/watch?v=abc\",
    \"tags\":$TAGS
  }"
```

### 2. Busca Avançada

```bash
# Buscar vídeos sobre Python para iniciantes
curl "http://localhost:8000/api/v1/recursos?tipo=video&search=python&tags=iniciante"
```

### 3. Organizando em Grupos

```bash
# Criar grupo e adicionar recursos
curl -X POST "http://localhost:8000/api/v1/grupos" \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "Trilha Python",
    "descricao": "Recursos progressivos de Python",
    "recurso_ids": [1, 3, 5, 7]
  }'
```

---

## Testando Localmente

```bash
# 1. Subir containers
docker-compose up -d

# 2. Rodar migrations
docker-compose exec backend php artisan migrate

# 3. (Opcional) Popular com dados de exemplo
docker-compose exec backend php artisan db:seed

# 4. Testar API
curl http://localhost:8000/api/v1/recursos
```

---

## Mais Informações

- **Arquitetura:** Veja [ARCHITECTURE.md](ARCHITECTURE.md)

---
