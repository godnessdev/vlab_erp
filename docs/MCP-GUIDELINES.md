# MCP Guidelines - Model Context Protocol

## Diretrizes Específicas para AI-Assisted Development

**Versão**: 1.0  
**Data**: 03 de Fevereiro de 2026  
**Vinculado**: [ONBOARDING-MCP.md](./ONBOARDING-MCP.md)

---

## 🎯 Objetivo das Guidelines MCP

Estabelecer padrões claros para que AI agents (GitHub Copilot, Claude, etc.) via Laravel Boost possam:

- Gerar código seguindo exatamente nossas arquiteturas
- Aplicar automaticamente padrões de segurança multitenant
- Implementar compliance fiscal brasileiro corretamente
- Manter consistência em todos os domínios do ERP

---

## 🤖 Configuração MCP Server

### 1. **Configuração boost.json**

```json
{
    "guidelines": {
        "path": ".ai/guidelines",
        "priority": [
            "erp-architecture.md",
            "multitenant-patterns.md",
            "fiscal-compliance.md",
            "security-standards.md",
            "testing-standards.md",
            "performance-optimization.md",
            "api-conventions.md"
        ]
    },
    "context": {
        "project_type": "ERP Multitenant",
        "domain": "Brazilian Tax Compliance",
        "architecture": "DDD + Livewire + PostgreSQL RLS",
        "ai_model": "github-copilot",
        "compliance": ["LGPD", "NFS-e Nacional", "IBSCBS 2026"]
    },
    "code_generation": {
        "enforce_guidelines": true,
        "auto_testing": true,
        "multitenancy_check": true,
        "fiscal_validation": true
    }
}
```

### 2. **Context Variables (.env.mcp)**

```bash
# MCP Environment Configuration
MCP_PROJECT_TYPE="ERP_MULTITENANT_FISCAL"
MCP_DOMAIN="BRAZILIAN_TAX_COMPLIANCE"
MCP_ARCHITECTURE="DDD_LIVEWIRE_RLS"
MCP_GUIDELINES_PATH=".ai/guidelines"
MCP_VALIDATION_LEVEL="STRICT"
MCP_FISCAL_YEAR="2026"
MCP_TAX_REFORM="IBSCBS_INFORMATIVO"
```

---

## 📋 Prompt Templates por Domínio

### **Domain 1: Identity & Authentication**

#### **Template Base**

```markdown
Context: Desenvolver componente de autenticação para ERP multitenant brasileiro
Domain: Identity & Authentication  
Guidelines: security-standards.md, multitenant-patterns.md
Architecture: Laravel 12 + Livewire + RLS PostgreSQL

Requirements:

- Multitenancy: Isolamento total via Row Level Security
- LGPD: Audit trail obrigatório para todas ações
- 2FA: Google Authenticator obrigatório para operações fiscais
- Session: Tenant-aware com validação de pertencimento

Output:

- Livewire component (class + blade separados)
- Pest tests com cenários multitenancy
- Migration com RLS policies
- Audit logging integration
```

#### **Exemplo Prático**

```
Prompt: "Implemente componente Livewire para login com 2FA seguindo security-standards.md"

Expected AI Output:
├── app/Http/Livewire/Auth/Login.php
├── resources/views/livewire/auth/login.blade.php
├── tests/Feature/Auth/LoginTest.php
├── database/migrations/add_mfa_to_users.php
└── app/Services/AuditService.php (se não existir)
```

### **Domain 2: Company Management**

#### **Template Base**

```markdown
Context: Gerenciamento de empresas (tenants) no ERP fiscal brasileiro
Domain: Company Management
Guidelines: multitenant-patterns.md, fiscal-compliance.md  
Architecture: Company as Tenant Root + RLS isolation

Requirements:

- CNPJ: Validação completa com consulta Receita Federal
- Certificates: Upload A1/A3 com validação
- Settings: Configurações por tenant (regime tributário, etc.)
- Branches: Suporte múltiplas filiais por empresa

Output:

- Domain models com value objects (CNPJ, IE)
- Livewire forms com validação em tempo real
- Services para integração externa (Receita Federal)
- Tests com isolation between companies
```

