# ERP Architecture Guidelines for AI Agents

## Overview
This ERP system follows Domain-Driven Design (DDD) principles with 8 core business domains. AI agents must understand the domain boundaries, aggregates, and interaction patterns.

## Domain Architecture

### 1. Identity Domain (Core)
```php
// Primary aggregates
- User (identity management)
- Profile (role-based permissions)
- Authentication (security context)
```

**AI Guidelines:**
- Always validate user context before domain operations
- Respect role-based access control (RBAC) patterns
- Consider multi-tenant isolation in all queries

### 2. Company Domain (Core)
```php
// Primary aggregates
- Company (multitenant root)
- Branch (geographical/operational units)
- Settings (company-specific configurations)
```

**AI Guidelines:**
- Every business operation must be scoped to a company
- Use Row Level Security (RLS) for data isolation
- Implement company-aware service providers

### 3. Services Domain
```php
// Primary aggregates
- Service (business offerings)
- Category (service classification)
- Pricing (dynamic pricing strategies)
```

**AI Guidelines:**
- Services are company-specific and configurable
- Handle complex pricing logic with strategy pattern
- Support both product and service business models

### 4. Orders Domain
```php
// Primary aggregates
- Order (transaction root)
- OrderItem (line items with business rules)
- OrderStatus (state machine)
```

**AI Guidelines:**
- Orders follow saga pattern for consistency
- Implement event sourcing for audit trails
- Handle partial fulfillment scenarios

### 5. Billing Domain
```php
// Primary aggregates
- Invoice (financial document)
- Payment (transaction processing)
- Contract (recurring billing)
```

**AI Guidelines:**
- Ensure fiscal compliance for Brazilian market
- Implement idempotent payment processing
- Support multiple payment methods and currencies

### 6. Fiscal Domain (Brazil-Specific)
```php
// Primary aggregates
- TaxDocument (NFe, NFCe, CTe)
- TaxCalculation (complex Brazilian tax rules)
- FiscalSettings (per-company tax configuration)
```

**AI Guidelines:**
- Critical domain - fiscal errors have legal consequences
- Implement extensive validation and logging
- Support SPED and government integration requirements

### 7. Financial Domain
```php
// Primary aggregates
- Account (chart of accounts)
- Transaction (double-entry bookkeeping)
- Report (financial statements)
```

**AI Guidelines:**
- Maintain strict double-entry accounting rules
- Ensure transaction atomicity and consistency
- Support multi-currency financial operations

### 8. Auditing Domain (Cross-cutting)
```php
// Primary aggregates
- AuditLog (system changes tracking)
- DataHistory (versioned entity states)
- Compliance (regulatory requirements)
```

**AI Guidelines:**
- Automatically track all business-critical changes
- Ensure LGPD compliance for personal data
- Implement comprehensive security monitoring

## Domain Interaction Patterns

### Event-Driven Communication
```php
// Example: Order placed event affecting multiple domains
OrderPlaced::class → [
    BillingDomain::createInvoice(),
    InventoryDomain::reserveItems(),
    AuditingDomain::logTransaction(),
    FinancialDomain::recordSale()
]
```

### Anti-Corruption Layers
- Use adapters for external integrations (payment gateways, tax services)
- Implement DTOs for cross-domain communication
- Maintain domain language purity

### Aggregate Design Rules
1. **Single Responsibility**: Each aggregate manages one business concept
2. **Consistency Boundaries**: Transactions only within aggregates
3. **Event Publishing**: Use domain events for cross-aggregate communication
4. **Identity Management**: UUIDs for all aggregate roots

## Code Generation Guidelines

When generating code, AI agents should:

1. **Follow Naming Conventions**
   - Aggregates: PascalCase nouns (Order, Invoice, Company)
   - Services: PascalCase with "Service" suffix
   - Events: PascalCase with past tense (OrderPlaced, InvoiceGenerated)

2. **Implement Required Interfaces**
   ```php
   // All aggregates must extend
   abstract class AggregateRoot extends Model
   {
       protected array $domainEvents = [];
       
       public function pullDomainEvents(): array
       {
           $events = $this->domainEvents;
           $this->domainEvents = [];
           return $events;
       }
   }
   ```

3. **Use Value Objects**
   ```php
   // Prefer value objects for domain concepts
   class Money
   {
       public function __construct(
           public readonly int $amount,
           public readonly string $currency = 'BRL'
       ) {}
   }
   ```

4. **Implement Repository Pattern**
   ```php
   interface OrderRepositoryInterface
   {
       public function findByCompany(CompanyId $companyId): Collection;
       public function save(Order $order): void;
       public function nextIdentity(): OrderId;
   }
   ```

## Testing Guidelines

- **Unit Tests**: Test domain logic in isolation
- **Integration Tests**: Test cross-domain scenarios
- **Feature Tests**: Test complete user workflows
- **Contract Tests**: Ensure API consistency

## Performance Considerations

- Use eager loading for predictable N+1 scenarios
- Implement query result caching for read-heavy operations
- Consider CQRS for complex reporting requirements
- Use database transactions judiciously

## Security Guidelines

- Validate all input at domain boundaries
- Implement authorization at the aggregate level
- Use encryption for sensitive data (PII, financial)
- Audit all state-changing operations

## Error Handling

- Use domain-specific exceptions
- Implement circuit breaker pattern for external services
- Provide meaningful error messages for business rules
- Log errors with sufficient context for debugging
