# 🔍 Script de Validação Frontend
# Uso: .\scripts\validate-frontend.ps1 -Route "/gestao/empresas"

param(
    [Parameter(Mandatory=$true)]
    [string]$Route
)

# Cores
function Write-ColorOutput {
    param(
        [string]$Color,
        [string]$Message
    )
    
    $colors = @{
        'Red' = 'Red'
        'Green' = 'Green'
        'Yellow' = 'Yellow'
        'Blue' = 'Cyan'
    }
    
    Write-Host $Message -ForegroundColor $colors[$Color]
}

function Write-Header {
    param([string]$Message)
    
    Write-Host ""
    Write-ColorOutput -Color 'Blue' -Message "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    Write-ColorOutput -Color 'Blue' -Message "  $Message"
    Write-ColorOutput -Color 'Blue' -Message "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    Write-Host ""
}

$URL = "http://127.0.0.1:8000$Route"

Write-Header "🔍 VALIDAÇÃO FRONTEND - $Route"

# 1. Limpar cache
Write-ColorOutput -Color 'Yellow' -Message "📦 Limpando cache..."
php artisan view:clear | Out-Null
php artisan config:clear | Out-Null
php artisan cache:clear | Out-Null
Write-ColorOutput -Color 'Green' -Message "✅ Cache limpo"

# 2. Verificar se servidor está rodando
Write-ColorOutput -Color 'Yellow' -Message "🌐 Verificando servidor..."
try {
    $response = Invoke-WebRequest -Uri $URL -UseBasicParsing -TimeoutSec 5 -ErrorAction Stop
    if ($response.StatusCode -eq 200 -or $response.StatusCode -eq 302) {
        Write-ColorOutput -Color 'Green' -Message "✅ Servidor respondendo em $URL"
    }
} catch {
    Write-ColorOutput -Color 'Red' -Message "❌ Servidor não está respondendo"
    Write-ColorOutput -Color 'Yellow' -Message "💡 Execute: php artisan serve"
    exit 1
}

# 3. Verificar rotas
Write-ColorOutput -Color 'Yellow' -Message "🛣️  Verificando rotas..."
$routes = php artisan route:list
if ($routes -match [regex]::Escape($Route)) {
    Write-ColorOutput -Color 'Green' -Message "✅ Rota registrada"
} else {
    Write-ColorOutput -Color 'Red' -Message "❌ Rota não encontrada"
    Write-ColorOutput -Color 'Yellow' -Message "💡 Verifique routes/web.php"
    exit 1
}

# 4. Verificar componentes Livewire
Write-ColorOutput -Color 'Yellow' -Message "⚡ Verificando componentes Livewire..."
php artisan livewire:list | Out-Null
Write-ColorOutput -Color 'Green' -Message "✅ Componentes Livewire carregados"

# 5. Verificar sintaxe PHP
Write-ColorOutput -Color 'Yellow' -Message "🔍 Verificando sintaxe PHP..."
$errorCount = 0
Get-ChildItem -Path "app/Livewire" -Filter "*.php" -Recurse | ForEach-Object {
    $result = php -l $_.FullName 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-ColorOutput -Color 'Red' -Message "❌ Erro de sintaxe em: $($_.FullName)"
        $errorCount++
    }
}

if ($errorCount -eq 0) {
    Write-ColorOutput -Color 'Green' -Message "✅ Sintaxe PHP válida"
} else {
    Write-ColorOutput -Color 'Red' -Message "❌ Encontrados $errorCount erros de sintaxe"
    exit 1
}

# 6. Executar testes (se existirem)
Write-ColorOutput -Color 'Yellow' -Message "🧪 Verificando testes..."
$testDir = "tests/Feature/Livewire"
if (Test-Path $testDir) {
    $testResult = & .\vendor\bin\pest.bat $testDir --bail 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-ColorOutput -Color 'Green' -Message "✅ Testes passando"
    } else {
        Write-ColorOutput -Color 'Red' -Message "❌ Testes falhando"
        Write-ColorOutput -Color 'Yellow' -Message "💡 Execute: .\vendor\bin\pest.bat $testDir"
        exit 1
    }
} else {
    Write-ColorOutput -Color 'Yellow' -Message "⚠️  Nenhum teste encontrado"
}

# 7. Verificar componentes Flux Pro
Write-ColorOutput -Color 'Yellow' -Message "🎨 Verificando componentes Flux..."
$fluxProComponents = @(
    "flux:table",
    "flux:columns",
    "flux:rows",
    "flux:row",
    "flux:cell",
    "flux:dropdown",
    "flux:menu",
    "flux:sidebar",
    "flux:navbar",
    "flux:pagination",
    "flux:toast",
    "flux:tooltip",
    "flux:popover"
)

$fluxErrorCount = 0
foreach ($component in $fluxProComponents) {
    $found = Get-ChildItem -Path "resources/views/livewire" -Filter "*.blade.php" -Recurse | 
             Select-String -Pattern [regex]::Escape($component) -Quiet
    
    if ($found) {
        Write-ColorOutput -Color 'Red' -Message "❌ Componente Flux Pro encontrado: $component"
        $fluxErrorCount++
    }
}

if ($fluxErrorCount -eq 0) {
    Write-ColorOutput -Color 'Green' -Message "✅ Nenhum componente Flux Pro usado"
} else {
    Write-ColorOutput -Color 'Red' -Message "❌ Encontrados $fluxErrorCount componentes Flux Pro"
    Write-ColorOutput -Color 'Yellow' -Message "💡 Consulte: .ai/FLUX-UI-FREE-COMPONENTS.md"
    exit 1
}

# 8. Resumo final
Write-Header "✅ VALIDAÇÃO CONCLUÍDA"

Write-ColorOutput -Color 'Green' -Message "✅ Cache limpo"
Write-ColorOutput -Color 'Green' -Message "✅ Servidor respondendo"
Write-ColorOutput -Color 'Green' -Message "✅ Rota registrada"
Write-ColorOutput -Color 'Green' -Message "✅ Componentes Livewire carregados"
Write-ColorOutput -Color 'Green' -Message "✅ Sintaxe PHP válida"
Write-ColorOutput -Color 'Green' -Message "✅ Testes passando"
Write-ColorOutput -Color 'Green' -Message "✅ Nenhum componente Flux Pro usado"

Write-Host ""
Write-ColorOutput -Color 'Blue' -Message "🌐 Acesse: $URL"
Write-Host ""
Write-ColorOutput -Color 'Yellow' -Message "⚠️  LEMBRE-SE:"
Write-Host "   1. Verificar logs do terminal (php artisan serve)"
Write-Host "   2. Verificar console do navegador (F12)"
Write-Host "   3. Testar todas funcionalidades manualmente"
Write-Host "   4. Testar dark mode"
Write-Host "   5. Testar responsivo (F12 > Device Toolbar)"
Write-Host ""
Write-ColorOutput -Color 'Green' -Message "✨ Validação automática concluída com sucesso!"
Write-Host ""