### **Domain 3: Fiscal Core**

#### **Template Base**

```markdown
Context: Motor fiscal para NFS-e nacional e compliance tributário brasileiro 2026
Domain: Fiscal Core
Guidelines: fiscal-compliance.md, performance-optimization.md
Architecture: Tax Calculation Engine + NFS-e Integration + IBSCBS

Requirements:

- NFS-e Nacional: Padrão 2026 com DPS → ADN → NFS-e
- IBSCBS: Campos informativos Reforma Tributária (não calcular localmente)
- Tax Engine: ISS, PIS/COFINS por regime tributário
- Contingency: Protocolos offline quando SEFAZ indisponível
- Performance: Cálculos < 100ms, emissão NFS-e < 5s

Output:

- Calculator classes com strategy pattern por tributo
- NFS-e service com API integration (Focus NFe ou similar)
- IBSCBS value objects (informativos apenas)
- Queue jobs para operações assíncronas
- Tests com dados oficiais governo
```

### **Domain 4: Services & Orders**

#### **Template Base**

```markdown
Context: Gestão de serviços e pedidos para ERP prestadores de serviços
Domain: Services & Orders  
Guidelines: erp-architecture.md, api-conventions.md
Architecture: Aggregate design + Event sourcing + Livewire UI

Requirements:

- Service Catalog: Códigos municipais, alíquotas ISS
- Order Workflow: Orçamento → Aprovação → Execução → Faturamento
- Customer Management: Foco B2B com dados fiscais completos
- Integration: Automatic NFS-e generation on order completion

Output:

- Domain aggregates (Service, Order, Customer)
- Livewire components para workflow visual
- Event sourcing para auditoria de mudanças
- API resources para integrações externas
```

---

## 🔍 Padrões de Validação MCP

### **1. Security Validation Pattern**

```php
// Todo código gerado deve incluir:

// ✅ Tenant isolation check
if (!app('current.company')) {
    throw new TenantException('Company context required');
}

// ✅ Authorization check
$this->authorize('action.name', $resource);

// ✅ Audit trail
AuditLog::create([
    'action' => 'action_name',
    'user_id' => auth()->id(),
    'company_id' => app('current.company')->id,
    'resource_type' => get_class($resource),
    'metadata' => $metadata
]);

// ✅ Input validation with multitenancy
protected function rules(): array
{
    return [
        'field' => [
            'required',
            Rule::exists('table', 'id')->where(function ($query) {
                $query->where('company_id', auth()->user()->company_id);
            })
        ]
    ];
}
```

### **2. Fiscal Validation Pattern**

```php
// Todo código fiscal deve incluir:

// ✅ Regime tributário check
$regime = $company->tax_regime;
if (!in_array($regime, ['simples_nacional', 'lucro_real', 'lucro_presumido'])) {
    throw new InvalidTaxRegimeException();
}

// ✅ IBSCBS informativo validation (não calcular)
public function validateIBSCBS(array $ibscbs): bool
{
    // ⚠️ Campos informativos apenas - valores reais vêm do ADN
    $required = ['finNFSe', 'cst', 'cClassTrib'];
    return collect($required)->every(fn($field) => isset($ibscbs[$field]));
}

// ✅ Contingency handling
try {
    $result = $this->sefazClient->submit($document);
} catch (SefazUnavailableException $e) {
    return $this->activateContingency($document);
}
```

### **3. Testing Pattern**

```php
// Todo feature deve ter testes:

// ✅ Multitenancy isolation
test('users only see data from their company')
    ->actingAsCompanyUser($companyA)
    ->get('/api/orders')
    ->assertJsonCount(3, 'data')
    ->each(fn($order) => expect($order['company_id'])->toBe($companyA->id));

// ✅ Fiscal scenarios with datasets
test('calculates tax correctly by regime', function ($regime, $amount, $expected) {
    $company = Company::factory()->create(['tax_regime' => $regime]);
    $result = app(TaxCalculator::class)->calculate($amount, $company);
    expect($result->toArray())->toMatchArray($expected);
})->with('tax_regimes'); // Dataset em tests/Datasets/
```

