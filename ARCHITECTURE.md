# Arquitetura do Projeto

## Sobre este Documento

Para inciar o projeto, decidi a arquitetura que iria utilizar, apliquei conceitos de Clean Architecture, pois tenho uma certa familiaridade. Além disso, resolvi aplicar conceitos de SOLID. Aproveitei que recentemente, finalizei o livro do CleanCode, e já tinha implementado esse padrão num projeto anterior. Nesse README, irei destrinchar mais a arquitetura.

## Visão Geral da Arquitetura

O projeto está dividido em duas partes principais que se comunicam via API REST:

```
hub-recursos-educacionais/
├── source/              # Backend (Laravel 11 + PHP 8.3)
│   └── app/
│       ├── Domain/      # Regras de negócio puras
│       ├── Application/ # Casos de uso
│       └── Infrastructure/ # Implementações concretas
│
└── themes/              # Frontend (Angular 17)
    └── src/
        ├── app/
        │   ├── components/  # Componentes standalone
        │   ├── services/    # Serviços de API
        │   └── models/      # Interfaces TypeScript
        └── styles/          # Design system
```

## Clean Architecture

Separaei em camadas ao invés de misturar tudo (controllers, lógica, banco de dados), organizei o código em camadas com responsabilidades bem definidas:


### Camadas do Backend

#### Domain

O Domain contém as regras de negócio puras, completamente independentes de frameworks ou bibliotecas externas.

```
source/app/Domain/
├── Entities/          # Objetos de negócio (ex: Recurso, Grupo)
├── ValueObjects/      # Valores imutáveis (ex: Url, Slug)
└── Contracts/         # Interfaces (ex: RecursoRepositoryInterface)
```

- Domain não pode depender de Laravel, Eloquent ou qualquer framework
- As regras de negócio devem estar aqui (ex: "um recurso deve ter uma URL válida")
- Uso interfaces (Contracts) ao invés de classes concretas
- Value Objects garantem consistência dos dados

**Exemplo prático:**
```php
// Domain/ValueObjects/Url.php
// Garante que sempre temos URLs válidas no sistema
class Url {
    private string $value;
    
    public function __construct(string $url) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('URL inválida');
        }
        $this->value = $url;
    }
    
    public function getValue(): string {
        return $this->value;
    }
}
```

#### Application

Aqui ficam os casos de uso, as ações que o usuário pode fazer no sistema. Cada UseCase representa uma funcionalidade específica.

```
source/app/Application/
├── UseCases/
│   ├── Recurso/
│   │   ├── CreateRecurso.php      # Criar recurso
│   │   ├── UpdateRecurso.php      # Atualizar recurso
│   │   ├── DeleteRecurso.php      # Deletar recurso
│   │   └── FindRecursos.php       # Buscar recursos
│   ├── Grupo/
│   │   └── ...                    # Casos de uso de grupos
│   └── Health/
│       └── GetHealthStatus.php    # Health check
│
├── DTOs/              # Data Transfer Objects
└── Services/          # Serviços de aplicação (ex: GeminiService)
```

- Cada UseCase tem uma única responsabilidade (Single Responsibility)
- UseCases orquestram o fluxo: validar → processar → persistir
- DTOs transferem dados entre camadas sem expor detalhes internos
- Serviços complexos (como integração com IA) ficam aqui

**Exemplo prático:**
```php
// Application/UseCases/Health/GetHealthStatus.php
// Caso de uso simples: verificar saúde do sistema
class GetHealthStatus implements UseCaseInterface {
    public function execute(mixed $input = null): array {
        return [
            'status' => 'healthy',
            'database' => 'connected',
            'gemini_ai' => 'connected',
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
```

#### Infrastructure (Infraestrutura)

Esta camada contém todas as implementações concretas e integrações com frameworks e APIs externas.


```
source/app/Infrastructure/
├── Persistence/
│   ├── Eloquent/           # Models do Eloquent
│   │   ├── RecursoModel.php
│   │   ├── TagModel.php
│   │   └── GrupoModel.php
│   └── Repositories/       # Implementações dos Repositories
│       ├── EloquentRecursoRepository.php
│       ├── EloquentTagRepository.php
│       └── EloquentGrupoRepository.php
│
├── Http/
│   ├── Controllers/        # Controllers da API
│   │   ├── RecursoController.php
│   │   ├── GrupoController.php
│   │   └── HealthController.php
│   ├── Requests/          # Form Requests (validação)
│   └── Middleware/        # Middlewares customizados
│
├── External/              # Integrações externas
│   └── Gemini/
│       ├── GeminiClient.php
│       └── GeminiException.php
│
├── Factories/             # Factories para criação de objetos
│   └── RecursoFactory.php
│
└── Providers/             # Service Providers do Laravel
    └── DomainServiceProvider.php
```

