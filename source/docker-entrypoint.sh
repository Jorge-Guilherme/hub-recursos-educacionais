#!/bin/bash
set -e

echo "🚀 Iniciando aplicação Laravel..."

# Instalar dependências se o autoload não existir
if [ ! -f "vendor/autoload.php" ]; then
    echo "📦 Instalando dependências do Composer..."
    composer install --no-interaction --prefer-dist
    composer dump-autoload
else
    echo "✅ Dependências já instaladas"
fi

# Aguardar PostgreSQL
until pg_isready -h postgres -U hub_user; do
    echo "⏳ Aguardando PostgreSQL..."
    sleep 2
done

# Gerar APP_KEY se não existir
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    echo "🔑 Gerando APP_KEY..."
    php artisan key:generate --no-interaction
fi

# Rodar migrations
echo "🗄️  Rodando migrations..."
php artisan migrate --force --no-interaction || echo "⚠️  Erro ao rodar migrations"

# Limpar cache
echo "🧹 Limpando cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear

echo "✅ Aplicação pronta!"

# Iniciar servidor
exec php artisan serve --host=0.0.0.0 --port=8000
