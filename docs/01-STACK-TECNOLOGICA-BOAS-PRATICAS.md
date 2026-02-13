# 🚀 STACK TECNOLÓGICA & BOAS PRÁTICAS - ERP MULTITENANT

## 📋 Visão Geral

Este documento define a stack tecnológica completa e as boas práticas para desenvolvimento do ERP SaaS multitenant, baseado no Laravel 12 com arquitetura orientada por agentes de IA.

## 🏗️ Stack Tecnológica Completa

### Core Framework & Linguagem
```yaml
Laravel: 12.x (LTS)
PHP: 8.4+
  Features Utilizadas:
    - Fibers (concorrência assíncrona)
    - Attributes (validações customizadas)
    - Enums (status e tipos)
    - Named Arguments
    - Match Expressions
    - Constructor Property Promotion
```

### Banco de Dados & Cache
```yaml
PostgreSQL: 16+
  Features:
    - Row Level Security (RLS)
    - JSONB para dados flexíveis
    - Particionamento temporal
    - Full-text search
    - UUID nativo

Redis: 7+
  Uso:
    - Cache de aplicação
    - Queues Laravel
    - Sessions
    - Rate limiting
    - Pub/Sub para eventos
```

### AI & Desenvolvimento Assistido
```yaml
Laravel Boost: MCP Server
  Capabilities:
    - Application introspection
    - Database tools
    - Route inspection
    - Artisan commands
    - Log analysis
    - Tinker integration
    - Documentation search

Editores Suportados:
  - Claude Code (primary)
  - Cursor
  - VS Code + extensions
  - PhpStorm

Guidelines Customizadas:
  - Domain-driven design
  - Fiscal compliance
  - Multitenant patterns
  - Security standards
```

### Pacotes Laravel Oficiais
```yaml
Sanctum: API authentication
Horizon: Queue monitoring & management
Telescope: Development debugging
Pulse: Application performance monitoring
Octane: High-performance HTTP server
Pennant: Feature flags
Cashier: SaaS billing & subscriptions
Scout: Full-text search
Socialite: OAuth providers
Dusk: Browser testing
Pint: Code styling
Sail: Docker development environment
```

### Frontend & Assets
```yaml
Vite: Asset bundling (substitui Mix)
  Plugins:
    - Vue 3 / React (opcional)
    - PostCSS
    - TypeScript

Tailwind CSS: 4.x
  Configuração:
    - Design system customizado
    - Dark mode support
    - Mobile-first
    - Componentes reutilizáveis

Livewire: 4.x (para interfaces dinâmicas)
  Features:
    - Real-time updates
    - File uploads
    - Validation
    - SPA-like experience
```

### Infraestrutura & Deploy
```yaml
Produção:
  Laravel Forge: Server management
  Laravel Vapor: Serverless (opcional)
  AWS/DigitalOcean: Cloud provider
  CloudFlare: CDN + DDoS protection

Storage:
  AWS S3: XMLs, PDFs, certificados
  Local: Cache temporário

Monitoring:
  Sentry: Error tracking
  New Relic: Performance monitoring
  LogRocket: Session replay
  Pulse: Laravel-native monitoring
```

### Ferramentas de Desenvolvimento
```yaml
Testing:
  PHPUnit: Unit testing
  Pest: Modern testing framework
  Dusk: Browser testing
  Faker: Test data generation

Quality:
  Pint: Code formatting
  Larastan: Static analysis
  PHPMD: Mess detection
  PHP_CodeSniffer: Coding standards

CI/CD:
  GitHub Actions: Automation
  Laravel Envoy: Deployment
  Docker: Containerization (via Sail)
```

## 🎯 Arquitetura de Agentes & Guidelines

### Estrutura .ai/guidelines/

```markdown
.ai/
├── guidelines/
│   ├── 01-erp-architecture.md          # Arquitetura DDD
│   ├── 02-multitenant-patterns.md      # Padrões multitenant
│   ├── 03-fiscal-compliance.md         # Conformidade fiscal
│   ├── 04-security-standards.md        # Segurança e LGPD
│   ├── 05-performance-optimization.md  # Performance
│   ├── 06-testing-standards.md         # Testes
│   ├── 07-api-conventions.md          # APIs
│   └── 08-deployment-guide.md         # Deploy
├── boost.json                         # Configuração Boost
└── mcp-config.json                    # Model Context Protocol
```

### Guidelines Específicas para ERP

#### 1. Domain-Driven Design (DDD)
```php
// Exemplo de Aggregate Root
#[Table('pessoas')]
class Pessoa extends Model
{
    use HasUuids, SoftDeletes, LogsActivity;
    
    protected $fillable = [
        'tipo', 'nome_razao_social', 'nome_fantasia',
        'data_nascimento_constituicao', 'status'
    ];
    
    protected $casts = [
        'tipo' => TipoPessoa::class,
        'status' => StatusPessoa::class,
        'data_nascimento_constituicao' => 'date',
    ];
    
    // Domain Methods
    public function validarDocumentos(): bool
    {
        return $this->documentos()->where('valido', true)->exists();
    }
    
    public function assumirPapel(Empresa $empresa, TipoPapel $tipo): Papel
    {
        return $this->papeis()->create([
            'empresa_id' => $empresa->id,
            'tipo_papel' => $tipo,
            'data_inicio' => now(),
            'status' => StatusPapel::ATIVO,
        ]);
    }
}
```

