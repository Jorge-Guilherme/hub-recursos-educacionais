#!/bin/bash

set -e

echo "🚀 Iniciando setup do Hub Recursos Educacionais..."
echo ""

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Verificar Docker
if ! command -v docker &> /dev/null; then
    echo -e "${RED}❌ Docker não está instalado. Por favor, instale o Docker primeiro.${NC}"
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}❌ Docker Compose não está instalado. Por favor, instale o Docker Compose primeiro.${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Docker e Docker Compose encontrados${NC}"
echo ""

if [ ! -d "source" ]; then
    echo -e "${YELLOW}📁 Criando estrutura source...${NC}"
    mkdir -p source
fi

if [ ! -d "themes" ]; then
    echo -e "${YELLOW}📁 Criando estrutura themes...${NC}"
    mkdir -p themes
fi

echo -e "${YELLOW}📦 Configurando Source (Laravel)...${NC}"
cd source

if [ ! -f "composer.json" ]; then
    echo -e "${YELLOW}🎯 Laravel não encontrado. Criando novo projeto...${NC}"
    docker run --rm -v $(pwd):/app composer create-project laravel/laravel .
    echo -e "${GREEN}✅ Laravel criado${NC}"
else
    echo -e "${YELLOW}📦 Instalando dependências...${NC}"
    docker run --rm -v $(pwd):/app composer install
    echo -e "${GREEN}✅ Dependências instaladas${NC}"
fi

# .env
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo -e "${GREEN}✅ Arquivo .env criado${NC}"
    fi
fi

cd ..

echo ""
echo -e "${YELLOW}📦 Configurando Themes (Angular)...${NC}"
cd themes

if [ ! -f "package.json" ]; then
    echo -e "${RED}⚠️  Angular não encontrado.${NC}"
    echo -e "${YELLOW}Execute manualmente: cd frontend && npx @angular/cli new . --routing --style=scss --skip-git${NC}"
else
    echo -e "${YELLOW}📦 package.json encontrado. Frontend parece estar configurado.${NC}"
fi

cd ..

echo ""
echo -e "${YELLOW}🐳 Iniciando containers...${NC}"
docker-compose up -d --build

echo ""
echo -e "${GREEN}✅ Containers iniciados!${NC}"
echo ""

echo -e "${YELLOW}⏳ Aguardando PostgreSQL...${NC}"
sleep 10

echo -e "${YELLOW}🔑 Gerando chave do Laravel...${NC}"
docker-compose exec -T backend php artisan key:generate

# Executar migrations
echo -e "${YELLOW}📊 Executando migrations...${NC}"
docker-compose exec -T backend php artisan migrate --force

echo ""
echo -e "${GREEN}✅ Setup concluído com sucesso!${NC}"
echo ""
echo "📝 URLs importantes:"
echo -e "   Frontend (Angular): ${GREEN}http://localhost:4200${NC}"
echo -e "   Backend (Laravel):  ${GREEN}http://localhost:8000${NC}"
echo -e "   PostgreSQL:         ${GREEN}localhost:5432${NC}"
echo ""
echo "🛠️  Comandos úteis:"
echo "   make help          - Ver todos os comandos disponíveis"
echo "   make logs          - Ver logs dos containers"
echo "   make backend-shell - Acessar shell do backend"
echo "   make pint          - Executar linter"
echo ""
echo -e "${GREEN}🎉 Bom desenvolvimento!${NC}"
