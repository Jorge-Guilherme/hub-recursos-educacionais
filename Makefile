.PHONY: help up down build restart logs backend-shell frontend-shell db-migrate db-seed pint phpcs test

help: ## Exibir ajuda
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}'

up: ## Iniciar todos os containers
	docker-compose up -d

down: ## Parar e remover todos os containers
	docker-compose down

build: ## Construir/reconstruir containers
	docker-compose up -d --build

restart: ## Reiniciar todos os containers
	docker-compose restart

logs: ## Ver logs de todos os containers
	docker-compose logs -f

backend-shell: ## Acessar shell do backend
	docker-compose exec backend bash

frontend-shell: ## Acessar shell do frontend
	docker-compose exec frontend sh

db-migrate: ## Executar migrations
	docker-compose exec backend php artisan migrate

db-seed: ## Executar seeders
	docker-compose exec backend php artisan db:seed

db-fresh: ## Recriar banco de dados com seeders
	docker-compose exec backend php artisan migrate:fresh --seed

pint: ## Executar Laravel Pint (corrige formatação)
	docker-compose exec backend ./vendor/bin/pint

pint-test: ## Verificar formatação com Pint (sem corrigir)
	docker-compose exec backend ./vendor/bin/pint --test

phpcs: ## Executar PHP CodeSniffer
	docker-compose exec backend ./vendor/bin/phpcs

phpcbf: ## Auto-fix PHPCBF
	docker-compose exec backend ./vendor/bin/phpcbf

test: ## Executar testes
	docker-compose exec backend php artisan test

cache-clear: ## Limpar cache
	docker-compose exec backend php artisan cache:clear
	docker-compose exec backend php artisan config:clear
	docker-compose exec backend php artisan route:clear
	docker-compose exec backend php artisan view:clear

install: ## Instalar dependências (primeira vez)
	@echo "Instalando dependências do backend..."
	cd backend && composer install
	@echo "Instalando dependências do frontend..."
	cd frontend && npm install
	@echo "Configurando ambiente..."
	cp backend/.env.example backend/.env
	@echo "Pronto! Execute 'make up' para iniciar os containers."
