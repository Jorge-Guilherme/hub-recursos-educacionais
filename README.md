# Hub Recursos Educacionais

Plataforma de gestão de recursos educacionais desenvolvida com Laravel (source) e Angular (themes).

## Arquitetura

Este projeto segue os princípios de **Clean Architecture** e **SOLID**:

### Estrutura de Camadas

```
source/
├── Domain/              # Regras de negócio (Entities, Value Objects)
├── Application/         # Casos de uso, DTOs, Services
├── Infrastructure/      # Implementações (Repositories, HTTP, External)
└── app/                 # Laravel framework

themes/
└── Angular application
```

### Princípios SOLID

- **S**ingle Responsibility: Cada classe tem uma única responsabilidade
- **O**pen/Closed: Aberto para extensão, fechado para modificação
- **L**iskov Substitution: Substituição transparente de abstrações
- **I**nterface Segregation: Interfaces específicas e coesas
- **D**ependency Inversion: Dependência de abstrações, não implementações

## Tecnologias

- **Source**: Laravel 11.x + PHP 8.3
- **Themes**: Angular 17.x
- **Banco de Dados**: PostgreSQL 16
- **Containerização**: Docker & Docker Compose
- **CI/CD**: GitHub Actions
- **Linting**: Laravel Pint + PHP_CodeSniffer

## Pré-requisitos

- Docker >= 24.0
- Docker Compose >= 2.20
- Git

## Setup

### Rápido

```bash
chmod +x setup.sh && ./setup.sh
```

### Manual

```bash
# Source (Laravel)
cd source
composer create-project laravel/laravel .
composer install
cp .env.example .env
php artisan key:generate

# Themes (Angular)
cd ../themes
npm install -g @angular/cli
ng new . --routing --style=scss --skip-git
npm install

# Iniciar
cd ..
docker-compose up -d
```

## Acessar

- **Themes (Angular)**: http://localhost:4200
- **Source API**: http://localhost:8000
- **API Health**: http://localhost:8000/api/health
- **PostgreSQL**: localhost:5432

## Integração com Gemini AI

O projeto inclui integração com Google Gemini AI para geração automática de descrições de recursos educacionais.

### Configuração

1. Obtenha uma chave de API do Gemini:
   - Acesse [Google AI Studio](https://makersuite.google.com/app/apikey)
   - Crie uma nova chave de API

2. Configure a variável de ambiente no backend:
```bash
cd source
echo "GEMINI_API_KEY=sua-chave-aqui" >> .env
```

3. Reinicie o container do backend:
```bash
docker-compose restart backend
```

### Uso

### Endpoint

```http
POST /api/v1/recursos/gerar-descricao
Content-Type: application/json

{
  "titulo": "Tutorial de Laravel",
  "tipo": "video",
  "url": "https://youtube.com/..."
}
```

Resposta:
```json
{
  "descricao": "Descrição gerada pela IA..."
}
```

## Testes e Qualidade

### Linting

```bash
# Pint
docker-compose exec backend ./vendor/bin/pint

# PHPCS
docker-compose exec backend ./vendor/bin/phpcs
```

### Testes

```bash
docker-compose exec backend php artisan test
```

## GitHub Actions

Workflows automáticos:
- **Laravel Pint**: Verifica formatação
- **PHP_CodeSniffer**: Verifica padrões PSR-12

## Estrutura

```
hub-recursos-educacionais/
├── source/              # Laravel + Clean Architecture
│   ├── Domain/
│   ├── Application/
│   ├── Infrastructure/
│   └── app/
├── themes/              # Angular
├── .github/workflows/   # CI/CD
└── docker-compose.yml
```

## Comandos Docker

```bash
make up            # Iniciar
make down          # Parar
make logs          # Ver logs
make pint          # Executar linter
make test          # Executar testes
```

## Desenvolvimento

### Source (Laravel)

```bash
# Controller
docker-compose exec backend php artisan make:controller NomeController

# Model
docker-compose exec backend php artisan make:model Nome -m

# Migration
docker-compose exec backend php artisan make:migration create_table

# UseCase (Clean Architecture)
# Criar em: source/Application/UseCases/
```

### Themes (Angular)

```bash
# Componente
docker-compose exec frontend ng g component components/nome

# Serviço
docker-compose exec frontend ng g service services/nome

# Módulo
docker-compose exec frontend ng g module modules/nome --routing
```

Siga os princípios **SOLID** e **Clean Architecture**.