- Infrastructure implementa as interfaces definidas no Domain
- Aqui uso Laravel, Eloquent, APIs externas (Google Gemini)
- Controllers são "finos", apenas recebem request e chamam UseCases (essa parte é boa para implementar conceitos de segurança)
- Repositories fazem a ponte entre Domain e banco de dados
- Posso trocar implementações sem mexer no Domain

**Exemplo prático:**
```php
// Infrastructure/Persistence/Repositories/EloquentRecursoRepository.php
// Implementa a interface do Domain usando Eloquent
class EloquentRecursoRepository implements RecursoRepositoryInterface {
    protected RecursoModel $model;
    
    public function find(int $id): ?Recurso {
        $eloquentModel = $this->model->find($id);
        
        if (!$eloquentModel) {
            return null;
        }
        
        // Converte Model do Eloquent para Entity do Domain
        return RecursoFactory::fromEloquent($eloquentModel);
    }
}
```

---

## Fluxo de Dados

As camadas externas dependem das internas:

```
┌─────────────────────────────────────────────────────────┐
│                      HTTP Request                       │
│              (GET /api/v1/recursos/1)                   │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
         ┌───────────────────────────────┐
         │  Controller (Infrastructure)  │
         │  - Recebe request             │
         │  - Valida entrada             │
         │  - Chama UseCase              │
         └───────────┬───────────────────┘
                     │
                     ▼
         ┌───────────────────────────────┐
         │    UseCase (Application)      │
         │  - Orquestra lógica           │
         │  - Usa Repository Interface   │
         └───────────┬───────────────────┘
                     │
                     ▼
         ┌───────────────────────────────┐
         │ Repository Interface (Domain) │
         │  - Define contrato            │
         └───────────┬───────────────────┘
                     │
                     ▼
         ┌───────────────────────────────┐
         │Repository Impl (Infrastructure)│
         │  - Usa Eloquent               │
         │  - Acessa banco               │
         └───────────┬───────────────────┘
                     │
                     ▼
         ┌───────────────────────────────┐
         │        Database (PostgreSQL)   │
         └───────────────────────────────┘
```

**Exemplo real do fluxo:**

1. **Request chega:** `GET /api/v1/recursos/1`
2. **Controller recebe:** `RecursoController@show($id)`
3. **Controller chama UseCase:** `$this->findRecursoUseCase->execute($id)`
4. **UseCase usa Repository:** `$this->repository->find($id)`
5. **Repository busca no banco:** `RecursoModel::find($id)`
6. **Repository retorna Entity:** Converte Model → Domain Entity
7. **UseCase processa:** Aplica regras de negócio se necessário
8. **Controller formata resposta:** Retorna JSON

---

## Frontend

No frontend, apliquei princípios similares de separação de responsabilidades:

```
themes/src/app/
├── components/              # Componentes standalone
│   ├── recurso-list/        # Lista de recursos
│   ├── recurso-form/        # Formulário de recurso
│   ├── recurso-detail/      # Detalhes do recurso
│   ├── grupo-list/          # Lista de grupos
│   ├── grupo-form/          # Formulário de grupo
│   └── grupo-detail/        # Detalhes do grupo
│
├── services/                # Serviços (comunicação com API)
│   ├── recurso.service.ts   # CRUD de recursos
│   ├── grupo.service.ts     # CRUD de grupos
│   └── gemini.service.ts    # Integração com IA
│
└── models/                  # Interfaces TypeScript
    ├── recurso.model.ts
    ├── grupo.model.ts
    └── tag.model.ts
```

- **Componentes** = responsáveis apenas pela UI e interação
- **Services** = lógica de comunicação com API (similar aos UseCases)
- **Models** = tipagem forte com TypeScript (similar às Entities)
- **Standalone components** = mais modernos, dispensam NgModule

---

## Os princípios SOLID que usei

Durante o desenvolvimento tentei aplicá-los consistentemente. Aqui está o que cada um significa e como usei no projeto:

### S - Single Responsibility

Cada classe deve ter apenas uma razão para mudar.

```php
// Faz apenas uma coisa: verificar saúde do sistema
class GetHealthStatus implements UseCaseInterface {
    public function execute(mixed $input = null): array {
        return [
            'status' => 'healthy',
            'database' => 'connected'
        ];
    }
}

// Faz muitas coisas ao mesmo tempo
class RecursoController {
    public function store() {
        // valida dados + salva + notifica + loga + envia email
        // Muitas responsabilidades!
    }
}
```

**Como apliquei:**
- Controllers apenas recebem requests e delegam para UseCases
- UseCases focam em um único caso de uso
- Repositories apenas acessam dados
- Services fazem uma coisa específica (ex: GeminiService só integra com IA)

