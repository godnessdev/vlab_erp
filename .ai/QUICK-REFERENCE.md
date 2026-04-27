# MCP Quick Reference Guide

## 🚀 Quick Start

### Para Garantir que IA Sempre Siga Guidelines

**Método 1: Prompt Explícito (Recomendado)**

Sempre inicie seus prompts com:

```markdown
⚠️ CRITICAL: Follow strictly all guidelines in .ai/guidelines/

Read before responding:
- .ai/guidelines/erp-architecture.md
- .ai/guidelines/multitenant-patterns.md
- .ai/guidelines/security-standards.md
- .ai/validation-checklist.yml

[Seu prompt aqui]
```

**Método 2: Kiro Steering File (Automático)**

O arquivo `.kiro/steering/mcp-guidelines.md` está configurado com `inclusion: auto`, o que significa que **será automaticamente incluído em todas as interações** no Kiro.

**Método 3: Verificação Manual**

Após receber código da IA, sempre valide contra:
```bash
cat .ai/validation-checklist.yml
```

## 📋 Templates de Prompt por Domínio

### Identity & Authentication

```markdown
⚠️ Follow .ai/guidelines/security-standards.md and multitenant-patterns.md

Context: [Descreva o componente de autenticação]
Domain: Identity & Authentication
Guidelines: security-standards.md, multitenant-patterns.md

Requirements:
- 2FA obrigatório para operações fiscais
- Audit trail (LGPD)
- Tenant-aware sessions
- Role-based access control

Output:
- Livewire component (class + blade)
- Pest tests com cenários multitenancy
- Migration com RLS policies
```

### Company Management

```markdown
⚠️ Follow .ai/guidelines/multitenant-patterns.md and fiscal-compliance.md

Context: [Descreva o componente de gestão de empresas]
Domain: Company Management
Guidelines: multitenant-patterns.md, fiscal-compliance.md

Requirements:
- CNPJ validation completa
- Certificate upload (A1/A3)
- Tenant isolation total
- Settings per-tenant

Output:
- Domain models com value objects
- Livewire forms com validação real-time
- Services para integração externa
- Tests com isolation between companies
```

### Fiscal Core

```markdown
⚠️ Follow .ai/guidelines/fiscal-compliance.md and performance-optimization.md

Context: [Descreva o componente fiscal]
Domain: Fiscal Core
Guidelines: fiscal-compliance.md, performance-optimization.md

Requirements:
- NFS-e Nacional 2026 (DPS → ADN → NFS-e)
- IBSCBS informativos (NÃO calcular localmente)
- Tax calculation by regime
- Contingency protocols
- Performance < 100ms cálculos, < 5s emissão

Output:
- Calculator classes com strategy pattern
- NFS-e service com API integration
- IBSCBS value objects (informativos)
- Queue jobs para async operations
- Tests com dados oficiais governo
```

### Services & Orders

```markdown
⚠️ Follow .ai/guidelines/erp-architecture.md and api-conventions.md

Context: [Descreva o componente de serviços/pedidos]
Domain: Services & Orders
Guidelines: erp-architecture.md, api-conventions.md

Requirements:
- Service catalog com códigos municipais
- Order workflow (Orçamento → Execução → Faturamento)
- Customer management B2B
- Integration com NFS-e automática

Output:
- Domain aggregates (Service, Order, Customer)
- Livewire components para workflow visual
- Event sourcing para auditoria
- API resources para integrações
```

## ✅ Checklist Rápido

### Antes de Pedir Código para IA

- [ ] Identifiquei o domínio correto
- [ ] Li as guidelines relevantes
- [ ] Preparei prompt com contexto completo
- [ ] Incluí referência às guidelines no prompt

### Após Receber Código da IA

- [ ] Código segue arquitetura DDD
- [ ] Tenant isolation aplicado (global scopes + RLS)
- [ ] Audit trail implementado
- [ ] Authorization checks presentes
- [ ] Testes incluídos (multitenancy + edge cases)
- [ ] Performance otimizada (< 200ms)
- [ ] Fiscal compliance validado (se aplicável)

### Antes de Commit

```bash
# 1. Executar testes
./vendor/bin/pest

# 2. Verificar qualidade
./vendor/bin/phpstan analyse
./vendor/bin/pint --test

# 3. Validar contra checklist
cat .ai/validation-checklist.yml
```

## 🔍 Padrões Obrigatórios

### Security (SEMPRE)

