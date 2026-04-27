---
inclusion: auto
---

# MCP Guidelines - Always Active

**⚠️ CRITICAL: This steering file is ALWAYS active and must be followed in EVERY interaction.**

## 🎯 Core Mandate

You are developing a **Brazilian ERP Multitenant System** with strict fiscal compliance requirements. Every piece of code you generate MUST follow the guidelines in `.ai/guidelines/` and validation rules in `.ai/validation-checklist.yml`.

## 📋 Before Every Response

**MANDATORY STEPS:**

1. ✅ **Read relevant guidelines** from `.ai/guidelines/`
2. ✅ **Check validation rules** in `.ai/validation-checklist.yml`
3. ✅ **Verify multitenancy patterns** are applied
4. ✅ **Ensure fiscal compliance** (NFS-e, IBSCBS, SPED)
5. ✅ **Include comprehensive tests** (Pest framework)

## 🏗️ Project Context

```yaml
Type: ERP Multitenant Fiscal
Domain: Brazilian Tax Compliance
Architecture: DDD + Livewire + PostgreSQL RLS
Framework: Laravel 12 + PHP 8.4
Frontend: Livewire 4.x + Flux UI + Alpine.js
Testing: Pest + Architecture Tests
Compliance: LGPD, NFS-e Nacional 2026, IBSCBS 2026
```

## 🔒 Mandatory Security Patterns

### 1. Tenant Isolation (ALWAYS)

```php
// ✅ REQUIRED: Global scope on all tenant-aware models
protected static function booted(): void
{
    static::addGlobalScope('company', function (Builder $builder) {
        if ($companyId = app('current.company')?->id) {
            $builder->where('company_id', $companyId);
        }
    });
}

// ✅ REQUIRED: RLS policies in migrations
DB::statement("
    CREATE POLICY company_isolation ON table_name
    FOR ALL
    USING (company_id = current_setting('app.current_company_id')::uuid)
");
```

### 2. Audit Trail (ALWAYS)

```php
// ✅ REQUIRED: Audit log for all sensitive operations
AuditLog::create([
    'action' => 'action_name',
    'user_id' => auth()->id(),
    'company_id' => app('current.company')->id,
    'resource_type' => get_class($resource),
    'resource_id' => $resource->id,
    'metadata' => ['before' => $before, 'after' => $after]
]);
```

### 3. Authorization (ALWAYS)

```php
// ✅ REQUIRED: Authorization check before actions
$this->authorize('action.name', $resource);

// ✅ REQUIRED: Tenant-scoped validation
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

## 📋 Mandatory Fiscal Patterns

### 1. NFS-e Nacional 2026

```php
// ✅ REQUIRED: Follow DPS → ADN → NFS-e workflow
// ✅ REQUIRED: XML validation against official schemas
// ✅ REQUIRED: Digital signature with valid certificate
// ✅ REQUIRED: Contingency protocol when SEFAZ offline
```

### 2. IBSCBS (Reforma Tributária 2026)

```php
// ⚠️ CRITICAL: IBSCBS fields are INFORMATIVE ONLY
// ❌ NEVER calculate IBS/CBS locally
// ✅ ONLY include fields from ADN response

$ibscbs = [
    'finNFSe' => $adn->finNFSe,      // From ADN
    'cst' => $adn->cst,              // From ADN
    'cClassTrib' => $adn->cClassTrib // From ADN
];
```

### 3. Tax Calculation

```php
// ✅ REQUIRED: Calculate by tax regime
// ✅ REQUIRED: Consider municipal rates
// ✅ REQUIRED: Apply retentions correctly
// ✅ REQUIRED: Performance < 100ms per calculation
```

## 🧪 Mandatory Testing Patterns

### 1. Multitenancy Tests (ALWAYS)

```php
test('users only see data from their company')
    ->actingAsCompanyUser($companyA)
    ->get('/api/resource')
    ->assertJsonCount(3, 'data')
    ->each(fn($item) => expect($item['company_id'])->toBe($companyA->id));

test('users cannot access other company data')
    ->actingAsCompanyUser($companyA)
    ->get("/api/resource/{$companyB->resource->id}")
    ->assertForbidden();
