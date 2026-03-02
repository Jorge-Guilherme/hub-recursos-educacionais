# Documentação do Banco de Dados

## Visão Geral

O banco de dados é estruturalmente simples, acredito que em uma possível integração, seria possível também aproveitar as tabelas criadas, ou dependendo da estrutura existente, aproveitar o que já tem. A aplicação utiliza Laravel com Eloquent ORM e implementa recursos como soft deletes para algumas tabelas.

## Diagrama de Relacionamentos

```
┌─────────────┐           ┌──────────────────┐           ┌─────────────┐
│   grupos    │           │  grupo_recurso   │           │  recursos   │
│             │───────────│   (pivot)        │───────────│             │
│ - id        │           │ - grupo_id (FK)  │           │ - id        │
│ - nome      │           │ - recurso_id(FK) │           │ - titulo    │
│ - descricao │           └──────────────────┘           │ - descricao │
└─────────────┘                                          │ - tipo      │
                                                         │ - url       │
                                                         └─────────────┘
                                                               │
                                                               │ 
                                                               │
                                                         ┌──────────────────┐
                                                         │   recurso_tag    │
                                                         │     (pivot)      │
                                                         │ - recurso_id(FK) │
                                                         │ - tag_id (FK)    │
                                                         └──────────────────┘
                                                               │
                                                               │
                                                         ┌─────────────┐
                                                         │    tags     │
                                                         │             │
                                                         │ - id        │
                                                         │ - nome      │
                                                         │ - slug      │
                                                         └─────────────┘
```

## Tabelas Principais do Domínio

### recursos

Tabela central do sistema que armazena os recursos educacionais.

**Colunas:**
- `id` (BIGINT, PK, AUTO_INCREMENT) - Identificador único
- `titulo` (VARCHAR) - Título do recurso
- `descricao` (TEXT) - Descrição detalhada do recurso
- `tipo` (ENUM: 'video', 'pdf', 'link') - Tipo do recurso
- `url` (VARCHAR) - URL ou caminho do recurso
- `created_at` (TIMESTAMP) - Data de criação
- `updated_at` (TIMESTAMP) - Data da última atualização
- `deleted_at` (TIMESTAMP, NULLABLE) - Data de exclusão lógica (soft delete)

**Índices:**
- PRIMARY KEY: `id`
- INDEX: `tipo`

**Características:**
- Implementa **Soft Deletes**: registros podem ser excluídos logicamente
- O campo `tipo` é restrito aos valores: video, pdf, link

---

### tags

Armazena as tags/etiquetas que podem ser associadas aos recursos para organização e busca.

**Colunas:**
- `id` (BIGINT, PK, AUTO_INCREMENT) - Identificador único
- `nome` (VARCHAR, UNIQUE) - Nome da tag
- `slug` (VARCHAR, UNIQUE) - Versão URL-friendly do nome
- `created_at` (TIMESTAMP) - Data de criação
- `updated_at` (TIMESTAMP) - Data da última atualização

**Índices:**
- PRIMARY KEY: `id`
- UNIQUE: `nome`
- UNIQUE: `slug`

**Características:**
- O slug é gerado automaticamente a partir do nome no Model
- Nomes de tags são únicos no sistema

---

### grupos

Organiza recursos em grupos/coleções temáticas.

**Colunas:**
- `id` (BIGINT, PK, AUTO_INCREMENT) - Identificador único
- `nome` (VARCHAR) - Nome do grupo
- `descricao` (TEXT, NULLABLE) - Descrição do grupo
- `created_at` (TIMESTAMP) - Data de criação
- `updated_at` (TIMESTAMP) - Data da última atualização
- `deleted_at` (TIMESTAMP, NULLABLE) - Data de exclusão lógica (soft delete)

**Índices:**
- PRIMARY KEY: `id`

**Características:**
- Implementa **Soft Deletes**: registros podem ser excluídos logicamente
- A descrição é opcional

---

