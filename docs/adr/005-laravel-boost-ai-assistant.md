# ADR-005: Laravel Boost para AI-Assisted Development

**Status**: Aceito  
**Data**: 2026-02-02  
**Decisores**: Equipe de Arquitetura ERP

## Contexto

Desenvolvimento do ERP multitenant para prestadores de serviços brasileiros envolve:

- **Complexidade Fiscal**: Regras tributárias brasileiras complexas e em constante mudança
- **Compliance**: LGPD, auditoria fiscal, NFS-e nacional, IBSCBS (Reforma Tributária 2026)
- **Multitenancy**: Isolamento seguro entre empresas com performance
- **Time-to-Market**: Pressão para entregar funcionalidades fiscais rapidamente
- **Quality Assurance**: Zero tolerância a bugs em cálculos fiscais

Alternativas avaliadas:

1. **Laravel Boost**: AI assistant integrado ao Laravel, acesso a contexto do projeto
2. **GitHub Copilot**: AI genérico, sem contexto específico do domínio
3. **No AI assistance**: Desenvolvimento manual tradicional

## Decisão

Escolhemos **Laravel Boost** como ferramenta principal de AI-assisted development.

## Justificativa

### ✅ Pontos Favoráveis

**1. Contexto Domain-Specific**

Laravel Boost lerá e aplicará nossos guidelines específicos:

```
.ai/guidelines/
├── erp-architecture.md          # Padrões DDD para ERP
├── multitenant-patterns.md      # Isolamento entre empresas
├── fiscal-compliance.md         # Regras fiscais brasileiras 2026
├── security-standards.md        # LGPD + auditoria
├── performance-optimization.md  # Escalabilidade multitenant
├── testing-standards.md         # Pest + testes fiscais
├── api-conventions.md           # REST para APIs
└── deployment-guide.md          # Infraestrutura produção
```

**2. Code Generation Inteligente**

Exemplo de prompt que funcionará com nossos guidelines:

```
"Crie um componente Livewire para cadastro de cliente com:
- Validação de CNPJ em tempo real
- Consulta automática à Receita Federal
- Isolamento multitenant seguindo security-standards.md
- Auditoria LGPD de todas as ações
- Testes Pest seguindo testing-standards.md"
```

Resultado esperado:

```php
// Será gerado seguindo exatamente nossos padrões
class CadastrarCliente extends ERPComponent
{
    public $cnpj = '';
    public $razao_social = '';
    // ... outros campos

    protected $rules = [
        'cnpj' => ['required', 'cnpj', Rule::unique('clients')->where('company_id', auth()->user()->company_id)],
        // ... outras validations
    ];

    public function mount()
    {
        $this->authorize('clients.create');
        $this->validateTenant();
        $this->auditAction('client_form_opened');
    }

    public function updatedCnpj()
    {
        if (strlen($this->cnpj) === 14) {
            $this->consultarReceitaFederal();
        }
    }

    // ... resto do código seguindo guidelines
}
```

**3. Compliance Automation**

AI entenderá regras fiscais complexas e gerará código compliant:

```php
// Prompt: "Implemente cálculo de IBSCBS para NFS-e seguindo Reforma Tributária 2026"
// Resultado: Código que segue fiscal-compliance.md automaticamente

class IBSCBSCalculator
{
    public function calculate(Fatura $fatura): array
    {
        // ⚠️ Importante: Valores informativos apenas para teste 2026
        // Cálculo real será feito pelo ADN/SEFIN Nacional

        $regime = $fatura->company->tax_regime;
        $valor = $fatura->valor_servicos;

        return [
            'finNFSe' => $this->determineFinalidade($fatura),
            'indFinal' => $this->determineIndicadorConsumidor($fatura->tomador),
            'cst' => $this->determineCST($regime, $fatura->tipo_servico),
            'cClassTrib' => $this->getClassificacaoTributaria($fatura->servico),
            // Valores informativos - NÃO usar em produção
            'vIBS' => 0.00, // Será calculado pelo ADN
            'vCBS' => 0.00, // Será calculado pelo ADN
        ];
    }
}
```

**4. Testing Assistance**

```php
// Prompt: "Gere testes Pest para validar isolamento multitenant no componente CadastrarCliente"
// Resultado automático seguindo testing-standards.md:

test('usuário só vê clientes da própria empresa')
    ->actingAsCompanyUser($companyA)
    ->livewire(ListarClientes::class)
    ->assertSee($clienteCompanyA->name)
    ->assertDontSee($clienteCompanyB->name);

test('não pode editar cliente de outra empresa')
    ->actingAsCompanyUser($companyA)
    ->livewire(EditarCliente::class, ['client' => $clienteCompanyB])
    ->assertForbidden();

test('auditoria registra acesso a dados do cliente', function () {
    $user = User::factory()->create();
    $client = Client::factory()->for($user->company)->create();

    actingAs($user)
        ->livewire(VisualizarCliente::class, ['client' => $client]);

    expect(AuditLog::latest()->first())
        ->action->toBe('client_accessed')
        ->user_id->toBe($user->id)
        ->resource_id->toBe($client->id);
});
```

### ✅ Integração com Workflow

**1. Code Review Assistido**