```php
// ✅ Global scope
protected static function booted(): void
{
    static::addGlobalScope('company', function (Builder $builder) {
        if ($companyId = app('current.company')?->id) {
            $builder->where('company_id', $companyId);
        }
    });
}

// ✅ Audit trail
AuditLog::create([
    'action' => 'action_name',
    'user_id' => auth()->id(),
    'company_id' => app('current.company')->id,
    'resource_type' => get_class($resource),
    'resource_id' => $resource->id,
    'metadata' => $metadata
]);

// ✅ Authorization
$this->authorize('action.name', $resource);
```

### Fiscal (SEMPRE)

```php
// ✅ IBSCBS informativos (NÃO calcular)
$ibscbs = [
    'finNFSe' => $adn->finNFSe,      // From ADN
    'cst' => $adn->cst,              // From ADN
    'cClassTrib' => $adn->cClassTrib // From ADN
];

// ✅ Contingency
try {
    $result = $this->sefazClient->submit($document);
} catch (SefazUnavailableException $e) {
    return $this->activateContingency($document);
}
```

### Testing (SEMPRE)

```php
// ✅ Multitenancy test
test('users only see their company data')
    ->actingAsCompanyUser($companyA)
    ->get('/api/resource')
    ->each(fn($item) => expect($item['company_id'])->toBe($companyA->id));

// ✅ Fiscal test with datasets
test('calculates tax by regime', function ($regime, $amount, $expected) {
    $company = Company::factory()->create(['tax_regime' => $regime]);
    $result = app(TaxCalculator::class)->calculate($amount, $company);
    expect($result->toArray())->toMatchArray($expected);
})->with('tax_regimes');
```

## 📁 Arquivos Importantes

### Configuração MCP
- `.ai/boost.json` - Configuração principal
- `.env.mcp` - Variáveis de ambiente
- `.ai/validation-checklist.yml` - Regras de validação

### Guidelines
- `.ai/guidelines/erp-architecture.md` - Arquitetura DDD
- `.ai/guidelines/multitenant-patterns.md` - Multitenancy
- `.ai/guidelines/fiscal-compliance.md` - Compliance fiscal
- `.ai/guidelines/security-standards.md` - Segurança
- `.ai/guidelines/testing-standards.md` - Testes

### Documentação
- `docs/MCP-GUIDELINES.md` - Padrões detalhados
- `docs/ONBOARDING-MCP.md` - Roadmap desenvolvimento
- `.ai/README.md` - Guia completo de uso

### Kiro Steering
- `.kiro/steering/mcp-guidelines.md` - Auto-incluído (Kiro)

## 🚨 Erros Comuns

### ❌ IA não seguiu guidelines

**Causa**: Prompt não foi explícito o suficiente

**Solução**:
```markdown
⚠️ CRITICAL: Read .ai/guidelines/[domain].md BEFORE responding

[Inclua trechos relevantes das guidelines no prompt]
```

### ❌ Código sem tenant isolation

**Causa**: Global scope não foi aplicado

**Solução**: Sempre valide que modelo tem:
```php
protected static function booted(): void
{
    static::addGlobalScope('company', function (Builder $builder) {
        if ($companyId = app('current.company')?->id) {
            $builder->where('company_id', $companyId);
        }
    });
}
```

### ❌ Testes falhando

**Causa**: Cenários multitenancy não cobertos

**Solução**: Sempre inclua testes de isolamento:
```php
test('users cannot access other company data')
    ->actingAsCompanyUser($companyA)
    ->get("/api/resource/{$companyB->resource->id}")
    ->assertForbidden();
```

## 📞 Comandos Úteis

```bash
# Ver configuração MCP
cat .ai/boost.json

# Ver checklist de validação
cat .ai/validation-checklist.yml

# Ver guidelines de um domínio
cat .ai/guidelines/fiscal-compliance.md

# Executar testes
./vendor/bin/pest

# Verificar qualidade
./vendor/bin/phpstan analyse
./vendor/bin/pint --test

# Ver documentação completa
cat .ai/README.md
```

## 🎯 Lembre-se

1. **SEMPRE** leia guidelines antes de pedir código
2. **SEMPRE** inclua referência às guidelines no prompt
3. **SEMPRE** valide código contra checklist
4. **SEMPRE** execute testes antes de commit
5. **SEMPRE** aplique tenant isolation
6. **SEMPRE** implemente audit trail
7. **SEMPRE** inclua authorization checks

---

**Para guia completo, consulte: `.ai/README.md`**
