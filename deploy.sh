#!/bin/bash

set -e

echo "🚀 Iniciando deploy..."
echo ""

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Branch check
BRANCH=$(git branch --show-current)
if [ "$BRANCH" != "main" ]; then
    echo -e "${RED}❌ Você deve estar na branch 'main' para fazer deploy${NC}"
    echo -e "${YELLOW}Branch atual: $BRANCH${NC}"
    exit 1
fi

if [[ -n $(git status -s) ]]; then
    echo -e "${RED}❌ Existem mudanças não commitadas${NC}"
    echo -e "${YELLOW}Commit ou descarte as mudanças antes de fazer deploy${NC}"
    git status -s
    exit 1
fi

echo -e "${YELLOW}📥 Pull...${NC}"
git pull origin main

echo -e "${YELLOW}🐳 Rebuild containers...${NC}"
docker-compose -f docker-compose.prod.yml down
docker-compose -f docker-compose.prod.yml up -d --build

echo -e "${YELLOW}⏳ Aguardando...${NC}"
sleep 10

echo -e "${YELLOW}📦 Instalando deps...${NC}"
docker-compose -f docker-compose.prod.yml exec -T backend composer install --no-dev --optimize-autoloader

echo -e "${YELLOW}⚡ Otimizando...${NC}"
docker-compose -f docker-compose.prod.yml exec -T backend php artisan config:cache
docker-compose -f docker-compose.prod.yml exec -T backend php artisan route:cache
docker-compose -f docker-compose.prod.yml exec -T backend php artisan view:cache

echo -e "${YELLOW}📊 Migrations...${NC}"
docker-compose -f docker-compose.prod.yml exec -T backend php artisan migrate --force

echo -e "${YELLOW}🎨 Build themes...${NC}"
docker-compose -f docker-compose.prod.yml exec -T frontend npm run build --prod

echo -e "${YELLOW}🏥 Health check...${NC}"
sleep 5

HEALTH_CHECK=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/api/health || echo "000")

if [ "$HEALTH_CHECK" = "200" ]; then
    echo -e "${GREEN}✅ Deploy concluído com sucesso!${NC}"
    echo ""
    echo -e "🌐 Aplicação disponível em: ${GREEN}http://localhost${NC}"
else
    echo -e "${RED}❌ Erro no health check. Status: $HEALTH_CHECK${NC}"
    echo -e "${YELLOW}Verifique os logs: docker-compose -f docker-compose.prod.yml logs${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}🎉 Deploy finalizado!${NC}"
echo ""
echo "Comandos úteis:"
echo "  docker-compose -f docker-compose.prod.yml logs -f    # Ver logs"
echo "  docker-compose -f docker-compose.prod.yml ps          # Status"
echo "  docker-compose -f docker-compose.prod.yml down        # Parar"
