# Brazilian Fiscal Compliance Guidelines for AI Agents

## Overview
Brazilian fiscal compliance is complex and legally binding. AI agents must understand SPED (Public Digital Bookkeeping System), NFe (Electronic Invoice) requirements, and tax calculation rules.

## Critical Legal Context

### Why Fiscal Compliance Matters
- **Legal Requirement**: Non-compliance can result in fines, business closure, or criminal charges
- **Audit Trail**: All fiscal operations must be auditable by federal authorities
- **Real-time Validation**: Many fiscal documents require government approval before issuance

### Key Government Systems
1. **SEFAZ** (State Tax Authority): NFe, NFCe validation
2. **RFB** (Federal Revenue): CTe, MDFe, EFD submissions
3. **IBGE**: Statistical reporting
4. **SUFRAMA**: Amazon region special operations

## Tax Document Types

### 1. NFe (Nota Fiscal Eletrônica) - Electronic Invoice

**Use Cases:**
- B2B sales and services
- Interstate commerce
- High-value transactions (>R$ 5000 in some states)

**Implementation Pattern:**
```php
class NFeService
{
    public function create(Order $order): NFeDocument
    {
        $nfe = new NFeDocument([
            'company' => $order->company,
            'customer' => $order->customer,
            'items' => $order->items->map(fn($item) => $this->mapToNFeItem($item)),
            'taxes' => $this->calculateTaxes($order),
            'cfop' => $this->determineCFOP($order),
        ]);
        
        // Validate business rules before submission
        $this->validateNFe($nfe);
        
        // Submit to SEFAZ for authorization
        $response = $this->submitToSEFAZ($nfe);
        
        if ($response->isApproved()) {
            $nfe->markAsAuthorized($response->authorizationKey);
            $this->generateDanfe($nfe); // PDF for printing
        }
        
        return $nfe;
    }
    
    private function calculateTaxes(Order $order): TaxCalculation
    {
        $calculator = new BrazilianTaxCalculator($order->company);
        
        return $calculator
            ->withICMS($this->getICMSRate($order))
            ->withIPI($this->getIPIRate($order))
            ->withPIS($this->getPISRate($order))
            ->withCOFINS($this->getCOFINSRate($order))
            ->calculate($order);
    }
}
```

### 2. NFCe (Nota Fiscal de Consumidor Eletrônica) - Consumer Electronic Invoice

**Use Cases:**
- B2C retail sales
- Point-of-sale transactions
- Consumer-facing businesses

**Key Differences from NFe:**
```php
class NFCeService extends BaseDocumentService
{
    protected function getDocumentConstraints(): array
    {
        return [
            'max_value' => 50000.00, // R$ 50,000 limit
            'requires_cpf_cnpj' => false, // Optional for small amounts
            'offline_contingency' => true, // Can operate offline
            'simplified_layout' => true, // Simpler DANFE format
        ];
    }
    
    public function handleOfflineMode(Order $order): NFCeDocument
    {
        // Offline contingency protocol
        $nfce = $this->createOfflineDocument($order);
        
        // Queue for later synchronization
        OfflineDocumentQueue::dispatch($nfce);
        
        return $nfce;
    }
}
```

### 3. CTe (Conhecimento de Transporte Eletrônico) - Electronic Transport Document

**Use Cases:**
- Cargo transportation
- Logistics services
- Multi-modal transport

### 4. MDFe (Manifesto de Documentos Fiscais Eletrônicos) - Electronic Fiscal Document Manifest

**Use Cases:**
- Grouping multiple CTes
- Long-distance transportation
- Cargo manifest management

## Tax Calculation Engine

### ICMS (State VAT) Calculation
```php
class ICMSCalculator
{
    private array $stateRates = [
        'SP' => 18.00,
        'RJ' => 20.00,
        'MG' => 18.00,
        // ... all 27 states
    ];
    
    public function calculate(Order $order): ICMSTax
    {
        $originState = $order->company->state;
        $destinationState = $order->customer->state;
        
        if ($originState === $destinationState) {
            return $this->calculateIntrastate($order);
        }
        
        return $this->calculateInterstate($order);
    }
    
    private function calculateInterstate(Order $order): ICMSTax
    {
        $originRate = $this->stateRates[$order->company->state];
        $destinationRate = $this->stateRates[$order->customer->state];
        
        // DIFAL (Interstate Tax Difference) calculation
        $internalRate = min($originRate, 12.00); // Interstate rate cap
        $difal = max(0, $destinationRate - $internalRate);
        
        return new ICMSTax([
            'internal_amount' => $order->subtotal * ($internalRate / 100),
            'difal_amount' => $order->subtotal * ($difal / 100),
            'origin_state' => $order->company->state,
            'destination_state' => $order->customer->state,
        ]);
    }
}
```