---

### O - Open/Closed

Classes devem estar abertas para extensão, mas fechadas para modificação.

```php
// Posso criar novos repositories sem modificar o código existente
interface RepositoryInterface {
    public function find(int $id);
    public function all();
}

class EloquentRecursoRepository implements RepositoryInterface {
    public function find(int $id) { /* implementação com Eloquent */ }
}

class CachedRecursoRepository implements RepositoryInterface {
    public function find(int $id) { /* implementação com cache */ }
}

// Posso criar MongoRecursoRepository sem alterar código existente!
```

**Como apliquei:**
- Uso interfaces para definir contratos
- Posso trocar Eloquent por outro ORM sem quebrar nada
- Factories permitem criar objetos de formas diferentes

---

### L - Liskov Substitution 

Subclasses devem poder **substituir suas classes base** sem quebrar o sistema.

```php
// Qualquer implementação de RepositoryInterface funciona
class RecursoService {
    private RepositoryInterface $repository;
    
    public function __construct(RepositoryInterface $repository) {
        // Pode receber EloquentRepository, CachedRepository, etc
        // Todos funcionam da mesma forma!
        $this->repository = $repository;
    }
    
    public function buscarTodos() {
        return $this->repository->all(); // Funciona com qualquer implementação
    }
}
```

**Como apliquei:**
- Todas as implementações de Repository respeitam o mesmo contrato
- Posso injetar qualquer implementação nos UseCases
- Testes usam Mock Repositories que substituem os reais

---

### I - Interface Segregation

Melhor ter interfaces específicas do que uma interface genérica gigante.

```php
// Interfaces específicas para cada necessidade
interface ReadableRepositoryInterface {
    public function find(int $id);
    public function all();
}

interface WritableRepositoryInterface {
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}

// Um serviço que só lê dados não precisa saber sobre write
class RecursoReader {
    private ReadableRepositoryInterface $repository;
    
    public function __construct(ReadableRepositoryInterface $repository) {
        $this->repository = $repository; // Só pode ler!
    }
}
```

**Como apliquei:**
- Criei interfaces focadas (ex: `RecursoRepositoryInterface` vs `RepositoryInterface`)
- UseCases recebem apenas as interfaces que realmente precisam
- Evitei "God Interfaces" com 50 métodos

---

### D - Dependency Inversion

Depender de abstrações (interfaces), não de implementações concretas.

```php
// Depende de interface (abstração)
class CreateRecurso implements UseCaseInterface {
    private RecursoRepositoryInterface $repository; // Interface!
    
    public function __construct(RecursoRepositoryInterface $repository) {
        $this->repository = $repository;
    }
}

// Depende de implementação concreta
class CreateRecurso {
    private EloquentRecursoRepository $repository; // Concreto!
    
    public function __construct() {
        $this->repository = new EloquentRecursoRepository(); // Acoplado!
    }
}
```

**Como apliquei:**
- UseCases dependem de interfaces, não de repositories concretos
- Laravel Service Container resolve as dependências automaticamente
- Posso trocar implementações apenas mudando o binding no Service Provider

---

## Padrões de Design Utilizados

Além de SOLID, apliquei alguns padrões de design clássicos:

### Repository Pattern

Abstrair o acesso a dados, permitindo trocar o banco sem mexer na lógica.

```php
// Interface define o contrato (Domain)
interface RecursoRepositoryInterface {
    public function find(int $id): ?Recurso;
    public function all(): array;
    public function create(Recurso $recurso): Recurso;
}

// Implementação usa Eloquent (Infrastructure)
class EloquentRecursoRepository implements RecursoRepositoryInterface {
    public function find(int $id): ?Recurso {
        $model = RecursoModel::find($id);
        return $model ? RecursoFactory::fromEloquent($model) : null;
    }
}
```

### Factory Pattern

Centralizar a criação de objetos complexos.

```php
class RecursoFactory {
    // Cria entidade a partir do Eloquent Model
    public static function fromEloquent(RecursoModel $model): Recurso {
        return new Recurso(
            id: $model->id,
            titulo: $model->titulo,
            descricao: $model->descricao,
            tipo: TipoRecurso::from($model->tipo),
            url: new Url($model->url),
            tags: $model->tags->map(fn($tag) => TagFactory::fromEloquent($tag))
        );
    }
    
    // Cria entidade a partir de array
    public static function fromArray(array $data): Recurso {
        // ... lógica de criação
    }
}
```

### DTO (Data Transfer Object)

Transferir dados entre camadas sem expor detalhes internos.

