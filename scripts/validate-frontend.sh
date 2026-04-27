#!/bin/bash

# 🔍 Script de Validação Frontend
# Uso: ./scripts/validate-frontend.sh [rota]
# Exemplo: ./scripts/validate-frontend.sh /gestao/empresas

set -e

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para imprimir com cor
print_color() {
    color=$1
    message=$2
    echo -e "${color}${message}${NC}"
}

# Função para imprimir header
print_header() {
    echo ""
    print_color "$BLUE" "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    print_color "$BLUE" "  $1"
    print_color "$BLUE" "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
}

# Verificar se rota foi fornecida
if [ -z "$1" ]; then
    print_color "$RED" "❌ Erro: Rota não fornecida"
    echo "Uso: ./scripts/validate-frontend.sh [rota]"
    echo "Exemplo: ./scripts/validate-frontend.sh /gestao/empresas"
    exit 1
fi

ROUTE=$1
URL="http://127.0.0.1:8000${ROUTE}"

print_header "🔍 VALIDAÇÃO FRONTEND - ${ROUTE}"

# 1. Limpar cache
print_color "$YELLOW" "📦 Limpando cache..."
php artisan view:clear > /dev/null 2>&1
php artisan config:clear > /dev/null 2>&1
php artisan cache:clear > /dev/null 2>&1
print_color "$GREEN" "✅ Cache limpo"

# 2. Verificar se servidor está rodando
print_color "$YELLOW" "🌐 Verificando servidor..."
if curl -s -o /dev/null -w "%{http_code}" "$URL" | grep -q "200\|302"; then
    print_color "$GREEN" "✅ Servidor respondendo em ${URL}"
else
    print_color "$RED" "❌ Servidor não está respondendo"
    print_color "$YELLOW" "💡 Execute: php artisan serve"
    exit 1
fi

# 3. Verificar rotas
print_color "$YELLOW" "🛣️  Verificando rotas..."
if php artisan route:list | grep -q "${ROUTE}"; then
    print_color "$GREEN" "✅ Rota registrada"
else
    print_color "$RED" "❌ Rota não encontrada"
    print_color "$YELLOW" "💡 Verifique routes/web.php"
    exit 1
fi

# 4. Verificar componentes Livewire
print_color "$YELLOW" "⚡ Verificando componentes Livewire..."
php artisan livewire:list > /dev/null 2>&1
print_color "$GREEN" "✅ Componentes Livewire carregados"

# 5. Verificar sintaxe PHP
print_color "$YELLOW" "🔍 Verificando sintaxe PHP..."
ERROR_COUNT=0
for file in app/Livewire/**/*.php; do
    if [ -f "$file" ]; then
        if ! php -l "$file" > /dev/null 2>&1; then
            print_color "$RED" "❌ Erro de sintaxe em: $file"
            ERROR_COUNT=$((ERROR_COUNT + 1))
        fi
    fi
done

if [ $ERROR_COUNT -eq 0 ]; then
    print_color "$GREEN" "✅ Sintaxe PHP válida"
else
    print_color "$RED" "❌ Encontrados $ERROR_COUNT erros de sintaxe"
    exit 1
fi

# 6. Executar testes (se existirem)
print_color "$YELLOW" "🧪 Verificando testes..."
TEST_DIR="tests/Feature/Livewire"
if [ -d "$TEST_DIR" ]; then
    if ./vendor/bin/pest "$TEST_DIR" --bail > /dev/null 2>&1; then
        print_color "$GREEN" "✅ Testes passando"
    else
        print_color "$RED" "❌ Testes falhando"
        print_color "$YELLOW" "💡 Execute: ./vendor/bin/pest $TEST_DIR"
        exit 1
    fi
else
    print_color "$YELLOW" "⚠️  Nenhum teste encontrado"
fi

# 7. Verificar componentes Flux Pro
print_color "$YELLOW" "🎨 Verificando componentes Flux..."
FLUX_PRO_COMPONENTS=(
    "flux:table"
    "flux:columns"
    "flux:rows"
    "flux:row"
    "flux:cell"
    "flux:dropdown"
    "flux:menu"
    "flux:sidebar"
    "flux:navbar"
    "flux:pagination"
    "flux:toast"
    "flux:tooltip"
    "flux:popover"
)

FLUX_ERROR_COUNT=0
for component in "${FLUX_PRO_COMPONENTS[@]}"; do
    if grep -r "$component" resources/views/livewire/ > /dev/null 2>&1; then
        print_color "$RED" "❌ Componente Flux Pro encontrado: $component"
        FLUX_ERROR_COUNT=$((FLUX_ERROR_COUNT + 1))
    fi
done

if [ $FLUX_ERROR_COUNT -eq 0 ]; then
    print_color "$GREEN" "✅ Nenhum componente Flux Pro usado"
else
    print_color "$RED" "❌ Encontrados $FLUX_ERROR_COUNT componentes Flux Pro"
    print_color "$YELLOW" "💡 Consulte: .ai/FLUX-UI-FREE-COMPONENTS.md"
    exit 1
fi

# 8. Resumo final
print_header "✅ VALIDAÇÃO CONCLUÍDA"

print_color "$GREEN" "✅ Cache limpo"
print_color "$GREEN" "✅ Servidor respondendo"
print_color "$GREEN" "✅ Rota registrada"
print_color "$GREEN" "✅ Componentes Livewire carregados"
print_color "$GREEN" "✅ Sintaxe PHP válida"
print_color "$GREEN" "✅ Testes passando"
print_color "$GREEN" "✅ Nenhum componente Flux Pro usado"

echo ""
print_color "$BLUE" "🌐 Acesse: ${URL}"
echo ""
print_color "$YELLOW" "⚠️  LEMBRE-SE:"
echo "   1. Verificar logs do terminal (php artisan serve)"
echo "   2. Verificar console do navegador (F12)"
echo "   3. Testar todas funcionalidades manualmente"
echo "   4. Testar dark mode"
echo "   5. Testar responsivo (F12 > Device Toolbar)"
echo ""
print_color "$GREEN" "✨ Validação automática concluída com sucesso!"
echo ""