### PIS/COFINS Calculation
```php
class PISCOFINSCalculator
{
    public function calculate(Company $company, Order $order): PISCOFINSax
    {
        $regime = $company->tax_regime; // Simples, Presumido, Real
        
        return match ($regime) {
            TaxRegime::SIMPLES => $this->calculateSimples($order),
            TaxRegime::PRESUMIDO => $this->calculatePresumido($order),
            TaxRegime::REAL => $this->calculateReal($order),
        };
    }
    
    private function calculateReal(Order $order): PISCOFINSTax
    {
        // Real regime: cumulative vs non-cumulative
        $pisRate = $order->isExportSale() ? 0.00 : 1.65;
        $cofinsRate = $order->isExportSale() ? 0.00 : 7.60;
        
        return new PISCOFINSTax([
            'pis_amount' => $order->subtotal * ($pisRate / 100),
            'cofins_amount' => $order->subtotal * ($cofinsRate / 100),
            'regime' => TaxRegime::REAL,
            'calculation_basis' => $order->subtotal,
        ]);
    }
}
```

## CFOP (Fiscal Operation Code) Management

```php
class CFOPDeterminer
{
    private array $cfopRules = [
        // Sales within state
        '5.101' => 'Sale of goods produced by the issuer',
        '5.102' => 'Sale of goods acquired from third parties',
        '5.103' => 'Sale of goods produced by the issuer and acquired from third parties',
        
        // Interstate sales
        '6.101' => 'Interstate sale of goods produced by the issuer',
        '6.102' => 'Interstate sale of goods acquired from third parties',
        
        // Services
        '5.933' => 'Service provision within the state',
        '6.933' => 'Interstate service provision',
        
        // Returns
        '5.202' => 'Return of sale within the state',
        '6.202' => 'Interstate return of sale',
    ];
    
    public function determine(Order $order): string
    {
        $isInterestate = $order->company->state !== $order->customer->state;
        $isService = $order->isServiceOrder();
        $isReturn = $order->isReturnOrder();
        
        if ($isReturn) {
            return $isInterestate ? '6.202' : '5.202';
        }
        
        if ($isService) {
            return $isInterestate ? '6.933' : '5.933';
        }
        
        // Goods sale - determine based on production
        if ($this->isProducedGoods($order)) {
            return $isInterestate ? '6.101' : '5.101';
        }
        
        return $isInterestate ? '6.102' : '5.102';
    }
}
```

## SPED Integration

### EFD-ICMS/IPI (SPED Fiscal)
```php
class SPEDFiscalGenerator
{
    public function generateEFDFile(Company $company, Carbon $startDate, Carbon $endDate): SPEDFile
    {
        $generator = new EFDGenerator($company);
        
        // Record 0000 - File opening
        $generator->addRecord('0000', [
            'cod_ver' => '016', // EFD version
            'cod_fin' => '0', // Original file
            'dt_ini' => $startDate->format('dmY'),
            'dt_fin' => $endDate->format('dmY'),
            'nome' => $company->corporate_name,
            'cnpj' => $company->cnpj,
            'cpf' => '',
            'uf' => $company->state,
            'ie' => $company->state_registration,
            'cod_mun' => $company->city_code,
            'im' => $company->municipal_registration,
            'suframa' => $company->suframa_code,
            'ind_perfil' => 'A', // Profile indicator
            'ind_ativ' => '0', // Activity indicator
        ]);
        
        // Add all required blocks
        $this->addBlock0($generator, $company); // Registration and tables
        $this->addBlockC($generator, $company, $startDate, $endDate); // Documents
        $this->addBlockD($generator, $company, $startDate, $endDate); // Services
        $this->addBlockE($generator, $company, $startDate, $endDate); // ICMS apportionment
        
        return $generator->generate();
    }
}
```

### ECD (Digital Accounting Bookkeeping)
```php
class ECDGenerator
{
    public function generateECDFile(Company $company, int $year): ECDFile
    {
        $transactions = FinancialTransaction::where('company_id', $company->id)
            ->whereYear('transaction_date', $year)
            ->orderBy('transaction_date')
            ->get();
        
        $generator = new ECDBookkeepingGenerator($company);
        
        foreach ($transactions as $transaction) {
            $generator->addJournalEntry([
                'date' => $transaction->transaction_date,
                'account_code' => $transaction->account_code,
                'account_name' => $transaction->account_name,
                'debit_amount' => $transaction->debit_amount,
                'credit_amount' => $transaction->credit_amount,
                'description' => $transaction->description,
                'document_number' => $transaction->document_number,
            ]);
        }
        
        return $generator->generate();
    }
}
```

