# Hub de Recursos Educacionais

Sistema fullstack para gerenciamento de recursos educacionais (vídeos, PDFs, links) com integração de IA para geração automática de descrições e tags.

**Desenvolvido como desafio técnico para vaga de estágio**


**Obs 1:** Como consegui finalizar o projeto com uma certa agilidade, achei interessante fazer o deploy. Utilizei o Oracle Cloud, pois oferecia uma versão gratuita de uma VM. 

**Obs 2:** As funcionalidades extras foram criadas pensando numa futura integração com outros módulos. Pensei na plataforma como uma biblioteca digital, onde possamos ter um estilo de "Google Classroom". Isso nos permite adicionar conexão com turmas, disciplinas, etc.

---

## Documentação

- **[API Documentation](API_DOCUMENTATION.md)** - Documentação completa da API REST com todos os endpoints.
- **[Architecture](ARCHITECTURE.md)** - Explicação detalhada da arquitetura geral, Clean Architecture e SOLID.
- **[DATABASE](DATABASE.md)** - Explicação detalhada do Banco de Dados,

---

## Como Executar

### Pré-requisitos

- Docker >= 24.0
- Docker Compose >= 2.20

### Passos

1. **Clone o repositório:**
```bash
git clone https://github.com/Jorge-Guilherme/hub-recursos-educacionais.git
cd hub-recursos-educacionais
```

2. **Configure as variáveis de ambiente:**
```bash
cd source
cp .env.example .env
cd ..
```

3. **Suba os containers:**
```bash
docker-compose up -d
```

4. **Execute as migrations:**
```bash
docker-compose exec backend php artisan migrate
```

5. **Acesse a aplicação:**
- **Frontend:** http://localhost:4200
- **API:** http://localhost:8000/api/v1
- **Health Check:** http://localhost:8000/api/v1/health

---

## Tecnologias Utilizadas

### Backend
- **Laravel 11** - Framework PHP
- **PHP 8.3** - Linguagem
- **PostgreSQL 16** - Banco de dados relacional
- **Google Gemini AI** - Geração de descrições e tags
- **Clean Architecture** - Separação em camadas (Domain, Application, Infrastructure)
- **SOLID** - Princípios de design

### Frontend
- **Angular 17** - Framework frontend
- **TypeScript** - Linguagem
- **Standalone Components** - Arquitetura moderna do Angular
- **Lucide Icons** - Biblioteca de ícones
- **Reactive Forms** - Gerenciamento de formulários

### DevOps
- **Docker & Docker Compose** - Containerização
- **Nginx** - Servidor web
- **GitHub Actions** - CI/CD

---

## Funcionalidades

- CRUD completo de recursos educacionais -> Feature obrigatória
- Organização em grupos temáticos -> Feature extra
- Geração de descrições com IA (Google Gemini) -> Feature obrigatória
- Geração de tags com IA -> Feature extra
- Busca e filtros avançados -> Feature extra
- Paginação -> Feature obrigatória
- CI/CD -> Feature Bônus
- Observabilidade -> Feature Bônus

---

## Estrutura do Projeto

```
hub-recursos-educacionais/
├── source/              # Backend (Laravel + Clean Architecture)
│   ├── app/
│   │   ├── Domain/          # Regras de negócio puras
│   │   ├── Application/     # Casos de uso
│   │   └── Infrastructure/  # Implementações concretas
│   └── database/
│
├── themes/              # Frontend (Angular)
│   └── src/
│       ├── app/
│       │   ├── components/  # Componentes standalone
│       │   ├── services/    # Serviços de API
│       │   └── models/      # Interfaces TypeScript
│       └── styles/          # Design system
│
└── docker-compose.yml   # Orquestração dos containers
```

---

## Vídeo do projeto funcionando

Pode colocar no 2x, mas eu peço gentilmente que assista.

https://www.youtube.com/watch?v=l5qQv2U27uA