## Tabelas de Relacionamento

### recurso_tag

Tabela pivot que implementa o relacionamento muitos-para-muitos entre recursos e tags.

**Colunas:**
- `id` (BIGINT, PK, AUTO_INCREMENT) - Identificador único
- `recurso_id` (BIGINT, FK) - Referência ao recurso
- `tag_id` (BIGINT, FK) - Referência à tag
- `created_at` (TIMESTAMP) - Data de criação
- `updated_at` (TIMESTAMP) - Data da última atualização

**Índices e Constraints:**
- PRIMARY KEY: `id`
- FOREIGN KEY: `recurso_id` → `recursos.id` (ON DELETE CASCADE)
- FOREIGN KEY: `tag_id` → `tags.id` (ON DELETE CASCADE)
- UNIQUE: `(recurso_id, tag_id)` - Impede duplicação de relacionamento

**Características:**
- Quando um recurso é excluído, todos os seus relacionamentos com tags são removidos automaticamente (cascade)
- Quando uma tag é excluída, todos os seus relacionamentos com recursos são removidos automaticamente (cascade)

---

### grupo_recurso

Tabela pivot que implementa o relacionamento muitos-para-muitos entre grupos e recursos.

**Colunas:**
- `id` (BIGINT, PK, AUTO_INCREMENT) - Identificador único
- `grupo_id` (BIGINT, FK) - Referência ao grupo
- `recurso_id` (BIGINT, FK) - Referência ao recurso
- `created_at` (TIMESTAMP) - Data de criação
- `updated_at` (TIMESTAMP) - Data da última atualização

**Índices e Constraints:**
- PRIMARY KEY: `id`
- FOREIGN KEY: `grupo_id` → `grupos.id` (ON DELETE CASCADE)
- FOREIGN KEY: `recurso_id` → `recursos.id` (ON DELETE CASCADE)
- UNIQUE: `(grupo_id, recurso_id)` - Impede duplicação de relacionamento

**Características:**
- Quando um grupo é excluído, todos os seus relacionamentos com recursos são removidos automaticamente (cascade)
- Quando um recurso é excluído, todos os seus relacionamentos com grupos são removidos automaticamente (cascade)

---

## Tabelas do Sistema Laravel

### users

Armazena os usuários da aplicação.

**Colunas:**
- `id` (BIGINT, PK, AUTO_INCREMENT) - Identificador único
- `name` (VARCHAR) - Nome do usuário
- `email` (VARCHAR, UNIQUE) - E-mail do usuário
- `email_verified_at` (TIMESTAMP, NULLABLE) - Data de verificação do e-mail
- `password` (VARCHAR) - Senha criptografada
- `remember_token` (VARCHAR, NULLABLE) - Token para "lembrar-me"
- `created_at` (TIMESTAMP) - Data de criação
- `updated_at` (TIMESTAMP) - Data da última atualização

**Índices:**
- PRIMARY KEY: `id`
- UNIQUE: `email`

---

### password_reset_tokens

Armazena tokens para reset de senha.

**Colunas:**
- `email` (VARCHAR, PK) - E-mail do usuário
- `token` (VARCHAR) - Token de reset
- `created_at` (TIMESTAMP, NULLABLE) - Data de criação

---

### sessions

Gerencia as sessões dos usuários.

**Colunas:**
- `id` (VARCHAR, PK) - Identificador da sessão
- `user_id` (BIGINT, NULLABLE, FK) - Referência ao usuário
- `ip_address` (VARCHAR) - Endereço IP do usuário
- `user_agent` (TEXT) - Informações do navegador
- `payload` (LONGTEXT) - Dados da sessão
- `last_activity` (INTEGER) - Timestamp da última atividade

**Índices:**
- PRIMARY KEY: `id`
- INDEX: `user_id`
- INDEX: `last_activity`

---

### cache e cache_locks

Tabelas para o sistema de cache do Laravel.