```bash
# Boost analisa PR e identifica problemas
php artisan boost:review-pr --guidelines

# Output exemplo:
# ⚠️  EmitirNfse.php linha 45: Faltou validação de tenant context
# ✅  TaxCalculator.php: Segue fiscal-compliance.md corretamente
# ⚠️  ClientController.php linha 23: Missing audit log para LGPD
```

**2. Documentation Sync**

```bash
# Boost atualiza código quando guidelines mudam
php artisan boost:sync-guidelines

# Exemplo: Quando fiscal-compliance.md é atualizado com nova NT da RFB,
# Boost sugere alterações em todos os calculators fiscais automaticamente
```

**3. Refactoring Assistido**

```php
// Prompt: "Refatore OrderService para usar padrão Repository seguindo erp-architecture.md"
// Boost entende nossos patterns e faz refactoring completo mantendo testes
```

### ⚠️ Pontos de Atenção

**1. AI Reliability**

- IA pode gerar código incorreto ocasionalmente
- Mitigação: Code review obrigatório + testes automatizados

**2. Guidelines Dependency**

- Eficácia depende da qualidade de nossos guidelines
- Mitigação: Manter guidelines atualizados e precisos

**3. Learning Curve**

- Equipe precisa aprender a prompting efetivo
- Mitigação: Training sessions + best practices doc

## Consequências

### Positivas

- ✅ **Velocity**: 30-50% redução no tempo de desenvolvimento
- ✅ **Consistency**: Código segue padrões automaticamente
- ✅ **Quality**: Menos bugs por compliance automation
- ✅ **Knowledge Transfer**: Guidelines aplicados automaticamente por novos devs
- ✅ **Documentation**: Guidelines ficam sempre em sync com código

### Negativas

- ❌ **Over-reliance**: Risco de equipe perder habilidades fundamentais
- ❌ **Black box**: AI decisions podem ser opacas
- ❌ **Guidelines maintenance**: Necessário manter documentação precisa

## Implementação

### 1. Setup Inicial

```bash
# Instalação Laravel Boost
composer require laravel/boost
php artisan boost:install

# Configuração para nossos guidelines
php artisan boost:configure --guidelines-path=.ai/guidelines
```

### 2. Guidelines Integration

```php
// config/boost.php
return [
    'guidelines' => [
        'path' => '.ai/guidelines',
        'priority' => [
            'security-standards.md',      // Mais crítico
            'fiscal-compliance.md',
            'multitenant-patterns.md',
            'erp-architecture.md',
            'testing-standards.md',
            'performance-optimization.md',
            'api-conventions.md',
            'deployment-guide.md',
        ],
    ],

    'code_generation' => [
        'enforce_guidelines' => true,
        'auto_testing' => true,
        'audit_trail' => true,
    ],

    'integrations' => [
        'pest' => true,
        'livewire' => true,
        'multitenancy' => true,
    ],
];
```

### 3. Workflow Integration

```yaml
# .github/workflows/boost-review.yml
name: AI Code Review

on:
    pull_request:
        types: [opened, synchronize]

jobs:
    boost-review:
        runs-on: ubuntu-latest
        steps:
            - uses: actions/checkout@v3
            - name: Setup PHP
              uses: shivammathur/setup-php@v2
              with:
                  php-version: "8.4"

            - name: Laravel Boost Review
              run: |
                  composer install
                  php artisan boost:review-pr --guidelines --format=github
```

### 4. Prompting Best Practices

```markdown
# docs/boost-prompting-guide.md

## Effective Prompts for ERP Development

### ✅ Good Prompts

- "Crie um service para cálculo de ISS seguindo fiscal-compliance.md com testes Pest"
- "Implemente middleware de auditoria LGPD seguindo security-standards.md"
- "Gere migration para tabela multitenant seguindo multitenant-patterns.md"

### ❌ Avoid

- "Crie um controller" (muito vago)
- "Faz um CRUD" (não especifica regras de negócio)
- "Copia esse código" (não segue guidelines)

### 🎯 Template

"Implemente [FEATURE] seguindo [GUIDELINE.md] com [REQUIREMENTS_ESPECÍFICOS] incluindo testes Pest"
```

## Monitoramento

### KPIs

- **Development Velocity**: Tempo médio para implementar features
- **Code Quality**: Bugs em produção relacionados a compliance
- **Guidelines Adherence**: % de PRs que seguem padrões automaticamente
- **Developer Satisfaction**: Survey sobre produtividade com Boost

### Métricas Esperadas (6 meses)

- 40% redução em tempo de development
- 60% redução em bugs de compliance
- 90% adherence automática a guidelines
- 8.5/10 developer satisfaction score

## Roadmap

### Fase 1 (Q1 2026) - ✅ Setup Básico

- [x] Instalação Laravel Boost
- [x] Guidelines integration
- [x] Team training

### Fase 2 (Q2 2026) - Automation

- [ ] Auto-generation de componentes Livewire
- [ ] Integration com CI/CD
- [ ] Custom prompts para domínio fiscal

### Fase 3 (Q3 2026) - Advanced Features

- [ ] Real-time code review
- [ ] Automatic refactoring suggestions
- [ ] Performance optimization hints

### Fase 4 (Q4 2026) - AI-First Development

- [ ] Voice-to-code para specs
- [ ] Automatic documentation generation
- [ ] Predictive bug detection

---

**Revisão**: Avaliar ROI e developer satisfaction em 3 meses.
