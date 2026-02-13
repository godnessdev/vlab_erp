# ADR-004: Pest como Framework de Testes

**Status**: Aceito  
**Data**: 2026-02-02  
**Decisores**: Equipe de Arquitetura ERP

## Contexto

Sistema ERP para prestadores de serviços brasileiros requer testes robustos para:

- **Compliance Fiscal**: Cálculos corretos de impostos (ICMS, ISS, PIS/COFINS, IBSCBS)
- **Multitenancy**: Isolamento seguro entre empresas
- **Integrações Complexas**: NFS-e nacional, SEFAZ, Receita Federal
- **Regras de Negócio**: Validações fiscais, workflows de aprovação
- **Performance**: Suporte a milhares de usuários por tenant

Alternativas avaliadas:

1. **Pest**: Framework moderno, expressivo, built on PHPUnit
2. **PHPUnit**: Padrão da indústria, verbose, mas comprovado

## Decisão

Escolhemos **Pest** como framework principal de testes, mantendo compatibilidade com PHPUnit.

## Justificativa

### ✅ Pontos Favoráveis

**1. Sintaxe Expressiva para Testes Fiscais**

Comparação de sintaxe:

```php
// PHPUnit (verbose)
class NfseEmissionTest extends TestCase
{
    public function testCanEmitNfseWithValidData(): void
    {
        $company = Company::factory()->create();
        $fatura = Fatura::factory()->for($company)->create();

        $this->actingAs($user = User::factory()->for($company)->create());

        $response = $this->post('/nfse/emitir', [
            'fatura_id' => $fatura->id,
            'tomador' => ['cnpj' => '12.345.678/0001-90'],
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('nfse', ['fatura_id' => $fatura->id]);
    }
}

// Pest (expressivo)
test('pode emitir nfse com dados válidos')
    ->withCompany()
    ->withFatura()
    ->actingAsUser()
    ->post('/nfse/emitir', [
        'fatura_id' => $fatura->id,
        'tomador' => ['cnpj' => '12.345.678/0001-90'],
    ])
    ->assertSuccessful()
    ->assertDatabaseHas('nfse', ['fatura_id' => $fatura->id]);
```

**2. Datasets para Cenários Fiscais Múltiplos**

```php
test('calcula impostos corretamente por regime tributário', function ($regime, $valor, $expectedTaxes) {
    $company = Company::factory()->create(['tax_regime' => $regime]);
    $fatura = Fatura::factory()->for($company)->create(['valor' => $valor]);

    $calculator = app(TaxCalculatorService::class);
    $taxes = $calculator->calculate($fatura);

    expect($taxes->toArray())->toMatchArray($expectedTaxes);
})->with([
    // [regime, valor, expected_taxes]
    ['simples_nacional', 1000.00, ['iss' => 50.00, 'pis' => 0, 'cofins' => 0]],
    ['lucro_real', 1000.00, ['iss' => 50.00, 'pis' => 16.50, 'cofins' => 76.00]],
    ['lucro_presumido', 1000.00, ['iss' => 50.00, 'pis' => 6.50, 'cofins' => 30.00]],
]);

test('valida campos IBSCBS para reforma tributária 2026', function ($finNFSe, $cst, $isValid) {
    $ibscbs = ['finNFSe' => $finNFSe, 'cst' => $cst, 'cClassTrib' => '123456'];

    $validator = app(IBSCBSValidator::class);
    $result = $validator->validate($ibscbs);

    expect($result->isValid())->toBe($isValid);
})->with([
    [0, '00', true],   // Normal, Tributado
    [1, '41', true],   // Complementar, Não tributado
    [2, '00', false],  // finNFSe inválido
    [0, '99', false],  // CST inválido
]);
```

**3. Testing Helpers para Multitenancy**

```php
// tests/Pest.php - Helpers globais
function withTenantContext(Company $company = null): TestCase
{
    $company = $company ?? Company::factory()->create();

    app()->instance('current.company', $company);
    DB::statement("SET app.current_company_id = ?", [$company->id]);

    return test();
}

function actingAsCompanyUser(Company $company = null): TestCase
{
    $company = $company ?? Company::factory()->create();
    $user = User::factory()->for($company)->create();

    return test()->actingAs($user)->withTenantContext($company);
}

// Uso nos testes
test('usuário só acessa pedidos da própria empresa')
    ->actingAsCompanyUser($companyA)
    ->get('/api/orders')
    ->assertJsonCount(3, 'data')
    ->each(fn($order) => expect($order['company_id'])->toBe($companyA->id));

test('não pode acessar dados de outra empresa')
    ->actingAsCompanyUser($companyA)
    ->get("/api/orders/{$orderFromCompanyB->id}")
    ->assertNotFound();
```

**4. Expectation API Poderosa**

```php
test('nfse contém todos campos obrigatórios')
    ->actingAsCompanyUser()
    ->post('/nfse/emitir', $validNfseData)
    ->assertSuccessful()
    ->expect(fn($response) => $response->json('data'))
    ->toHaveKeys(['numero', 'chave_acesso', 'link_visualizacao'])
    ->numero->not->toBeEmpty()
    ->chave_acesso->toMatch('/^\d{44}$/')
    ->link_visualizacao->toBeUrl();
```

### ✅ Benefícios Específicos ERP

**1. Testes de Integração Fiscal**