**cache:**
- `key` (VARCHAR, PK) - Chave do cache
- `value` (MEDIUMTEXT) - Valor armazenado
- `expiration` (INTEGER) - Timestamp de expiração

**cache_locks:**
- `key` (VARCHAR, PK) - Chave do lock
- `owner` (VARCHAR) - Proprietário do lock
- `expiration` (INTEGER) - Timestamp de expiração

---

### jobs, job_batches e failed_jobs

Tabelas para o sistema de filas do Laravel.

**jobs** (trabalhos pendentes):
- `id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`

**job_batches** (lotes de trabalhos):
- `id`, `name`, `total_jobs`, `pending_jobs`, `failed_jobs`, `failed_job_ids`, `options`, `cancelled_at`, `created_at`, `finished_at`

**failed_jobs** (trabalhos falhados):
- `id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`

---

## Relacionamentos no Eloquent

### Recurso Model

```php
class Recurso extends Model
{
    // Um recurso pertence a muitas tags
    public function tags(): BelongsToMany
    
    // Um recurso pertence a muitos grupos
    public function grupos(): BelongsToMany
}
```

### Tag Model

```php
class Tag extends Model
{
    // Uma tag pertence a muitos recursos
    public function recursos(): BelongsToMany
}
```

### Grupo Model

```php
class Grupo extends Model
{
    // Um grupo tem muitos recursos
    public function recursos(): BelongsToMany
}
```

---

## Características Especiais

### Soft Deletes

As tabelas `recursos` e `grupos` implementam soft deletes, o que significa que:
- Registros não são permanentemente excluídos do banco de dados
- Uma coluna `deleted_at` é usada para marcar registros como excluídos
- Registros excluídos são automaticamente filtrados das consultas normais
- É possível restaurar registros excluídos ou excluí-los permanentemente

### Cascata de Exclusão

As tabelas pivot (`recurso_tag` e `grupo_recurso`) utilizam `ON DELETE CASCADE`:
- Ao excluir um recurso, todas as suas associações com tags e grupos são automaticamente removidas
- Ao excluir uma tag, todas as suas associações com recursos são automaticamente removidas
- Ao excluir um grupo, todas as suas associações com recursos são automaticamente removidas

### Geração Automática de Slug

O Model `Tag` gera automaticamente um slug a partir do nome:
- Converte o nome para formato URL-friendly
- Ocorre automaticamente ao criar uma nova tag (se o slug não for fornecido)

---

## Considerações de Performance

### Índices Implementados

1. **recursos.tipo** - Otimiza consultas que filtram por tipo de recurso
2. **sessions.user_id** - Otimiza consultas de sessões por usuário
3. **sessions.last_activity** - Otimiza limpeza de sessões antigas
4. **Constraints UNIQUE nas tabelas pivot** - Previnem duplicação e otimizam buscas

### Recomendações

- As tabelas pivot possuem constraints UNIQUE que também funcionam como índices compostos
- Considere adicionar índices em `grupos.nome` e `recursos.titulo` se houver buscas frequentes por nome
- O campo `tipo` já possui índice para filtros de tipo de recurso

---

## Ordem de Execução das Migrations

1. `0001_01_01_000000_create_users_table` - Cria users, password_reset_tokens, sessions
2. `0001_01_01_000001_create_cache_table` - Cria cache e cache_locks
3. `0001_01_01_000002_create_jobs_table` - Cria jobs, job_batches, failed_jobs
4. `2026_02_28_000001_create_recursos_table` - Cria recursos
5. `2026_02_28_000002_create_tags_table` - Cria tags
6. `2026_02_28_000003_create_recurso_tag_table` - Cria tabela pivot recurso_tag
7. `2026_02_28_000004_create_grupos_table` - Cria grupos
8. `2026_02_28_000005_create_grupo_recurso_table` - Cria tabela pivot grupo_recurso

As migrations são executadas em ordem cronológica, garantindo que as tabelas referenciadas por chaves estrangeiras sejam criadas primeiro.