#### 2. Multitenant Security
```php
// Middleware de Tenant
class EnsureTenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $tenantId = $request->header('X-Tenant-ID') 
                   ?? $request->route('empresa_id')
                   ?? auth()->user()?->empresa_atual_id;
                   
        if (!$tenantId) {
            abort(400, 'Tenant ID required');
        }
        
        // Set context for RLS
        DB::statement('SET app.tenant_id = ?', [$tenantId]);
        
        return $next($request);
    }
}

// Global Scope para Tenant
trait HasTenant
{
    protected static function bootHasTenant()
    {
        static::addGlobalScope(new TenantScope);
    }
}
```

#### 3. Fiscal Compliance
```php
// Service para NFS-e
#[Injectable]
class NfseService
{
    public function __construct(
        private CertificadoService $certificado,
        private Webservicemunicipal $webservice,
        private LogFiscalService $logger
    ) {}
    
    public function emitir(Fatura $fatura): Nfse
    {
        $rps = $this->gerarRps($fatura);
        
        DB::transaction(function() use ($rps) {
            $xml = $this->gerarXml($rps);
            $xmlAssinado = $this->certificado->assinar($xml);
            
            $response = $this->webservice->enviar($xmlAssinado);
            
            $this->logger->registrar('ENVIO_RPS', $rps, $response);
            
            return $this->processarRetorno($response, $rps);
        });
    }
}
```

## 📊 Boas Práticas por Categoria

### 1. **Estrutura de Código & DDD**

#### Models (Eloquent)
```php
// ✅ Boas Práticas
class Servico extends Model
{
    use HasUuids, HasTenant, LogsActivity;
    
    // Explicit fillable/guarded
    protected $fillable = ['descricao', 'preco_base', 'aliquota_iss_default'];
    
    // Strong typing com Enums (PHP 8.1+)
    protected $casts = [
        'unidade_medida' => UnidadeMedida::class,
        'status' => StatusServico::class,
        'preco_base' => 'decimal:2',
    ];
    
    // Relationships com type hints
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
    
    // Domain methods, não apenas getters/setters
    public function calcularIss(string $codigoMunicipio): float
    {
        $codigo = $this->codigosMunicipais()
            ->where('codigo_municipio_ibge', $codigoMunicipio)
            ->vigente()
            ->first();
            
        return $this->preco_base * ($codigo?->aliquota_iss ?? $this->aliquota_iss_default) / 100;
    }
}

// ❌ Evitar - Model anêmico
class Servico extends Model
{
    // Apenas dados, sem comportamentos
}
```

#### Services (Business Logic)
```php
// ✅ Service Pattern
#[Injectable]
class FaturamentoService
{
    public function __construct(
        private ServicoService $servicoService,
        private ImpostoService $impostoService,
        private EventDispatcher $events
    ) {}
    
    public function faturarOrdem(OrdemServico $ordem): Fatura
    {
        $this->validarOrdem($ordem);
        
        $fatura = DB::transaction(function() use ($ordem) {
            $fatura = $this->criarFatura($ordem);
            $this->criarItensFatura($fatura, $ordem);
            $this->calcularImpostos($fatura);
            
            return $fatura;
        });
        
        $this->events->dispatch(new FaturaGerada($fatura));
        
        return $fatura;
    }
}
```

### 2. **Multitenant & Security**

#### Row Level Security (PostgreSQL)
```sql
-- Política de isolamento
CREATE POLICY tenant_isolation ON ordem_servico
FOR ALL TO app_role
USING (empresa_id = current_setting('app.tenant_id')::uuid);

-- Aplicar em todas as tabelas
ALTER TABLE ordem_servico ENABLE ROW LEVEL SECURITY;
```

#### Cache Isolado por Tenant
```php
class TenantCache
{
    public function remember(string $key, callable $callback, int $ttl = 3600)
    {
        $tenantKey = "tenant:" . tenant()->id . ":" . $key;
        return Cache::remember($tenantKey, $ttl, $callback);
    }
}
```

### 3. **Performance & Escalabilidade**

#### Database Optimization
```php
// ✅ Índices compostos para multitenant
Schema::table('faturas', function (Blueprint $table) {
    $table->index(['empresa_id', 'status', 'data_emissao']);
    $table->index(['empresa_id', 'cliente_id', 'data_emissao']);
});

// ✅ Eager Loading
$ordensComItens = OrdemServico::with([
    'itens.servico', 
    'cliente.pessoa', 
    'apontamentos.prestador'
])->where('empresa_id', tenant()->id)->get();

// ❌ N+1 Problem
foreach ($ordens as $ordem) {
    echo $ordem->cliente->nome; // Query por iteração
}
```