```php
class CreateRecursoDTO {
    public function __construct(
        public readonly string $titulo,
        public readonly string $descricao,
        public readonly string $tipo,
        public readonly string $url,
        public readonly array $tags = []
    ) {}
    
    public static function fromRequest(Request $request): self {
        return new self(
            titulo: $request->input('titulo'),
            descricao: $request->input('descricao'),
            tipo: $request->input('tipo'),
            url: $request->input('url'),
            tags: $request->input('tags', [])
        );
    }
}
```

### Service Pattern

Encapsular lógica de negócio complexa que não pertence a uma entidade específica.

```php
class GeminiService {
    private GeminiClient $client;
    
    public function gerarDescricao(string $titulo, string $tipo, ?string $url): string {
        $prompt = "Gere uma descrição pedagógica para: $titulo ($tipo)";
        
        return $this->client->generateText($prompt, [
            'maxOutputTokens' => 1024,
            'temperature' => 0.7
        ]);
    }
}
```

---

## Dependency Injection

O Laravel faz isso automaticamente.

```php
// 1. Defino a interface no Domain
interface RecursoRepositoryInterface { }

// 2. Crio a implementação no Infrastructure
class EloquentRecursoRepository implements RecursoRepositoryInterface { }

// 3. Registro no Service Provider
class DomainServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(
            RecursoRepositoryInterface::class,  // Interface
            EloquentRecursoRepository::class    // Implementação
        );
    }
}

// 4. Laravel injeta automaticamente!
class CreateRecurso {
    public function __construct(
        private RecursoRepositoryInterface $repository  // Laravel resolve sozinho!
    ) {}
}
```

---

## Testabilidade

A arquitetura facilita muito os testes unitários:

```php
// Mock do repository
$mockRepository = Mockery::mock(RecursoRepositoryInterface::class);
$mockRepository->shouldReceive('find')
    ->with(1)
    ->andReturn($recursoEsperado);

// Injeto o mock no UseCase
$useCase = new FindRecurso($mockRepository);

// Testo apenas a lógica do UseCase
$resultado = $useCase->execute(1);

// Assert
$this->assertEquals($recursoEsperado, $resultado);
```

**Vantagens:**
- Não preciso de banco de dados para testar lógica
- Cada camada pode ser testada isoladamente
- Mocks substituem dependências complexas

---

## Boas Práticas que Segui

Durante o desenvolvimento, estabeleci algumas regras para manter a consistência:

1. **Domain é o núcleo:** Não pode depender de nada externo
2. **Application orquestra:** Não implementa regras, apenas coordena
3. **Infrastructure adapta:** Implementa interfaces e se comunica com o mundo externo
4. **Controllers são finos:** Apenas recebem request e chamam UseCases
5. **Use interfaces:** Sempre dependa de abstrações
6. **Value Objects validam:** Garantem consistência dos dados
7. **DTOs transferem:** Dados entre camadas de forma segura
8. **Factories criam:** Objetos complexos de forma centralizada
9. **Repositories abstraem:** O acesso a dados
10. **UseCases representam:** Ações reais do usuário

---

## Diagrama da Arquitetura Completa

```
┌─────────────────────────────────────────────────────────────┐
│                         FRONTEND                            │
│                      (Angular 17)                           │
│                                                             │
│  Components → Services → HTTP Client                        │
└────────────────────────┬────────────────────────────────────┘
                         │
                         │ REST API (JSON)
                         │
┌────────────────────────▼────────────────────────────────────┐
│                      INFRASTRUCTURE                         │
│                     (Laravel - External)                    │
│                                                             │
│  Controllers → Middleware → Validation                      │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                      APPLICATION                            │
│                     (Use Cases Layer)                       │
│                                                             │
│  UseCases → Services (Gemini) → DTOs                        │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                        DOMAIN                               │
│                  (Business Rules Core)                      │
│                                                             │
│  Entities → Value Objects → Contracts (Interfaces)          │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                     INFRASTRUCTURE                          │
│                  (Laravel - Persistence)                    │
│                                                             │
│  Repositories → Eloquent → Factories                        │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                   DATABASE (PostgreSQL)                     │
│  recursos | tags | recurso_tag | grupos | grupo_recurso    │
└─────────────────────────────────────────────────────────────┘
```


## Recursos que Usei como fonte

- [Clean Architecture - Uncle Bob](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)
- [SOLID Principles Explicado](https://en.wikipedia.org/wiki/SOLID)
- [Laravel Architecture Best Practices](https://laravel.com/docs/11.x/structure)
- [Domain-Driven Design - Martin Fowler](https://martinfowler.com/bliki/DomainDrivenDesign.html)
- [Repository Pattern no Laravel](https://medium.com/@cesiztel/repository-pattern-in-laravel)

---