```php
// Mock de serviços externos
test('emite nfse com contingência quando sefaz indisponível')
    ->fake(Http::class, [
        'nfse.prefeitura.sp.gov.br/*' => Http::response('', 500)
    ])
    ->actingAsCompanyUser()
    ->post('/nfse/emitir', $nfseData)
    ->assertSuccessful()
    ->expect(fn($response) => $response->json('data.status'))
    ->toBe('contingencia');

// Arquivos de teste real
test('xml nfse é válido segundo schema xsd oficial')
    ->actingAsCompanyUser()
    ->post('/nfse/emitir', $nfseData)
    ->expect(fn($response) => $response->json('data.xml_content'))
    ->toPassXSDValidation(storage_path('schemas/nfse_v2.04.xsd'));
```

**2. Performance Testing**

```php
test('dashboard carrega em menos de 500ms')
    ->actingAsCompanyUser()
    ->time(fn() => $this->get('/dashboard'))
    ->assertSuccessful()
    ->expect($this->responseTime)->toBeLessThan(500);

test('listagem de faturas escala com milhares de registros')
    ->actingAsCompanyUser($company)
    ->setup(fn() => Fatura::factory()->for($company)->count(10000)->create())
    ->get('/faturas?per_page=50')
    ->assertSuccessful()
    ->expect($this->responseTime)->toBeLessThan(200);
```

### ⚠️ Pontos de Atenção

**1. Learning Curve**

- Equipe precisa aprender sintaxe Pest
- Mitigação: Training session + pair programming

**2. Debugging**

- Stack traces podem ser menos claros
- Mitigação: Pest v2+ melhorou significativamente

**3. IDE Support**

- Nem todos IDEs têm autocomplete perfeito
- Mitigação: PHPStorm tem plugin oficial

## Consequências

### Positivas

- ✅ **Readability**: Testes são auto-documentação das regras fiscais
- ✅ **Productivity**: Menos boilerplate, mais foco na lógica
- ✅ **Datasets**: Cenários múltiplos em testes compactos
- ✅ **Expectations**: Validações complexas mais expressivas
- ✅ **Compatibility**: Roda sobre PHPUnit (fallback sempre disponível)

### Negativas

- ❌ **Learning curve**: Equipe precisa aprender nova sintaxe
- ❌ **Ecosystem**: Algumas ferramentas podem ter melhor suporte PHPUnit
- ❌ **Stack traces**: Debugging pode ser mais complexo

## Implementação

### 1. Estrutura de Testes

```
tests/
├── Feature/
│   ├── Fiscal/
│   │   ├── NfseEmissionTest.php
│   │   ├── TaxCalculationTest.php
│   │   └── SPEDIntegrationTest.php
│   ├── Auth/
│   │   ├── MultitenantAuthTest.php
│   │   └── MfaTest.php
│   └── API/
│       ├── OrdersAPITest.php
│       └── ClientsAPITest.php
├── Unit/
│   ├── Domain/
│   │   ├── TaxCalculatorTest.php
│   │   └── IBSCBSValidatorTest.php
│   └── Services/
│       ├── NfseServiceTest.php
│       └── ReinfServiceTest.php
└── Datasets/
    ├── TaxRegimes.php
    ├── IBSCBSScenarios.php
    └── CompanyFixtures.php
```

### 2. Configuration

```php
// tests/Pest.php
<?php

uses(Tests\TestCase::class)->in('Feature');

// Helpers globais
function withTenantContext(Company $company = null): TestCase { /* ... */ }
function actingAsCompanyUser(Company $company = null): TestCase { /* ... */ }
function mockSefazUnavailable(): void { /* ... */ }
function mockReceiptaFederal(): void { /* ... */ }

// Custom expectations
expect()->extend('toPassXSDValidation', function (string $xsdPath) {
    return $this->toSatisfy(function ($xml) use ($xsdPath) {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        return $dom->schemaValidate($xsdPath);
    });
});

expect()->extend('toBeValidCNPJ', function () {
    return $this->toMatch('/^\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}$/');
});
```

### 3. Datasets Compartilhados

```php
// tests/Datasets/TaxRegimes.php
<?php

dataset('tax_regimes', [
    'simples_nacional' => ['simples_nacional', ['iss_rate' => 5.0, 'pis_rate' => 0, 'cofins_rate' => 0]],
    'lucro_real' => ['lucro_real', ['iss_rate' => 5.0, 'pis_rate' => 1.65, 'cofins_rate' => 7.60]],
    'lucro_presumido' => ['lucro_presumido', ['iss_rate' => 5.0, 'pis_rate' => 0.65, 'cofins_rate' => 3.00]],
]);

dataset('ibscbs_scenarios_2026', [
    'normal_tributado' => [['finNFSe' => 0, 'cst' => '00'], true],
    'complementar_nao_tributado' => [['finNFSe' => 1, 'cst' => '41'], true],
    'invalid_finNFSe' => [['finNFSe' => 3, 'cst' => '00'], false],
]);
```

## Roadmap

### Fase 1 (Atual) - ✅ Setup

- [x] Instalação Pest
- [x] Helpers multitenancy
- [x] Datasets fiscais básicos
- [x] Custom expectations

### Fase 2 (Q2 2026)

- [ ] Performance testing suite
- [ ] Visual regression testing
- [ ] Mutation testing (Infection)

### Fase 3 (Q3 2026)

- [ ] E2E testing com Laravel Dusk
- [ ] API contract testing
- [ ] Load testing automatizado

## Monitoramento

- **Coverage**: Manter > 85% code coverage
- **Performance**: Test suite completa < 2 minutos
- **Reliability**: < 1% flaky tests
- **Documentation**: Testes como documentação viva das regras fiscais

---

**Revisão**: Reavaliar após 3 meses se produtividade da equipe melhorou vs. PHPUnit.