## Validation Rules

### Document Validation Pipeline
```php
class FiscalDocumentValidator
{
    private array $validators = [
        CompanyDataValidator::class,
        CustomerDataValidator::class,
        ProductDataValidator::class,
        TaxCalculationValidator::class,
        CFOPValidator::class,
        SEFAZSpecificValidator::class,
    ];
    
    public function validate(FiscalDocument $document): ValidationResult
    {
        $result = new ValidationResult();
        
        foreach ($this->validators as $validatorClass) {
            $validator = app($validatorClass);
            $validationResult = $validator->validate($document);
            
            $result->merge($validationResult);
            
            // Stop on critical errors
            if ($validationResult->hasCriticalErrors()) {
                break;
            }
        }
        
        return $result;
    }
}

class TaxCalculationValidator
{
    public function validate(FiscalDocument $document): ValidationResult
    {
        $result = new ValidationResult();
        
        // Validate ICMS calculation
        if (!$this->validateICMSCalculation($document)) {
            $result->addError('ICMS calculation is incorrect');
        }
        
        // Validate PIS/COFINS
        if (!$this->validatePISCOFINS($document)) {
            $result->addError('PIS/COFINS calculation is incorrect');
        }
        
        // Validate total amounts
        if (!$this->validateTotalAmounts($document)) {
            $result->addCriticalError('Total amounts do not match sum of items');
        }
        
        return $result;
    }
}
```

## Error Handling and Contingency

### SEFAZ Communication Errors
```php
class SEFAZCommunicationHandler
{
    public function handleSubmission(FiscalDocument $document): SubmissionResult
    {
        try {
            $response = $this->sefazClient->submit($document);
            
            return $this->processResponse($response);
            
        } catch (SEFAZTimeoutException $e) {
            // Activate contingency mode
            return $this->activateContingency($document);
            
        } catch (SEFAZRejectedException $e) {
            // Handle business rule rejections
            return $this->handleRejection($document, $e);
            
        } catch (CommunicationException $e) {
            // Network/infrastructure errors
            return $this->queueForRetry($document, $e);
        }
    }
    
    private function activateContingency(FiscalDocument $document): SubmissionResult
    {
        // FS-IA contingency mode for NFe
        $document->markAsContingency(ContingencyType::FS_IA);
        
        // Can issue document offline with validation later
        $document->generateContingencyNumber();
        
        // Queue for synchronization when service is restored
        ContingencyQueue::dispatch($document);
        
        return SubmissionResult::contingency($document);
    }
}
```

## AI Agent Guidelines for Fiscal Compliance

### 1. Never Skip Validation
```php
// WRONG - Direct submission without validation
$nfe = new NFe($orderData);
$sefaz->submit($nfe);

// CORRECT - Comprehensive validation pipeline
$nfe = $nfeService->create($order);
$validation = $fiscalValidator->validate($nfe);

if ($validation->isValid()) {
    $result = $sefazService->submit($nfe);
} else {
    throw new FiscalValidationException($validation->getErrors());
}
```

### 2. Always Handle Contingency
Every fiscal document service must implement contingency protocols for when government services are unavailable.

### 3. Maintain Audit Trails
All fiscal operations must be logged with sufficient detail for government audits.

### 4. Test with Real Scenarios
Use actual CNPJ numbers and product codes in testing to ensure compatibility with government systems.

### 5. Stay Updated with Legislation
Brazilian tax law changes frequently. Implement a system to track and apply legislative updates.

### 6. Regional Considerations
Different states have different rules:
- **São Paulo**: Stricter NFCe requirements
- **Amazonas**: SUFRAMA special zones
- **Rio de Janeiro**: Specific ICMS rates

### 7. Performance Critical
Fiscal document generation is often in the critical path of sales. Optimize for speed while maintaining compliance.

## Testing Fiscal Compliance

```php
class FiscalComplianceTest extends TestCase
{
    /** @test */
    public function nfe_calculation_matches_government_examples(): void
    {
        // Use real government test data
        $order = $this->createOrderFromGovernmentExample();
        
        $nfe = $this->nfeService->create($order);
        
        $this->assertEquals(
            $this->expectedGovernmentResult['icms_amount'],
            $nfe->taxes->icms_amount
        );
    }
    
    /** @test */
    public function cfop_determination_follows_regulation(): void
    {
        $interstateProductSale = Order::factory()
            ->interstate()
            ->withProducts()
            ->create();
            
        $cfop = $this->cfopDeterminer->determine($interstateProductSale);
        
        $this->assertEquals('6.102', $cfop);
    }
}
```