#### Queue Optimization
```php
// ✅ Job específico e bem definido
class ProcessarRetornoNfse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        public string $loteId,
        public int $tentativa = 1
    ) {}
    
    public function handle(): void
    {
        $lote = LoteRps::findOrFail($this->loteId);
        
        // Processing logic...
        
        if ($this->tentativa < 3) {
            ProcessarRetornoNfse::dispatch($this->loteId, $this->tentativa + 1)
                ->delay(now()->addMinutes(5));
        }
    }
}
```

### 4. **Testing & Quality**

#### Test Structure
```php
// ✅ Feature Test with proper setup
class FaturamentoTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    
    public function test_pode_faturar_ordem_concluida(): void
    {
        // Arrange
        $empresa = Empresa::factory()->create();
        $this->actingAsTenant($empresa);
        
        $ordem = OrdemServico::factory()
            ->concluida()
            ->create(['empresa_id' => $empresa->id]);
            
        // Act
        $fatura = app(FaturamentoService::class)->faturarOrdem($ordem);
        
        // Assert
        $this->assertNotNull($fatura);
        $this->assertEquals($ordem->valor_total_executado, $fatura->valor_total);
        $this->assertDatabaseHas('faturas', [
            'id' => $fatura->id,
            'status' => StatusFatura::ABERTA
        ]);
    }
}
```

### 5. **API Design**

#### Resource Pattern
```php
// ✅ API Resource bem estruturado
class FaturaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'numero_fatura' => $this->numero_fatura,
            'data_emissao' => $this->data_emissao->format('Y-m-d'),
            'valor_total' => number_format($this->valor_total, 2, '.', ''),
            'status' => $this->status->value,
            
            // Conditional includes
            'cliente' => new ClienteResource($this->whenLoaded('cliente')),
            'itens' => ItemFaturaResource::collection($this->whenLoaded('itens')),
            
            // Computed fields
            'dias_vencimento' => $this->data_vencimento->diffInDays(now()),
            'pode_cancelar' => $this->status === StatusFatura::ABERTA,
        ];
    }
}
```

#### Form Requests
```php
class StoreFaturaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Fatura::class);
    }
    
    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'uuid', new ExistsInTenant('papeis')],
            'ordens_servico' => ['required', 'array', 'min:1'],
            'ordens_servico.*' => ['uuid', new ExistsInTenant('ordem_servicos')],
            'data_vencimento' => ['required', 'date', 'after:today'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'cliente_id.required' => 'Cliente é obrigatório',
            'ordens_servico.required' => 'Pelo menos uma ordem de serviço deve ser selecionada',
        ];
    }
}
```

## 🔒 Segurança & LGPD

### Data Protection
```php
// Trait para logs LGPD
trait LogsLgpdAccess
{
    protected static function bootLogsLgpdAccess(): void
    {
        static::retrieved(function ($model) {
            if ($model instanceof Pessoa) {
                RastreioLgpd::create([
                    'pessoa_id' => $model->id,
                    'usuario_id' => auth()->id(),
                    'empresa_id' => tenant()->id,
                    'tipo_acao' => TipoAcaoLgpd::VISUALIZACAO,
                    'finalidade' => request()->route()->getName(),
                    'dados_acessados' => $model->toArray(),
                    'base_legal' => BaseLegalLgpd::LEGITIMO_INTERESSE,
                    'ip_origem' => request()->ip(),
                ]);
            }
        });
    }
}
```

### Encryption & Certificates
```php
class CertificadoService
{
    public function assinarXml(string $xml, string $empresaId): string
    {
        $certificado = $this->buscarCertificadoAtivo($empresaId);
        
        if ($certificado->isExpired()) {
            throw new CertificadoExpiradoException();
        }
        
        return $this->pkcs12Service->sign($xml, $certificado->getDecryptedContent());
    }
}
```

## 📈 Monitoring & Observability

### Custom Metrics
```php
// Custom metrics para Pulse
Pulse::record('nfse_emitida', 1, [
    'empresa_id' => $nfse->empresa_id,
    'municipio' => $nfse->municipio_prestacao,
])->count();

// Slow query tracking
DB::listen(function ($query) {
    if ($query->time > 1000) { // > 1 segundo
        Pulse::record('slow_query', $query->time, [
            'sql' => $query->sql,
            'tenant_id' => tenant()->id ?? 'unknown',
        ])->avg();
    }
});
```

---

## 🚀 Próximos Passos

1. **Configurar Ambiente**: `composer create-project laravel/laravel erp-multitenant`
2. **Instalar Boost**: `composer require laravel/boost --dev && php artisan boost:install`
3. **Setup Guidelines**: Criar arquivos em `.ai/guidelines/`
4. **Configurar DB**: PostgreSQL com RLS
5. **Implementar Domains**: Seguir estrutura DDD
6. **Desenvolver com IA**: Usar agentes para acelerar desenvolvimento

*Esta stack e práticas garantem um ERP enterprise-grade, escalável e maintível.*