```

### 2. Fiscal Tests (ALWAYS)

```php
test('calculates tax correctly by regime', function ($regime, $amount, $expected) {
    $company = Company::factory()->create(['tax_regime' => $regime]);
    $result = app(TaxCalculator::class)->calculate($amount, $company);
    expect($result->toArray())->toMatchArray($expected);
})->with('tax_regimes'); // Use datasets
```

## 📁 Architecture Structure (ALWAYS FOLLOW)

```
Domain/              # Business logic, entities, value objects
├── Identity/
├── Company/
├── Fiscal/
└── [Domain]/

Application/         # Use cases, services, DTOs
├── Services/
├── DTOs/
└── Queries/

Infrastructure/      # Technical implementations
├── Repositories/
├── External/
└── Cache/

Presentation/        # UI/API layer
├── Livewire/       # Separate class + blade files
├── API/
└── Views/
```

## 🎨 Livewire 4.x Patterns (ALWAYS)

```php
// ✅ REQUIRED: Separate class and Blade files
// ✅ REQUIRED: Use Flux UI components
// ✅ REQUIRED: Proper validation with attributes
// ✅ REQUIRED: Tenant-aware queries

class ComponentName extends Component
{
    #[Validate('required|string|max:255')]
    public string $field = '';
    
    public function mount(): void
    {
        // ✅ Check tenant context
        if (!app('current.company')) {
            abort(403, 'Company context required');
        }
    }
    
    public function save(): void
    {
        $this->authorize('create', ModelName::class);
        
        $validated = $this->validate();
        
        // ✅ Audit trail
        AuditLog::create([...]);
        
        // ✅ Tenant-scoped creation
        ModelName::create([
            ...$validated,
            'company_id' => app('current.company')->id
        ]);
    }
}
```

## 📊 Performance Requirements (ALWAYS)

- ✅ Response time < 200ms (95% of requests)
- ✅ No N+1 queries (use eager loading)
- ✅ Proper indexing on foreign keys
- ✅ Cache frequently accessed data
- ✅ Pagination for large datasets

## ✅ Pre-Commit Checklist

Before generating code, verify:

- [ ] Read relevant guidelines from `.ai/guidelines/`
- [ ] Checked `.ai/validation-checklist.yml`
- [ ] Applied tenant isolation (RLS + global scopes)
- [ ] Implemented audit trail (LGPD)
- [ ] Added authorization checks
- [ ] Included Pest tests with multitenancy scenarios
- [ ] Followed DDD architecture structure
- [ ] Used Livewire 4.x patterns (separate files)
- [ ] Ensured fiscal compliance (if applicable)
- [ ] Performance optimized (< 200ms)

## 📚 Guidelines Priority Order

When generating code, consult in this order:

1. **erp-architecture.md** - Overall architecture patterns
2. **multitenant-patterns.md** - Tenant isolation rules
3. **fiscal-compliance.md** - Fiscal requirements (if applicable)
4. **security-standards.md** - Security patterns
5. **testing-standards.md** - Testing requirements
6. **performance-optimization.md** - Performance patterns
7. **api-conventions.md** - API standards (if applicable)

## 🚫 Common Mistakes to AVOID

❌ **NEVER** bypass tenant isolation
❌ **NEVER** skip audit trail on sensitive operations
❌ **NEVER** calculate IBS/CBS locally (informative only)
❌ **NEVER** use SELECT * queries
❌ **NEVER** forget authorization checks
❌ **NEVER** skip tests (especially multitenancy)
❌ **NEVER** ignore N+1 query problems
❌ **NEVER** hardcode company_id (use app('current.company'))

## 🎯 Output Format

Every code generation must include:

1. **PHP Class** with strict types, PHPDoc, proper namespace
2. **Blade View** (if Livewire) with Flux UI components
3. **Pest Tests** with multitenancy and edge case scenarios
4. **Migration** (if needed) with RLS policies
5. **Brief explanation** of implementation decisions

## 📖 Documentation References

- **Main Guidelines**: `docs/MCP-GUIDELINES.md`
- **Development Roadmap**: `docs/ONBOARDING-MCP.md`
- **Validation Rules**: `.ai/validation-checklist.yml`
- **Configuration**: `.ai/boost.json`
- **Domain Guidelines**: `.ai/guidelines/*.md`

---

**🎯 REMEMBER: This is not optional. Every line of code must follow these guidelines. Quality, security, and compliance are non-negotiable.**