---

## 🚀 Workflow de Desenvolvimento com MCP

### **Fase 1: Planning & Context**

1. **Analyze Domain**: Identifique qual domínio será desenvolvido
2. **Select Guidelines**: Determine quais guidelines aplicar
3. **Set Context**: Configure MCP context para o domínio específico
4. **Define Tests**: Especifique cenários de teste antes do código

### **Fase 2: AI-Assisted Generation**

1. **Structured Prompt**: Use templates definidos acima
2. **Iterative Refinement**: Ajuste prompts baseado em output
3. **Guideline Validation**: Verifique se output segue guidelines
4. **Manual Review**: Code review humano obrigatório

### **Fase 3: Validation & Integration**

1. **Run Tests**: Execute suite completa (Pest)
2. **Performance Check**: Validate < 200ms response times
3. **Security Scan**: Verify tenant isolation e security
4. **Integration Test**: Test with existing components

### **Fase 4: Documentation & Handoff**

1. **Update Guidelines**: Se novos patterns emergiram
2. **Document Decisions**: Update ADRs se necessário
3. **Team Review**: Share learnings com equipe
4. **MCP Tuning**: Ajuste configurações baseado em feedback

---

## 🎯 Métricas de Sucesso MCP

### **Code Quality Metrics**

- **Guideline Adherence**: > 95% do código gerado segue guidelines
- **Security Coverage**: 100% componentes com tenant isolation
- **Test Coverage**: > 90% coverage automático via AI generation
- **Performance**: < 5% dos componentes precisam otimização manual

### **Productivity Metrics**

- **Development Speed**: 40% redução em time-to-delivery
- **Bug Reduction**: 60% menos bugs relacionados a multitenancy
- **Consistency**: 90% dos patterns aplicados automaticamente
- **Knowledge Transfer**: Novos devs produtivos em 1 semana vs 1 mês

### **Business Impact**

- **Compliance**: Zero violations fiscais por código AI-generated
- **Security**: Zero vazamentos de dados entre tenants
- **Maintenance**: 50% redução em refactoring necessário
- **Scalability**: Sistema suporta 10x more load sem architectural changes

---

## 📋 Checklist de Validação MCP

### **Antes de Commit**

- [ ] Código segue template do domínio específico
- [ ] Security patterns aplicados (audit, authorization, isolation)
- [ ] Testes Pest incluídos com cenários multitenancy
- [ ] Performance requirements atendidos (< 200ms)
- [ ] Fiscal compliance validado (se aplicável)
- [ ] Documentation inline adequada
- [ ] Guidelines específicas respeitadas

### **Code Review Checklist**

- [ ] MCP context foi adequadamente configurado
- [ ] Prompt template correto foi usado
- [ ] Output atende todos requirements funcionais
- [ ] Security patterns foram aplicados automaticamente
- [ ] Tests cobrem cenários críticos (multitenancy, fiscal)
- [ ] Performance não foi degradada
- [ ] Integration points funcionam corretamente

---

## 🔄 Continuous Improvement

### **Weekly MCP Review**

1. **Analyze Generated Code**: Quality, consistency, adherence
2. **Collect Team Feedback**: What worked? What didn't?
3. **Update Prompt Templates**: Based on learnings
4. **Refine Guidelines**: Add new patterns discovered
5. **Tune MCP Configuration**: Improve AI context

### **Monthly Assessment**

1. **Measure Productivity Gains**: Compare before/after MCP
2. **Security Audit**: Verify no regressions in tenant isolation
3. **Performance Review**: Ensure response times maintained
4. **Business Value**: Assess impact on delivery speed
5. **Team Satisfaction**: Survey developer experience

---

**🎯 Este documento estabelece o framework para desenvolvimento AI-assisted eficaz, mantendo qualidade, segurança e compliance do ERP multitenant brasileiro.**
